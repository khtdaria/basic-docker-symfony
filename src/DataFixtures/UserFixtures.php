<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Tenant;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function getDependencies(): array
    {
        return [TenantFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Tenant $platformOwner */
        $platformOwner = $this->getReference(TenantFixtures::REF_PLATFORM_OWNER, Tenant::class);
        /** @var Tenant $acmeCorp */
        $acmeCorp = $this->getReference(TenantFixtures::REF_ACME_CORP, Tenant::class);
        /** @var Tenant $betaStudio */
        $betaStudio = $this->getReference(TenantFixtures::REF_BETA_STUDIO, Tenant::class);

        $this->createUser($manager, $platformOwner, [
            'email'     => 'admin@platform.local',
            'firstName' => 'Super',
            'lastName'  => 'Admin',
            'roles'     => [UserRole::SuperAdmin->value, UserRole::TenantAdmin->value],
            'password'  => 'admin123',
        ]);

        $this->createUser($manager, $acmeCorp, [
            'email'     => 'admin@acme.local',
            'firstName' => 'Alice',
            'lastName'  => 'Johnson',
            'roles'     => [UserRole::TenantAdmin->value],
            'password'  => 'admin123',
        ]);

        $this->createUser($manager, $betaStudio, [
            'email'     => 'admin@beta.local',
            'firstName' => 'Bob',
            'lastName'  => 'Smith',
            'roles'     => [UserRole::TenantAdmin->value],
            'password'  => 'admin123',
        ]);

        $manager->flush();
    }

    /**
     * @param array{email: string, firstName: string, lastName: string, roles: list<string>, password: string} $data
     */
    private function createUser(ObjectManager $manager, Tenant $tenant, array $data): void
    {
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setRoles(array_values($data['roles']));
        $user->setIsActive(true);
        $user->setTenant($tenant);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));

        $manager->persist($user);

    }
}
