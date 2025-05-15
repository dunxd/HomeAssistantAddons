<?php

namespace SebLucas\Cops\Framework\Adapter;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\UriGenerator;
use Symfony\Component\Routing\RouterInterface;

/**
 * Symfony Framework Adapter - @todo
 */
class SymfonyAdapter implements AdapterInterface
{
    private HandlerManager $handlerManager;
    private \Symfony\Component\HttpKernel\HttpKernel $kernel;
    private RouterInterface $router;
    /** @var array<mixed> */
    private array $config = [];

    public function __construct(
        ?HandlerManager $handlerManager = null,
        ?\Symfony\Component\HttpKernel\HttpKernel $kernel = null,
        ?\Symfony\Component\Routing\RouterInterface $router = null
    ) {
        $this->handlerManager = $handlerManager ?? new HandlerManager();
        $this->kernel = $kernel ?? $this->createDefaultKernel();
        $this->router = $router ?? $this->kernel->getContainer()->get('router');
    }

    private function createDefaultKernel(): \Symfony\Component\HttpKernel\HttpKernel
    {
        $kernel = new \Symfony\Component\HttpKernel\HttpKernel(
            new \Symfony\Component\EventDispatcher\EventDispatcher(),
            new \Symfony\Component\HttpKernel\Controller\ControllerResolver(),
            //new \Symfony\Component\HttpKernel\HttpCache\Store(__DIR__ . '/cache')
        );

        return $kernel;
    }

    public function getName(): string
    {
        return 'symfony';
    }

    public function initialize(array $config = []): void
    {
        $this->config = $config;

        // Register routes with Symfony's router
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
        $collection = new \Symfony\Component\Routing\RouteCollection();

        foreach ($routes as $name => $routeData) {
            [$pattern, $params, $methods] = $routeData;

            $route = new \Symfony\Component\Routing\Route(
                $pattern,
                array_merge($params, ['_controller' => [$this, 'handleRequest']]),
                [],  // requirements
                [],  // options
                '',  // host
                [],  // schemes
                $methods
            );

            $collection->add($name, $route);
        }

        // Replace existing routes with our collection
        $this->router->setRouteCollection($collection);
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
            // Let Symfony's router handle the routing
            $symfonyRequest = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            $params = $this->router->match($symfonyRequest->getPathInfo());

            // Update request with route parameters
            $request->merge($params);

            if ($request->invalid) {
                $handler = $this->handlerManager->createHandler('error');
                return $handler->handle($request);
            }

            $handler = $this->handlerManager->createFromRequest($request);
            return $handler->handle($request);

        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            return Response::sendError($request, 'Not Found', 404);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return Response::sendError($request, $e->getMessage());
        }
    }

    public function generateUrl(string $name, array $params = [], bool $absolute = false): string
    {
        try {
            $referenceType = $absolute
                ? \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
                : \Symfony\Component\Routing\Generator\UrlGeneratorInterface::RELATIVE_PATH;

            return $this->router->generate($name, $params, $referenceType);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Cannot generate URL for route '$name'", 0, $e);
        }
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->handlerManager;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }
}
