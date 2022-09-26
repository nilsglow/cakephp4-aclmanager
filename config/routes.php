<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;
use Cake\Core\Configure;

// return static function (RouteBuilder $routes) {

//     $routes->setRouteClass(DashedRoute::class);
//     $routes->scope('/', function (RouteBuilder $builder) {
//         $builder->connect('/', ['controller' => 'Users', 'action' => 'login']);
//         $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout', 'plugin' => null, 'prefix' => false]);
//         $builder->setExtensions(['json', 'xml', 'pdf']);
//         $builder->fallbacks();
//     });
// };

$routes->setRouteClass(DashedRoute::class);

if (!Configure::read('AclManager.admin') || Configure::read('AclManager.admin') != true) {
    $routes->connect(
        'AclManager',
        ['plugin' => 'AclManager', 'controller' => 'Acl', 'action' => 'index']
    );

    $routes->connect(
        'AclManager/:action/*',
        ['plugin' => 'AclManager', 'controller' => 'Acl']
    );
} else {
    // Connect routes for admin prefix.
    $routes->connect(
        'admin/AclManager',
        ['plugin' => 'AclManager', 'controller' => 'Acl', 'action' => 'index']
    );

    $routes->connect(
        'admin/AclManager/:action/*',
        ['plugin' => 'AclManager', 'controller' => 'Acl']
    );
}