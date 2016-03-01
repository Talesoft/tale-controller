<?php

namespace My\App\Controller\Admin;

use Tale\Controller;

class OtherController extends Controller
{

    public function indexAction()
    {

        return $this->response->withStatus(100);
    }

    public function fiveAction()
    {

        return $this->response->withStatus(101);
    }

    public function sixAction()
    {

        return $this->response->withStatus(102);
    }
}