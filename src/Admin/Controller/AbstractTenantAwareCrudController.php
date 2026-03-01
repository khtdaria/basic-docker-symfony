<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

/**
 * @template TEntity of object
 * @extends AbstractCrudController<TEntity>
 */
abstract class AbstractTenantAwareCrudController extends AbstractCrudController
{
    protected function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user;
    }

    protected function isSuperAdmin(): bool
    {
        return $this->getCurrentUser()->isSuperAdmin();
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (!$this->isSuperAdmin()) {
            $rootAlias = $qb->getRootAliases()[0];
            $qb
                ->andWhere($rootAlias.'.tenant = :currentTenant')
                ->setParameter('currentTenant', $this->getCurrentUser()->getTenant()->getId(), 'uuid');
        }

        return $qb;
    }

    /**
     * @param TEntity $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$this->isSuperAdmin() && method_exists($entityInstance, 'setTenant')) {
            $entityInstance->setTenant($this->getCurrentUser()->getTenant());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
