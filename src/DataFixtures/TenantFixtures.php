<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Tenant;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class TenantFixtures extends Fixture implements DependentFixtureInterface
{
    public const string REF_PLATFORM_OWNER = 'tenant_platform-owner';
    public const string REF_ACME_CORP      = 'tenant_acme-corp';
    public const string REF_BETA_STUDIO    = 'tenant_beta-vr-studio';

    public function getDependencies(): array
    {
        return [VideoFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $platformOwner = new Tenant();
        $platformOwner->setName('Platform Owner');
        $platformOwner->setSlug('platform-owner');
        $platformOwner->setIsSuperTenant(true);
        $platformOwner->setIsActive(true);

        $manager->persist($platformOwner);
        $this->addReference(self::REF_PLATFORM_OWNER, $platformOwner);

        /** @var Video $cityTour */
        $cityTour = $this->getReference(VideoFixtures::REF_CITY_TOUR, Video::class);
        /** @var Video $underwater */
        $underwater = $this->getReference(VideoFixtures::REF_UNDERWATER, Video::class);

        $acmeCorp = new Tenant();
        $acmeCorp->setName('Acme Corp');
        $acmeCorp->setSlug('acme-corp');
        $acmeCorp->setIsActive(true);
        $acmeCorp->addVideo($cityTour);
        $acmeCorp->addVideo($underwater);

        $manager->persist($acmeCorp);
        $this->addReference(self::REF_ACME_CORP, $acmeCorp);

        /** @var Video $mountain */
        $mountain = $this->getReference(VideoFixtures::REF_MOUNTAIN, Video::class);

        $betaStudio = new Tenant();
        $betaStudio->setName('Beta VR Studio');
        $betaStudio->setSlug('beta-vr-studio');
        $betaStudio->setIsActive(true);
        $betaStudio->addVideo($mountain);

        $manager->persist($betaStudio);
        $this->addReference(self::REF_BETA_STUDIO, $betaStudio);

        $manager->flush();
    }
}
