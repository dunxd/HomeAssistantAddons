<?php

namespace SebLucas\Cops\Framework\Adapter;

use Slim\App;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Handlers\QueueBasedHandler;

/**
 * Slim Framework Adapter - @todo
 */
class SlimAdapter implements AdapterInterface
{
    /** @var array<string, class-string> */
    protected array $middlewares = [];

    public function __construct(
        private readonly App $app,
        private readonly HandlerManager $manager = new HandlerManager()
    ) {
        // Register COPS middleware with Slim
        foreach ($this->middlewares as $middlewareClass) {
            $this->app->add(new $middlewareClass());
        }
    }

    public function getName(): string
    {
        return 'slim';
    }

    public function handleRequest(RequestContext $context): Response
    {
        // Run Slim app with COPS request
        $request = $context->getRequest();
        $response = $this->app->handle($this->convertToPsrRequest($request));

        // Convert PSR response to COPS response
        return $this->convertToCopsResponse($response);
    }

    public function getRouter(): RouterInterface
    {
        // Bridge Slim router to COPS RouterInterface
        return new class ($this->app->getRouteCollector()) implements RouterInterface {
            public function __construct(
                private readonly \Slim\Routing\RouteCollector $routeCollector
            ) {}

            public function match(string $path, string $method): array
            {
                $request = $this->createRequest($path, $method);
                $route = $this->routeCollector->getRouteParser()->parse($path)[0];

                return [
                    'handler' => $route[1]['handler'] ?? null,
                    'params' => $route[1]['arguments'] ?? [],
                ];
            }

            public function generate(string $name, array $parameters = []): string
            {
                return $this->routeCollector->getRouteParser()->urlFor($name, $parameters);
            }

            private function createRequest(string $path, string $method): ServerRequestInterface
            {
                $factory = \Slim\Factory\ServerRequestCreatorFactory::create();
                $request = $factory->createServerRequest($method, $path);
                return $request->withAttribute(RouteContext::ROUTE_PARSER, $this->routeCollector->getRouteParser());
            }
        };
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->manager;
    }

    public function addMiddleware(string $middlewareClass): self
    {
        $this->middlewares[] = $middlewareClass;
        $this->app->add(new $middlewareClass());
        return $this;
    }

    /**
     * Convert COPS Request to PSR-7 ServerRequestInterface
     */
    protected function convertToPsrRequest(Request $request): ServerRequestInterface
    {
        $factory = \Slim\Factory\ServerRequestCreatorFactory::create();
        $serverRequest = $factory->createServerRequest(
            $request->getMethod(),
            $request->getUri()
        );

        // Copy headers
        foreach ($request->getHeaders() as $name => $values) {
            $serverRequest = $serverRequest->withHeader($name, $values);
        }

        // Copy query params
        $serverRequest = $serverRequest->withQueryParams($request->getQueryParams());

        // Copy post data
        $serverRequest = $serverRequest->withParsedBody($request->getParsedBody());

        return $serverRequest;
    }

    /**
     * Convert PSR-7 Response to COPS Response
     */
    protected function convertToCopsResponse(\Psr\Http\Message\ResponseInterface $psrResponse): Response
    {
        $response = new Response();

        // Copy status
        $response->setStatusCode($psrResponse->getStatusCode());

        // Copy headers
        foreach ($psrResponse->getHeaders() as $name => $values) {
            $response->setHeader($name, implode(', ', $values));
        }

        // Copy body
        $response->setContent((string) $psrResponse->getBody());

        return $response;
    }

    public function registerRoutes(): void
    {
        foreach ($this->manager->getHandlers() as $handlerClass) {
            foreach ($this->manager->addHandlerRoutes($handlerClass) as $name => $route) {
                [$path, $params, $methods, $options] = $route;
                // Register with Slim
                $this->app->map(
                    $methods,
                    $path,
                    function ($request, $response, $args) use ($handlerClass, $params) {
                        // Merge route args with params
                        $params = array_merge($params, $args);
                        // Create handler instance
                        $handler = new $handlerClass();
                        // Convert request and handle
                        $copsRequest = $this->convertToCopsRequest($request)->withQueryParams($params);
                        $copsResponse = $handler->handle($copsRequest);
                        // Convert response back
                        return $this->convertToPsrResponse($copsResponse);
                    }
                )->setName($name);
            }
        }
    }

    public function addRoute(string|array $methods, string $path, array $params, array $options = []): void
    {
        $name = $options[ConfigureRoutes::ROUTE_NAME] ?? ($params[Route::ROUTE_PARAM] ?? '');

        // Register with Slim
        $route = $this->app->map(
            is_array($methods) ? $methods : [$methods],
            $path,
            function (Request $request, Response $response, array $args) use ($params) {
                // Merge route args with fixed params
                $params = array_merge($params, $args);
                // Handle request using params
                return $this->handleRequest($request, $response, $params);
            }
        );

        if ($name) {
            $route->setName($name);
        }

        // Store for later reference
        $this->routes[$name] = [$path, $params, is_array($methods) ? $methods : [$methods], $options];
    }
}
