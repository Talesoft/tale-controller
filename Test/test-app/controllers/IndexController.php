<?php

namespace My\App\Controller;

use Tale\Controller;

class IndexController extends Controller
{

    public function indexAction()
    {

        return $this->getResponse()->withStatus(100);
    }

    public function twoAction()
    {

        return $this->getResponse()->withStatus(101);
    }

    public function threeAction()
    {

        return $this->getResponse()->withStatus(102);
    }
}