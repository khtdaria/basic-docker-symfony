<?php

declare(strict_types=1);

namespace App\Api\V1\Controller;

use App\Repository\VrDeviceRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth', name: 'api_v1_auth_')]
final class AuthController extends AbstractController
{
    private const int TOKEN_EXPIRES_IN = 3600;

    public function __construct(
        private readonly VrDeviceRepository $deviceRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * POST /api/v1/auth/token.
     *
     * Authenticate a VR device and return a JWT token.
     *
     * Request body:
     *   { "identifier": "device-001", "password": "secret" }
     *
     * Response:
     *   { "token": "<jwt>", "device_id": "<uuid>", "tenant_id": "<uuid>" }
     */
    #[Route('/token', name: 'token', methods: ['POST'])]
    public function token(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($request->getContent(), true);

        $identifier = isset($data['identifier']) && is_string($data['identifier'])
            ? trim($data['identifier'])
            : '';

        $password = isset($data['password']) && is_string($data['password'])
            ? $data['password']
            : '';

        if ('' === $identifier || '' === $password) {
            return $this->json(
                ['error' => 'identifier and password are required'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $device = $this->deviceRepository->findActiveByIdentifier($identifier);

        if (null === $device || !$this->passwordHasher->isPasswordValid($device, $password)) {
            return $this->json(
                ['error' => 'Invalid credentials'],
                Response::HTTP_UNAUTHORIZED,
            );
        }

        $token = $this->jwtManager->createFromPayload($device, [
            'device_id' => $device->getId()->toString(),
            'tenant_id' => $device->getTenant()->getId()->toString(),
        ]);

        return $this->json([
            'token' => $token,
            'device_id' => $device->getId()->toString(),
            'tenant_id' => $device->getTenant()->getId()->toString(),
            'expires_in' => self::TOKEN_EXPIRES_IN,
        ]);
    }
}
