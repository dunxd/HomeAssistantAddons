<?php

namespace SebLucas\Cops\Framework\Adapter;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\UriGenerator;

/**
 * Laravel Framework Adapter - @todo
 */
class LaravelAdapter implements AdapterInterface
{
    private HandlerManager $handlerManager;
    private \Illuminate\Foundation\Application $app;
    private \Illuminate\Routing\Router $router;
    /** @var array<mixed> */
    private array $config = [];

    public function __construct(
        ?HandlerManager $handlerManager = null,
        ?\Illuminate\Foundation\Application $app = null
    ) {
        $this->handlerManager = $handlerManager ?? new HandlerManager();
        $this->app = $app ?? new \Illuminate\Foundation\Application();
        $this->router = $this->app['router'];
    }

    public function getName(): string
    {
        return 'laravel';
    }

    public function initialize(array $config = []): void
    {
        $this->config = $config;

        // Register routes with Laravel's router
        $routes = $this->loadRoutes();
        $this->registerRoutes($routes);
    }

    public function loadRoutes(): array
    {
        $routes = [];
        foreach ($this->handlerManager->getHandlers() as $name => $handlerClass) {
            if (method_exists($handlerClass, 'getRoutes')) {
                $handlerRoutes = $this->handlerManager->addHandlerRoutes($handlerClass);
                $routes = array_merge($routes, $handlerRoutes);
            }
        }
        return $routes;
    }

    public function registerRoutes(array $routes): void
    {
        foreach ($routes as $name => $routeData) {
            [$pattern, $params, $methods] = $routeData;

            $this->router->match($methods, $pattern, function (\Illuminate\Http\Request $laravelRequest) use ($params) {
                $request = $this->createRequest();
                $request->merge($laravelRequest->route()->parameters());  // Route parameters
                $request->merge($params);  // Fixed parameters
                $request->merge($laravelRequest->all());  // Query and POST parameters

                return $this->dispatch($request);
            })->name($name);
        }
    }

    public function createRequest(): Request
    {
        $request = new Request();
        UriGenerator::setLocale($request->locale());
        return $request;
    }

    public function dispatch(Request $request): Response
    {
        // @todo move to RequestContext
        // $request->matchRoute();
        try {
            // Laravel's router has already handled routing at this point
            if ($request->invalid) {
                $handler = $this->handlerManager->createHandler('error');
                return $handler->handle($request);
            }

            $handler = $this->handlerManager->createFromRequest($request);
            return $handler->handle($request);

        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return Response::sendError($request, $e->getMessage());
        }
    }

    public function generateUrl(string $name, array $params = [], bool $absolute = false): string
    {
        try {
            return $this->router->route($name, $params, $absolute);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Cannot generate URL for route '$name'", 0, $e);
        }
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->handlerManager;
    }

    public function getRouter(): \Illuminate\Routing\Router
    {
        return $this->router;
    }
}
