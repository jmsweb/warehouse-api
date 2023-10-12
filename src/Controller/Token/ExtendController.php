<?php

namespace App\Controller\Token;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ExtendController {

    protected ContainerInterface $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function __invoke(Request $request, Response $response, array $args) : Response {
        $service = $this->container->get('warehouse-auth');
        $cookieParams = $request->getCookieParams();

        $post = $service->post('/extend', [
            'json' => $cookieParams[$_ENV['COOKIE_NAME']]
        ]);

        $data = json_decode($post->getBody());

        if ($data->success) {
            $post = $service->post('/extend?XDEBUG_SESSION=ECLIPSE_DBGP', [
                'json' => $cookieParams[$_ENV['COOKIE_NAME']]
            ]);
            $expiry = time() + 600; // 10 minutes
            setcookie($_ENV['COOKIE_NAME'], $data->jwt, [
                'expires' => $expiry,
                'path' => '/',
                'domain' => $_ENV['COOKIE_DOMAIN'], // PAY ATTENTION
                'secure' => filter_var($_ENV['COOKIE_SECURE'], FILTER_VALIDATE_BOOLEAN), // MUST BE TRUE IN PROD
                'httponly' => true, // Cookie is inaccessible for JavaScript
                'samesite' => 'Strict'
            ]);

            setcookie($_ENV['COOKIE_EXPIRY'], $expiry, [
                'expires' => $expiry,
                'path' => '/',
                'domain' => $_ENV['COOKIE_DOMAIN'], // PAY ATTENTION
                'secure' => filter_var($_ENV['COOKIE_SECURE'], FILTER_VALIDATE_BOOLEAN), // MUST BE TRUE IN PROD
                'httponly' => false, // Cookie is accessible for JavaScript
                'samesite' => 'Strict'
            ]);

            $response->getBody()->write(json_encode([
                'success' => $data->success
            ]));
            return $response->withStatus(201);
        }

        $response->getBody()->write(json_encode(['success' => false]));
        return $response->withStatus(201);
    }
}