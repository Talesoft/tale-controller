<?php

namespace My\App\Controller;

use Tale\Controller;

class OtherController extends Controller
{

    public function indexAction()
    {

        return $this->response->withStatus(103);
    }

    public function fiveAction()
    {

        return $this->response->withStatus(104);
    }

    public function sixAction()
    {

        return $this->response->withStatus(105);
    }
}