<?php

namespace My\App\Controller;

use Tale\Controller;

class OtherController extends Controller
{

    public function indexAction()
    {

        return $this->getResponse()->withStatus(103);
    }

    public function fiveAction()
    {

        return $this->getResponse()->withStatus(104);
    }

    public function sixAction()
    {

        return $this->getResponse()->withStatus(105);
    }
}