<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Exception\HttpUnauthorizedException;

class CheckForCookie
{
    /**
     * Example middleware invokable class
     *
     * @param  Request        $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $cookieParams = $request->getCookieParams();

        if (!array_key_exists($_ENV['COOKIE_NAME'], $cookieParams)){
            throw new HttpUnauthorizedException($request);
        }

        return $handler->handle($request);
    }
}