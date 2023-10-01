<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Exception\ServerException;

class HealthCheck {

    protected ContainerInterface $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function __invoke(Request $request, Response $response, array $args): Response {
        $response->getBody()->write(json_encode(['warehouse_api' => 'OK']));
        $response = $response->withHeader('Access-Control-Allow-Origin', $_ENV['WAREHOUSE_REACT']);
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        return $response->withStatus(201);
    }
}