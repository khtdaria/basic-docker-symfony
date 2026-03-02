<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Entity\User;
use App\Entity\Video;
use App\Enum\VideoStatus;
use App\Message\ProcessVideoMessage;
use App\Service\VideoProcessingService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints\File;

/**
 * @extends AbstractCrudController<Video>
 */
final class VideoCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly VideoProcessingService $processingService,
        private readonly MessageBusInterface $bus,
    ) {
    }

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
            ->add(ChoiceFilter::new('status', 'Status')->setChoices([
                'Pending' => VideoStatus::Pending->value,
                'Processing' => VideoStatus::Processing->value,
                'Ready' => VideoStatus::Ready->value,
                'Failed' => VideoStatus::Failed->value,
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnDetail();
        yield TextField::new('title', 'Title');
        yield TextareaField::new('description', 'Description')->setRequired(false);

        yield Field::new('videoFile', 'Video File')
            ->setFormType(FileType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => Crud::PAGE_NEW === $pageName,
                'constraints' => [
                    new File(
                        maxSize: '4096M',
                        mimeTypes: ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/webm'],
                        mimeTypesMessage: 'Please upload a valid video file (mp4, mov, avi, mkv, webm).',
                    ),
                ],
            ])
            ->onlyOnForms();

        yield ChoiceField::new('status', 'Status')
            ->setChoices([
                'Pending' => VideoStatus::Pending->value,
                'Processing' => VideoStatus::Processing->value,
                'Ready' => VideoStatus::Ready->value,
                'Failed' => VideoStatus::Failed->value,
            ])
            ->renderAsBadges([
                VideoStatus::Pending->value => 'warning',
                VideoStatus::Processing->value => 'info',
                VideoStatus::Ready->value => 'success',
                VideoStatus::Failed->value => 'danger',
            ])
            ->onlyOnIndex();

        yield TextField::new('originalPath', 'Original File')->onlyOnDetail();
        yield TextField::new('hlsPath', 'HLS Playlist')->onlyOnDetail();
        yield TextField::new('previewImagePath', 'Preview Image')->onlyOnDetail();
        yield IntegerField::new('durationSec', 'Duration (sec)')->onlyOnDetail();
        yield TextField::new('codec', 'Codec')->onlyOnDetail();
        yield TextareaField::new('processingError', 'Processing Error')
            ->onlyOnDetail()
            ->setRequired(false);
        yield BooleanField::new('isActive', 'Active');
        yield DateTimeField::new('createdAt', 'Created')->onlyOnDetail();
        yield DateTimeField::new('updatedAt', 'Updated')->onlyOnDetail();
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Video $entityInstance */
        parent::persistEntity($entityManager, $entityInstance);

        $this->handleUploadedFile($entityInstance, $entityManager);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var Video $entityInstance */
        parent::updateEntity($entityManager, $entityInstance);

        $this->handleUploadedFile($entityInstance, $entityManager);
    }

    // todo: move it to separate "Action"
    private function handleUploadedFile(Video $video, EntityManagerInterface $em): void
    {
        $request = $this->getContext()?->getRequest();
        if (null === $request) {
            return;
        }

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->all()['Video']['videoFile'] ?? null;

        if (!$uploadedFile instanceof UploadedFile) {
            return;
        }

        $videoId = $video->getId()->toString();
        $videoDir = $this->processingService->getVideoDir($videoId);

        if (!is_dir($videoDir)) {
            mkdir($videoDir, 0755, true);
        }

        $extension = $uploadedFile->getClientOriginalExtension() ?: 'mp4';
        $filename = 'original.' . $extension;
        $uploadedFile->move($videoDir, $filename);

        $video->setOriginalPath($filename);
        $video->setStatus(VideoStatus::Pending);
        $em->flush();

        $this->bus->dispatch(new ProcessVideoMessage($videoId));
    }
}
