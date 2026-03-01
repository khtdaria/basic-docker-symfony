<?php

declare(strict_types=1);

namespace App\Api\V1\Controller;

use App\Api\V1\DTO\Response\VideoDTO;
use App\Entity\VrDevice;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use function count;

#[Route('/videos', name: 'api_v1_videos_')]
final class VideoController extends AbstractController
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(#[CurrentUser] VrDevice $device): JsonResponse
    {
        $device->markSeen();
        $this->em->flush();

        $videos = $this->videoRepository->findForDevice($device);

        $payload = array_map(
            static fn (mixed $v) => new VideoDTO($v)->toArray(),
            $videos,
        );

        return $this->json([
            'data' => $payload,
            'total' => count($payload),
        ]);
    }
}
