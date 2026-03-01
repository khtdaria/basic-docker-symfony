<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Entity\Area;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractTenantAwareCrudController<Area>
 */
final class AreaCrudController extends AbstractTenantAwareCrudController
{
    public static function getEntityFqcn(): string
    {
        return Area::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Location (Area)')
            ->setEntityLabelInPlural('Locations (Areas)')
            ->setDefaultSort(['name' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnDetail();
        yield TextField::new('name', 'Location Name');
        yield TextareaField::new('description', 'Description')->setRequired(false);
        yield BooleanField::new('isActive', 'Active');

        if ($this->isSuperAdmin()) {
            yield AssociationField::new('tenant', 'Tenant');
        }

        yield AssociationField::new('vrDevices', 'VR Devices');
        yield DateTimeField::new('createdAt', 'Created')->onlyOnDetail();
    }
}
