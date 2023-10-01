https://www.slimframework.com/docs/v4/cookbook/enable-cors.html

For simple CORS requests, the server only needs to add the following header to its response:

```
Access-Control-Allow-Origin: <domain>, ...
```

The following code should enable lazy CORS.

```
$app->options('/{routes:.+}', function ($request, $response, $args) {
return $response;
});

$app->add(function ($request, $handler) {
$response = $handler->handle($request);
return $response
->withHeader('Access-Control-Allow-Origin', 'http://mysite')
->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});
```
Add the following route as the last route:

```
<?php
use Slim\Exception\HttpNotFoundException;

/**
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: make sure this route is defined last
 */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});
```