<?php

namespace App\Controller\Token;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VerifyController {

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

        if (array_key_exists($_ENV['COOKIE_NAME'], $cookieParams)) {
            $post = $service->post('/verify', [
                'json' => $cookieParams[$_ENV['COOKIE_NAME']]
            ]);

            $data = json_decode($post->getBody());

            if ($data->success) {
                $response->getBody()->write(json_encode([
                    'success' => $data->success,
                    'payload' => $data->payload
                ]));
                return $response->withStatus(201);
            }
        }

        $response->getBody()->write(json_encode(['success' => false]));
        return $response->withStatus(201);
    }
}