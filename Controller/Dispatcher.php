<?php

namespace Tale\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tale\App;
use Tale\Config\DelegateTrait;
use Tale\Controller;
use Tale\Http\Runtime\MiddlewareInterface;
use Tale\Http\Runtime\MiddlewareTrait;
use Tale\Inflector;
use Tale\Loader;

class Dispatcher implements MiddlewareInterface
{
    use MiddlewareTrait;
    use DelegateTrait;

    private $_app;

    public function __construct(App $app)
    {

        $this->_app = $app;
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->_app;
    }

    public function dispatchRequest(ServerRequestInterface $request = null, ResponseInterface $response = null)
    {

        $request = $request ?: $this->getRequest();
        $response = $response ?: $this->getResponse();

        if (!$request || !$response)
            throw new \RuntimeException(
                "Failed to dispatch: Dispatcher was not handled through middleware ".
                "and is missing either the request or the response argument"
            );

        $nameSpace = trim($this->getOption('nameSpace', ''), '\\');
        $modules = $this->getOption('modules', []);

        $module = $request->getAttribute('module', $this->getOption(
            'defaultModule',
            ''
        ));
        $controller = $request->getAttribute('controller', $this->getOption(
            'defaultController',
            'index'
        ));

        //Make sure the values are correctly formatted
        if (Inflector::canonicalize($module) !== $module
            || Inflector::canonicalize($controller) !== $controller)
            return $response;

        $controllerPattern = $this->getOption('controllerPattern', '%sController');
        $controllerInflection = $this->getOption('controllerInflection', [Inflector::class, 'camelize']);

        $controllerClassName = sprintf($controllerPattern, call_user_func($controllerInflection, $controller));

        if (!empty($module)) {

            if (!isset($modules[$module]))
                return $response;

            $controllerClassName = trim($modules[$module], '\\')."\\$controllerClassName";
        }

        if (!empty($nameSpace))
            $controllerClassName = "$nameSpace\\$controllerClassName";

        if (!class_exists($controllerClassName, false)) {

            $loaderEnabled = $this->getOption('loader.enabled', false);
            $loaderPath = $this->getOption('loader.path', getcwd().'/controllers');
            $loaderPattern = $this->getOption('loader.pattern', null);
            $loader = null;

            if ($loaderEnabled) {

                $loader = new Loader($loaderPath, empty($nameSpace) ? null : $nameSpace, $loaderPattern);
                $loader->register();
            }

            if (!class_exists($controllerClassName) || !is_a($controllerClassName, Controller::class, true))
                return $response;

            if ($loaderEnabled) {

                $loader->unregister();
                $loader = null;
            }
        }

        $this->_app->prepend($controllerClassName);
        return $response;
    }

    public function dispatch(
        array $attributes,
        $preserve = true,
        ServerRequestInterface $request = null,
        ResponseInterface $response = null
    )
    {

        $request = $request ?: $this->getRequest();

        if (!$preserve)
            $attributes = array_replace([
                'module' => null,
                'controller' => null,
                'action' => null,
                'id' => null,
                'format' => null
            ], $attributes);

        foreach ($attributes as $key => $value)
            $request = $request->withAttribute($key, $value);

        return $this->dispatchRequest($request, $response);
    }

    protected function handleRequest()
    {

        return $this->handleNext(null, $this->dispatchRequest());
    }

    protected function getOptionNameSpace()
    {

        return 'controller';
    }

    protected function getTargetConfigurableObject()
    {

        return $this->_app;
    }
}