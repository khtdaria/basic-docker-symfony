<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Entity\User;
use App\Entity\Video;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;

/**
 * @extends AbstractCrudController<Video>
 */
final class VideoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Video::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Video')
            ->setEntityLabelInPlural('Videos')
            ->setDefaultSort(['title' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        /** @var User $user */
        $user = $this->getUser();

        $actions = $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        if (!$user->isSuperAdmin() && !$user->isTenantAdmin()) {
            $actions->disable(Action::NEW, Action::EDIT, Action::DELETE);
        }

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isActive', 'Active'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnDetail();
        yield TextField::new('title', 'Title');
        yield TextareaField::new('description', 'Description')->setRequired(false);
        yield UrlField::new('url', 'Video URL');
        yield TextField::new('thumbnailUrl', 'Thumbnail URL')->setRequired(false);
        yield IntegerField::new('durationSeconds', 'Duration (seconds)')->setRequired(false);
        yield BooleanField::new('isActive', 'Active');
        yield DateTimeField::new('createdAt', 'Created')->onlyOnDetail();
        yield DateTimeField::new('updatedAt', 'Updated')->onlyOnDetail();
    }
}
