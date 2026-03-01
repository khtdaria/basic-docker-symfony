<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Area;
use App\Entity\Tenant;
use App\Entity\VrDevice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class VrDeviceFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function getDependencies(): array
    {
        return [TenantFixtures::class, AreaFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Tenant $acmeCorp */
        $acmeCorp = $this->getReference(TenantFixtures::REF_ACME_CORP, Tenant::class);
        /** @var Tenant $betaStudio */
        $betaStudio = $this->getReference(TenantFixtures::REF_BETA_STUDIO, Tenant::class);
        /** @var Area $acmeLobby */
        $acmeLobby = $this->getReference(AreaFixtures::REF_ACME_LOBBY, Area::class);
        /** @var Area $acmeConferenceA */
        $acmeConferenceA = $this->getReference(AreaFixtures::REF_ACME_CONFERENCE_A, Area::class);
        /** @var Area $betaDemoFloor */
        $betaDemoFloor = $this->getReference(AreaFixtures::REF_BETA_DEMO_FLOOR, Area::class);

        $this->createDevice($manager, 'Headset #1',   'acme-device-001', $acmeCorp,   $acmeLobby);
        $this->createDevice($manager, 'Headset #2',   'acme-device-002', $acmeCorp,   $acmeConferenceA);
        $this->createDevice($manager, 'Demo Headset', 'beta-device-001', $betaStudio, $betaDemoFloor);

        $manager->flush();
    }

    private function createDevice(
        ObjectManager $manager,
        string $name,
        string $identifier,
        Tenant $tenant,
        Area $area,
    ): void
    {
        $device = new VrDevice();
        $device->setName($name);
        $device->setIdentifier($identifier);
        $device->setPassword($this->passwordHasher->hashPassword($device, 'device123'));
        $device->setIsActive(true);
        $device->setTenant($tenant);
        $device->setArea($area);

        $manager->persist($device);

    }
}
