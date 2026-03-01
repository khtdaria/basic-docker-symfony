<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'dashboard')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public function index(): Response
    {
        $vrUrl = $this->adminUrlGenerator
            ->setController(VrDeviceCrudController::class)
            ->setAction('new')
            ->generateUrl();

        $areaUrl = $this->adminUrlGenerator
            ->setController(AreaCrudController::class)
            ->setAction('new')
            ->generateUrl();


        return $this->render('admin/dashboard.html.twig', [
            'vrUrl' => $vrUrl,
            'areaUrl' => $areaUrl,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('VR Platform')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('messages')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        /** @var User $user */
        $user = $this->getUser();

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        if ($user->isSuperAdmin()) {
            yield MenuItem::section('Platform Management');
            yield MenuItem::linkTo(TenantCrudController::class, 'Tenants', 'fa fa-building');
        }

        yield MenuItem::section('My Workspace');

        if ($user->isSuperAdmin() || $user->isTenantAdmin()) {
            yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-users');
            yield MenuItem::linkTo(AreaCrudController::class, 'Locations (Areas)', 'fa fa-map-marker');
            yield MenuItem::linkTo(VrDeviceCrudController::class, 'VR Devices', 'fa fa-vr-cardboard');
            yield MenuItem::linkTo(VideoCrudController::class, 'Videos', 'fa fa-film');
        }

        yield MenuItem::section('');

        yield MenuItem::linkToLogout('Logout', 'fa fa-sign-out');
    }
}
