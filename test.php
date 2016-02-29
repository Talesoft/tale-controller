<?php

use Psr\Http\Message\ServerRequestInterface;
use Tale\App;
use Tale\Controller;

include 'vendor/autoload.php';


//Define a controller of some kind
class MyController extends Controller
{

    //GET|POST /
    public function indexAction()
    {

        $res = $this->getResponse();
        $res->getBody()->write('Hello index!');
        return $res;
    }

    //GET|POST /?action=about-us
    public function aboutUsAction()
    {

        $res = $this->getResponse();
        $res->getBody()->write('About us!');
        return $res;
    }

    //GET /?action=contact
    public function getContactAction()
    {

        $res = $this->getResponse();
        $res->getBody()->write('Contact form!');
        return $res;
    }

    //POST /?action=contact
    public function postContactAction()
    {

        //Handle contact form
        $res = $this->getResponse();
        $res->getBody()->write('Success!');
        return $res;
    }
}


//Dispatch via App
$app = new App();

//Make sure we can target the "action" somehow.
//Normally you'd use a router, we use a simple GET-variable in this case
//"index.php?action=about-us" would dispatch "MyController->aboutUsAction"

//This is a simple middleware mapping query's "action" to the required request attribute "action"
$app->append(function(ServerRequestInterface $req, $res, $next) {

    $params = $req->getQueryParams();
    $action = isset($params['action']) ? $params['action'] : null;

    if ($action)
        $req = $req->withAttribute('action', $action);

    return $next($req, $res);
});

//Append our controller middleware
$app->append(MyController::class);

//Display the app
$app->display();