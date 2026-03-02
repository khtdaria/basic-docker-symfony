<?php

declare(strict_types=1);

namespace App\Api\V1\Controller;

use App\Api\V1\DTO\Response\VideoDTO;
use App\Entity\VrDevice;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function list(#[CurrentUser] VrDevice $device, Request $request): JsonResponse
    {
        $device->markSeen();
        $this->em->flush();

        $baseUrl = $request->getSchemeAndHttpHost();
        $videos = $this->videoRepository->findReadyForDevice($device);

        $payload = array_map(
            static fn (mixed $v) => new VideoDTO($v, $baseUrl)->toArray(),
            $videos,
        );

        return $this->json([
            'data' => $payload,
            'total' => count($payload),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id, Request $request): JsonResponse
    {
        $video = $this->videoRepository->findReadyById($id);

        if (null === $video) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        $baseUrl = $request->getSchemeAndHttpHost();

        return $this->json(new VideoDTO($video, $baseUrl)->toArray());
    }
}
