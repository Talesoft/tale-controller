
# Tale Controller
**A Tale Framework Component**

# What is Tale Controller?

A middleware for `talesoft/tale-app` that allows easy instanciation and handling of controllers.

You can either use single, static controllers or use a dispatcher that automatically handles everything.

# Installation

Install via Composer

```bash
composer require "talesoft/tale-controller:*"
composer install
```

# Usage

## Single usage

A single controller can make up a whole website.
This is really useful for small websites with 5-10 sub-pages.
No configuration needed.

```php

use Tale\App;
use Tale\Controller;

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


//Create a new app context
$app = new App();

//Make sure we can target the "action" somehow.
//Normally you'd use a router, we use a simple GET-variable in this case
//"index.php?action=about-us" would dispatch "MyController->aboutUsAction"

//This is a simple middleware mapping query's "action" to the required request attribute "action"
$app->append(function($req, $res, $next) {

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

```

## Using the Dispatcher

When apps get larger, you want to split functionality into single modules.
With the Dispatcher you can control an automatic controller dispatching mechanism.

Imagine the following controller structure:
```
/
    /index.php
    /app
        /controllers
            IndexController.php
            ContactController.php
            PortfolioController.php
            /Admin
                /IndexController.php
```
                
                
This is a common case that the dispatcher can handle with a low configuration profile.

```php

use Tale\App;
use Tale\Controller\Dispatcher;

//Create a new app context
$app = new App([
    'controller' => [
        'nameSpace' => 'My\\Controllers',
        'loader' => ['path' => __DIR__.'/app/controllers']
    ]
]);

//This is a middleware mapping "module", "controller" and "action" GET-values to
//ServerRequestInterface-attributes
$app->append(function($req, $res, $next) {

    $params = $req->getQueryParams();
    $module = isset($params['module']) ? $params['module'] : null;
    $controller = isset($params['controller']) ? $params['controller'] : null;
    $action = isset($params['action']) ? $params['action'] : null;

    if ($module)
        $req = $req->withAttribute('module', $module);
            
    if ($controller)
        $req = $req->withAttribute('controller', $controller);

    if ($action)
        $req = $req->withAttribute('action', $action);

    return $next($req, $res);
});

//Append our dispatcher middleware
$app->append(Dispatcher::class);

//Display the app
$app->display();
```

Now you could call the `editAction` of the `Admin\IndexController` by requesting
`index.php?module=admin&action=edit`

Notice that all values are completely optional.

## ServerRequestInterface attributes

The following attributes are handled by the dispatcher:

### `module` (Default: `null`)

Tells the dispatcher which namespace to find controllers in.
The `controller.nameSpace` option will be prepended in any case.

### `controller` (Default: `index`)

Tells the dispatcher, which controller to load.
`my-blog` will be parsed to `MyBlogController`

The following attributes are handled by the controllers:

### `action` (Default: `index`)

Tells the controller which action to call.
`edit-user` will be parsed to `editUserAction`

If there's an `getEditUserAction`-method, that one will only listen to `GET`-requests
The same goes for `POST`-requests with `postEditUserAction`.
Not prefixing will handle all request methods.

### `id` (Default: null)

Specifies the first parameter given to the action.
Allowed values are numerical values and canonical strings (`some-user-name`)

### `format` (Default: html)

Specifies the format the result should appear in.
This mostly equals the file extension of the called URI (`/some-file.xml` will yield format `xml`)

This format is to be used by some kind of output formatter/renderer.


## Handle 404-errors

What if there's no fitting controller/it doesn't extend the correct class/the input is malformed etc.

That's all checked by tale-controller. Upon any kind of failure, control will be passed on
to the next middleware.

Handling 404 is as simple as adding an "end"-middleware that results in said 404-error

```php

$app->append(Dispatcher::class)
    ->append(function($req, $res) {
        
        $res->getBody()->write('<h1>404 - Not found!</h1>');
        return $res->withStatus(404);
    });
```

## Shorten things up

This module is specially designed to work with the `Tale\Router`.
You can use it stand-alone, but it will require extra-work (but is still really cool!)

Here's an example of how it could look like by installing `talesoft/tale-router` via composer

**env.json**
```json
{
    "middlewares": ["Tale\\Router"],
    "routes": {
        "/blog/:action?/:id?": "My\\Controller\\BlogController",
        "/:controller?/:action?/:id?.:format?": "Tale\\Controller\\Dispatcher"
    },
    "controller": {
        "nameSpace": "My\\Controller",
        "loader": {
            "path": "{{path}}/app/controllers"
        }
    }
}
```php

**index.php**
```php

use Tale\App;

$app = new App(['path' => __DIR__]);
$app->display();
```


## Configuration options

All configuration options.

```
controller.defaultModule            The default module to use (Default: null)
controller.defaultController        The default controller to use (Default: index)
controller.defaultAction            The default action to use (Default: index)
controller.defaultId                The default ID to use (Default: null)
controller.defaultFormat            The default format to use (Default: html)

controller.nameSpace                The namespace where controllers reside in (Default: null)
controller.modules                  A map [module-name => namespace] for module mapping

controller.controllerPattern        The pattern for controllers (Default: %sController)
controller.controllerInflection     How to inflect the controller name (Default: [Tale\Inflector, camelize]

controller.actionPattern            The pattern for actions (Default: %sAction)
controller.actonInflection          How to inflect the action name (Default: [Tale\Inflector, variablize]
controller.getActionPattern         The pattern for GET actions (Default: get%sAction)
controller.getActonInflection       How to inflect the GET action name (Default: [Tale\Inflector, camelize]
controller.postActionPattern        The pattern for POST actions (Default: post%sAction)
controller.postActonInflection      How to inflect the POST action name (Default: [Tale\Inflector, camelize]

controller.loader.enabled           Enable an auto-loader for controllers (Default: true)
controller.loader.path              The path for controller classes (Default: getcwd()/controllers)
controller.loader.pattern           The pattern for controller loading (Default: %s.php)
```

## Using multiple dispatchers

Using multiple dispatchers is as easy as extending the dispatcher.
You can set an option namespace to load different configuration values.

```php

class FirstDispatcher
{

    public function getOptionNameSpace()
    {   
    
        return 'firstDispatcher';
    }
}


class SecondDispatcher
{

    public function getOptionNameSpace()
    {   
    
        return 'secondDispatcher';
    }
}

$app->get(Router::class)
    ->all('/:controller?/:action?', FirstDispatcher::class)
    ->all('/sub-module/:controller?/:action?', SecondDispatcher::class);

$app->display();
```