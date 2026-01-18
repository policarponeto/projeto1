<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->match(['GET', 'POST'], '/usuarios', 'Usuarios::index');
$routes->match(['GET', 'POST'], '/usuarios/cadastrar', 'Usuarios::cadastrar');

$routes->match(['GET', 'POST'], '/contas', 'Contas::index');
$routes->match(['GET', 'POST'], '/contas/usuario/(:num)', 'Contas::getContaUsuario/$1');

$routes->match(['GET', 'POST'], '/transacoes/criar', 'Transacoes::criar');

// Rotas de diagnóstico
$routes->match(['GET', 'POST'], '/test-json', function() {
    include ROOTPATH . 'test-json.php';
});

$routes->match(['GET', 'POST'], '/server-diagnostic', function() {
    include ROOTPATH . 'server-diagnostic.php';
});
