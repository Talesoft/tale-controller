<?php

namespace Tale;

use Tale\Controller\Dispatcher;
use Tale\Http\Method;
use Tale\Http\Runtime\MiddlewareInterface;
use Tale\Http\Runtime\MiddlewareTrait;

class Controller implements MiddlewareInterface
{
    use MiddlewareTrait;

    private $_app;
    private $_dispatcher;

    public function __construct(App $app, Dispatcher $dispatcher = null)
    {

        $this->_app = $app;
        $this->_dispatcher = $dispatcher;
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->_app;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }

    protected function initialize()
    {

        return null;
    }

    protected function finalize()
    {

        return null;
    }

    protected function handleRequest()
    {

        $req = $this->getRequest();
        $action = $req->getAttribute('action', $this->_app->getOption(
            'controller.defaultAction',
            'index'
        ));
        $id = $req->getAttribute('id', $this->_app->getOption(
            'controller.defaultId',
            null
        ));
        $format = $req->getAttribute('format', $this->_app->getOption(
            'controller.defaultFormat',
            'html'
        ));

        //Make sure the values are correctly formatted
        if (Inflector::canonicalize($action) !== $action
            || Inflector::canonicalize($format) !== $format
            || (!is_null($id) && !is_numeric($id) && Inflector::canonicalize($id) !== $id))
            return $this->handleNext();

        $getActionPattern = $this->_app->getOption('controller.getActionPattern', 'get%sAction');
        $getActionInflection = $this->_app->getOption('controller.getActionInflection', [Inflector::class, 'camelize']);
        $postActionPattern = $this->_app->getOption('controller.postActionPattern', 'post%sAction');
        $postActionInflection = $this->_app->getOption('controller.postActionInflection', [Inflector::class, 'camelize']);
        $actionPattern = $this->_app->getOption('controller.actionPattern', '%sAction');
        $actionInflection = $this->_app->getOption('controller.actionInflection', [Inflector::class, 'variablize']);

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
            return $this->handleNext();

        if ($result = $this->initialize())
            return $result;

        if ($result = call_user_func([$this, $foundMethodName], $id))
            return $result;

        if ($result = $this->finalize())
            return $result;

        return $this->handleNext();
    }
}