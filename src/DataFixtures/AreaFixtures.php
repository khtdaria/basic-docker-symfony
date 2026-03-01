<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Area;
use App\Entity\Tenant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class AreaFixtures extends Fixture implements DependentFixtureInterface
{
    public const string REF_ACME_LOBBY         = 'area_acme-lobby';
    public const string REF_ACME_CONFERENCE_A  = 'area_acme-conference-a';
    public const string REF_BETA_DEMO_FLOOR    = 'area_beta-demo-floor';

    public function getDependencies(): array
    {
        return [TenantFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Tenant $acmeCorp */
        $acmeCorp = $this->getReference(TenantFixtures::REF_ACME_CORP, Tenant::class);
        /** @var Tenant $betaStudio */
        $betaStudio = $this->getReference(TenantFixtures::REF_BETA_STUDIO, Tenant::class);

        $lobby = $this->createArea($manager, 'Lobby', $acmeCorp);
        $this->addReference(self::REF_ACME_LOBBY, $lobby);

        $conferenceA = $this->createArea($manager, 'Conference Room A', $acmeCorp);
        $this->addReference(self::REF_ACME_CONFERENCE_A, $conferenceA);

        $demoFloor = $this->createArea($manager, 'Demo Floor', $betaStudio);
        $this->addReference(self::REF_BETA_DEMO_FLOOR, $demoFloor);

        $manager->flush();
    }

    private function createArea(ObjectManager $manager, string $name, Tenant $tenant): Area
    {
        $area = new Area();
        $area->setName($name);
        $area->setTenant($tenant);

        $manager->persist($area);

        return $area;
    }
}
