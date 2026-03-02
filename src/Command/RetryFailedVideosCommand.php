<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\VideoStatus;
use App\Message\ProcessVideoMessage;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:video:retry-failed',
    description: 'Re-queues all failed videos for processing',
)]
final class RetryFailedVideosCommand extends Command
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Retry a specific video ID only');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $specificId = $input->getOption('id');

        if ($specificId !== null) {
            $videos = array_filter(
                [$this->videoRepository->find($specificId)],
                static fn ($v) => $v !== null,
            );
        } else {
            /** @var \App\Entity\Video[] $videos */
            $videos = $this->videoRepository->findBy(['status' => VideoStatus::Failed]);
        }

        if (empty($videos)) {
            $io->info('No failed videos found.');

            return Command::SUCCESS;
        }

        foreach ($videos as $video) {
            $video->setStatus(VideoStatus::Pending);
            $video->setProcessingError(null);
            $this->em->flush();

            $this->bus->dispatch(new ProcessVideoMessage($video->getId()->toString()));
            $io->success(sprintf('Re-queued: [%s] %s', $video->getId()->toString(), $video->getTitle()));
        }

        return Command::SUCCESS;
    }
}
