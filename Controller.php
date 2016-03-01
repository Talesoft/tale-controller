<?php

namespace Tale;

use Psr\Http\Message\ResponseInterface;
use Tale\Config\DelegateTrait;
use Tale\Http\Method;
use Tale\Http\Runtime\MiddlewareInterface;
use Tale\Http\Runtime\MiddlewareTrait;

class Controller implements MiddlewareInterface
{
    use MiddlewareTrait;
    use DelegateTrait;

    protected $app;

    public function __construct(App $app)
    {

        $this->app = $app;
    }

    /**
     * @return App
     */
    public function getApp()
    {

        return $this->app;
    }

    protected function initialize()
    {

        return null;
    }

    protected function finalize()
    {

        return null;
    }

    protected function handleRequest(callable $next)
    {

        $req = $this->request;
        $action = $req->getAttribute('action', $this->getOption('defaultAction', 'index'));
        $id = $req->getAttribute('id', $this->getOption('defaultId', null));
        $format = $req->getAttribute('format', $this->getOption('defaultFormat', 'html'));

        //Make sure the values are correctly formatted
        if (Inflector::canonicalize($action) !== $action
            || Inflector::canonicalize($format) !== $format
            || (!is_null($id) && !is_numeric($id) && Inflector::canonicalize($id) !== $id))
            return $next($this->request, $this->response);

        $getActionPattern = $this->getOption('getActionPattern', 'get%sAction');
        $getActionInflection = $this->getOption('getActionInflection', [Inflector::class, 'camelize']);
        $postActionPattern = $this->getOption('postActionPattern', 'post%sAction');
        $postActionInflection = $this->getOption('postActionInflection', [Inflector::class, 'camelize']);
        $actionPattern = $this->getOption('actionPattern', '%sAction');
        $actionInflection = $this->getOption('actionInflection', [Inflector::class, 'variablize']);

        $getMethodName = sprintf($getActionPattern, call_user_func($getActionInflection, $action));
        $postMethodName = sprintf($postActionPattern, call_user_func($postActionInflection, $action));
        $methodName = sprintf($actionPattern, call_user_func($actionInflection, $action));

        $foundMethodName = null;
        if ($req->getMethod() === Method::GET && method_exists($this, $getMethodName))
            $foundMethodName = $getMethodName;
        else if ($req->getMethod() === Method::POST && method_exists($this, $postMethodName))
            $foundMethodName = $postMethodName;
        else if (method_exists($this, $methodName))
            $foundMethodName = $methodName;

        if (!$foundMethodName)
            return $next($this->request, $this->response);

        if (($result = $this->initialize())
            || ($result = call_user_func([$this, $foundMethodName], $id))
            || ($result = $this->finalize())) {

            if (!($result instanceof ResponseInterface))
                throw new \RuntimeException(
                    "Failed to dispatch controller: Called action $foundMethodName ".
                    "doesn't return a ".ResponseInterface::class." instance"
                );

            return $next($this->request, $result);
        }

        return $next($this->request, $this->response);
    }

    protected function getOptionNameSpace()
    {

        return 'controller';
    }

    protected function getTargetConfigurableObject()
    {

        return $this->app;
    }
}