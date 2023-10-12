<?php

namespace App\Controller\Customer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class AddCustomerController {
    
    protected ContainerInterface $container;
    
    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function __invoke(Request $request, Response $response, array $args): Response {
        $serverParams = $request->getServerParams();
        $data = $request->getParsedBody();
        $service = $this->container->get('warehouse-auth');
        $post = $service->post('/customer', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => $data,
            'auth' => [
                $serverParams['PHP_AUTH_USER'],
                $serverParams['PHP_AUTH_PW']
            ]
        ]);
        $data = json_decode($post->getBody());

        $response->getBody()->write(json_encode([
            'success' => $data->success,
            'message' => $data->message
        ]));

        $response = $response->withHeader('Access-Control-Allow-Origin', $_ENV['WAREHOUSE_REACT']);
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        return $response->withStatus(201);
    }
}