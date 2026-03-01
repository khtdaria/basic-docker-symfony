<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('../src/Api/V1/Controller/', 'attribute')
        ->prefix('/api/v1');

    $routes->import('../src/Admin/Controller/', 'attribute')
        ->prefix('');
};
