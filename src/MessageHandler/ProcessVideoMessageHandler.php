<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enum\VideoStatus;
use App\Message\ProcessVideoMessage;
use App\Repository\VideoRepository;
use App\Service\VideoProcessingService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessVideoMessageHandler
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly VideoProcessingService $processingService,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ProcessVideoMessage $message): void
    {
        $video = $this->videoRepository->find($message->videoId);

        if (null === $video) {
            $this->logger->warning('ProcessVideoMessage: video not found', ['videoId' => $message->videoId]);

            return;
        }

        $videoId = $video->getId()->toString();
        $this->logger->info('Video processing started', ['videoId' => $videoId, 'title' => $video->getTitle()]);

        $video->setStatus(VideoStatus::Processing);
        $this->em->flush();

        try {
            $this->processingService->process($video);
            $this->em->flush();
            $this->logger->info('Video processing finished successfully', ['videoId' => $videoId]);
        } catch (\Throwable $e) {
            $this->logger->error('Video processing failed', [
                'videoId' => $videoId,
                'title' => $video->getTitle(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $video->setStatus(VideoStatus::Failed);
            $video->setProcessingError($e->getMessage());
            $this->em->flush();
        }
    }
}
