<?php

namespace Tale\Test;

use Tale\App;
use Tale\Controller;
use Tale\Controller\Dispatcher;
use Tale\Http\ServerRequest;

class SingleController extends Controller
{

    public function indexAction()
    {

        return $this->getResponse()->withStatus(100);
    }

    public function twoAction()
    {

        return $this->getResponse()->withStatus(101);
    }
}

class ControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testSingleController()
    {

        $app = new App();
        $app->append(SingleController::class);

        $response = $app->run(new ServerRequest());
        $this->assertEquals(100, $response->getStatusCode());

        $response = $app->run((new ServerRequest())
            ->withAttribute('action', 'two')
        );
        $this->assertEquals(101, $response->getStatusCode());
    }

    public function testRouting()
    {

        $app = new App([
            'controller' => [
                'nameSpace' => 'My\\App\\Controller',
                'modules' => [
                    'adm' => 'Admin'
                ],
                'loader' => [
                    'path' => __DIR__.'/test-app/controllers'
                ]
            ]
        ]);

        $app->append(Dispatcher::class);

        $response = $app->run(new ServerRequest());
        $this->assertEquals(100, $response->getStatusCode());

        $response = $app->run((new ServerRequest())
            ->withAttribute('action', 'two')
        );
        $this->assertEquals(101, $response->getStatusCode());

        $response = $app->run((new ServerRequest())
            ->withAttribute('action', 'three')
        );
        $this->assertEquals(102, $response->getStatusCode());



        $response = $app->run((new ServerRequest())
            ->withAttribute('controller', 'other')
        );
        $this->assertEquals(103, $response->getStatusCode());

        $response = $app->run((new ServerRequest())
            ->withAttribute('controller', 'other')
            ->withAttribute('action', 'five')
        );
        $this->assertEquals(104, $response->getStatusCode());

        $response = $app->run((new ServerRequest())
            ->withAttribute('controller', 'other')
            ->withAttribute('action', 'six')
        );
        $this->assertEquals(105, $response->getStatusCode());



        $response = $app->run((new ServerRequest())
            ->withAttribute('module', 'adm')
            ->withAttribute('controller', 'other')
        );
        $this->assertEquals(100, $response->getStatusCode());

        $response = $app->run((new ServerRequest())
            ->withAttribute('module', 'adm')
            ->withAttribute('controller', 'other')
            ->withAttribute('action', 'five')
        );
        $this->assertEquals(101, $response->getStatusCode());

        $response = $app->run((new ServerRequest())
            ->withAttribute('module', 'adm')
            ->withAttribute('controller', 'other')
            ->withAttribute('action', 'six')
        );
        $this->assertEquals(102, $response->getStatusCode());
    }
}