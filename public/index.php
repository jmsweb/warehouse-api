<?php

use DI\Container;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Symfony\Component\Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use App\Middleware\CheckForCookie;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$container = new Container([
    'warehouse-auth' => new Client([
        'base_uri' => $_ENV['WAREHOUSE_AUTH'],
        'timeout' => 10,
        'verify' => $_ENV['SSL_CERT']
    ]),
    'warehouse-product' => new Client([
        'base_uri' => $_ENV['WAREHOUSE_PRODUCT'],
        'timeout' => 10,
        'verify' => $_ENV['SSL_CERT']
    ])
]);

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();

// CORS SETUP (PRE-FLIGHT)
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

// CORS SETUP (PRE-FLIGHT)
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', $_ENV['WAREHOUSE_REACT'])
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/health-check', App\Controller\HealthCheck::class);

// TOKEN AUTHENTICATION
$app->group('/api/v1/auth', function(RouteCollectorProxy $group) {
    $group->map(['POST'], '[/]', App\Controller\Token\GenerateController::class);
    $group->map(['GET'], '/verify', App\Controller\Token\VerifyController::class);
    $group->map(['POST'], '/extend', App\Controller\Token\ExtendController::class)->add(new CheckForCookie());
    $group->map(['POST'], '/reset', App\Controller\Token\ResetController::class)->add(new CheckForCookie());
    $group->map(['POST'], '/customer', App\Controller\Customer\AddCustomerController::class)->add(new CheckForCookie());
});

$app->group('/api/v1/product', function(RouteCollectorProxy $group) {
    $group->map(['POST'], '/add', App\Controller\Product\AddProductController::class)->add(new CheckForCookie());
});

/**
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: This must be defined last.
 */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

$app->run();