<?php
namespace App\Controller\Token;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GenerateController {

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
        $serverParams = $request->getServerParams();

        if (
            array_key_exists('PHP_AUTH_USER', $serverParams) &&
            array_key_exists('PHP_AUTH_PW', $serverParams)
        ) {
            $post = $service->post('/', [
                'auth' => [
                    $serverParams['PHP_AUTH_USER'],
                    $serverParams['PHP_AUTH_PW']
                ]
            ]);
            $data = json_decode($post->getBody());

            if ($data->success) {
                // Apply `double-submit-cookie` Strategy to Mitigate against XSS and CSRF
                // HttpOnly SameSite Secure only protects against XSS.
                setcookie($_ENV['COOKIE_NAME'], $data->jwt, [
                    'expires' => time() + 600,
                    'path' => '/',
                    'domain' => $_ENV['COOKIE_DOMAIN'], // PAY ATTENTION
                    'secure' => filter_var($_ENV['COOKIE_SECURE'], FILTER_VALIDATE_BOOLEAN), // MUST BE TRUE IN PROD
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                // localStorage JWT from Auth Microservice
                $response->getBody()->write(json_encode($data));
                return $response->withStatus(201);
            }
        }

        $response->getBody()->write(json_encode(['success' => false]));
        return $response->withStatus(201);
    }
}