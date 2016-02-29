<?php

namespace My\App\Controller\Admin;

use Tale\Controller;

class OtherController extends Controller
{

    public function indexAction()
    {

        return $this->getResponse()->withStatus(100);
    }

    public function fiveAction()
    {

        return $this->getResponse()->withStatus(101);
    }

    public function sixAction()
    {

        return $this->getResponse()->withStatus(102);
    }
}