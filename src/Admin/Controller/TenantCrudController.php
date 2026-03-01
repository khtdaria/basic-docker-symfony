<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Entity\Tenant;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
final class TenantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tenant::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tenant')
            ->setEntityLabelInPlural('Tenants')
            ->setPageTitle(Crud::PAGE_INDEX, 'All Tenants')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isActive', 'Active'))
            ->add(BooleanFilter::new('isSuperTenant', 'Super Tenant'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnDetail();
        yield TextField::new('name', 'Company Name');
        yield SlugField::new('slug', 'Slug')->setTargetFieldName('name');
        yield BooleanField::new('isActive', 'Active');
        yield BooleanField::new('isSuperTenant', 'Super Tenant');
        yield AssociationField::new('videos', 'Selected Videos')
            ->setFormTypeOption('by_reference', false)
            ->onlyOnForms();
        yield DateTimeField::new('createdAt', 'Created')->onlyOnDetail();
        yield DateTimeField::new('updatedAt', 'Updated')->onlyOnDetail();
    }
}
