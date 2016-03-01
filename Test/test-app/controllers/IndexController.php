<?php

namespace My\App\Controller;

use Tale\Controller;

class IndexController extends Controller
{

    public function indexAction()
    {

        return $this->response->withStatus(100);
    }

    public function twoAction()
    {

        return $this->response->withStatus(101);
    }

    public function threeAction()
    {

        return $this->response->withStatus(102);
    }
}