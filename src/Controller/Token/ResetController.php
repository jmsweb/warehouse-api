<?php

namespace App\Controller\Token;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ResetController {

    protected ContainerInterface $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function __invoke(Request $request, Response $response, array $args) : Response {
        $cookieParams = $request->getCookieParams();
        if (array_key_exists($_ENV['COOKIE_NAME'], $cookieParams)) {
            setcookie($_ENV['COOKIE_NAME'], '', [
                'expires' => -1,
                'path' => '/',
                'domain' => $_ENV['COOKIE_DOMAIN'], // PAY ATTENTION
                'secure' => filter_var($_ENV['COOKIE_SECURE'], FILTER_VALIDATE_BOOLEAN), // MUST BE TRUE IN PROD
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            setcookie($_ENV['COOKIE_EXPIRY'], '', [
                'expires' => -1,
                'path' => '/',
                'domain' => $_ENV['COOKIE_DOMAIN'], // PAY ATTENTION
                'secure' => filter_var($_ENV['COOKIE_SECURE'], FILTER_VALIDATE_BOOLEAN), // MUST BE TRUE IN PROD
                'httponly' => false,
                'samesite' => 'Strict'
            ]);
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withStatus(201);
        }

        $response->getBody()->write(json_encode(['success' => false]));
        return $response->withStatus(201);
    }
}