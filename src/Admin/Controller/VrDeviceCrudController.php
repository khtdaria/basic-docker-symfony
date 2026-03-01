<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Entity\VrDevice;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function in_array;

/**
 * @extends AbstractTenantAwareCrudController<VrDevice>
 */
final class VrDeviceCrudController extends AbstractTenantAwareCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return VrDevice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('VR Device')
            ->setEntityLabelInPlural('VR Devices')
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
        yield TextField::new('name', 'Device Name');
        yield TextField::new('identifier', 'Login Identifier');
        yield BooleanField::new('isActive', 'Active');

        if ($this->isSuperAdmin()) {
            yield AssociationField::new('tenant', 'Tenant');
        }

        yield AssociationField::new('area', 'Location (Area)')
            ->onlyOnDetail();

        yield AssociationField::new('videos', 'Assigned Videos')
            ->setFormTypeOption('by_reference', false)
            ->setHelp('Leave empty to use the tenant\'s video selection.')
            ->setRequired(false);

        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT], true)) {
            yield TextField::new('plainPassword', 'Password')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'required' => Crud::PAGE_NEW === $pageName,
                    'first_options' => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                ])
                ->setRequired(Crud::PAGE_NEW === $pageName);
        }

        yield DateTimeField::new('lastSeenAt', 'Last Seen')->onlyOnDetail();
        yield DateTimeField::new('createdAt', 'Created')->onlyOnDetail();
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->hashPasswordIfProvided($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->hashPasswordIfProvided($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPasswordIfProvided(mixed $entityInstance): void
    {
        if (!$entityInstance instanceof VrDevice) {
            return;
        }

        if (!(bool) $entityInstance->getPlainPassword()) {
            return;
        }

        $entityInstance->setPassword(
            $this->passwordHasher->hashPassword(
                $entityInstance,
                $entityInstance->getPlainPassword()
            )
        );

        $entityInstance->setPlainPassword(null);
    }
}
