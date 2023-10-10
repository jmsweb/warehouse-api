<?php

namespace App\Controller\Product;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class AddProductController {

    protected ContainerInterface $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function __invoke(Request $request, Response $response, array $args): Response {
        $data = $request->getParsedBody();
        $service = $this->container->get('warehouse-product');
        $post = $service->post('/add', [
            'json' => json_encode($data)
        ]);
        $response->getBody()->write(json_encode(['warehouse_api' => 'OK']));
        $response = $response->withHeader('Access-Control-Allow-Origin', $_ENV['WAREHOUSE_REACT']);
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        return $response->withStatus(201);
    }
}