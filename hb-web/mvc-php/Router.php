<?php
namespace Core;

/**
 * Routes requests to their respective MVC
 **/
class Router
{
    const RESERVED_APPLICATION = "application";
    const RESERVED_HTTP_METHOD = "httpMethod";

    /** @var \Core\IApplication The application object providing access to all service providers */
    public $application;

    /** @var string $BaseNameSpace All controllers will be prefixed by this namespace */
    public $baseNameSpace = "";

    /** @var array $routes Associative array of routes (the routing table) */
    protected $routes = [];

    /** @var array $params Parameters from the matched route */
    protected $params = [];

    /**
     * Add a route to the routing table
     * @param string    $route The route URL
     * @param array     $params Parameters (controller, action, etc.)
     * @return void
     **/
    public function add($route, $params = [])
    {
        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<\1>[a-zA-Z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-zA-Z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }

    /**
     * Enforces a HTTP GET method on the route
     * @param string    $route The route URL
     * @param array     $params Parameters (controller, action, etc.)
     * @return void
     */
    public function addHttpGet($route, $params = [])
    {
        $params[self::RESERVED_HTTP_METHOD] = ['GET'];
        $this->add($route, $params);
    }

    /**
     * Enforces a HTTP POST method on the route
     * @param string    $route The route URL
     * @param array     $params Parameters (controller, action, etc.)
     * @return void
     */
    public function addHttpPost($route, $params = [])
    {
        $params[self::RESERVED_HTTP_METHOD] = ['POST'];
        $this->add($route, $params);
    }

    /**
     * Enforces HTTP GET and POST methods on the route
     * @param string    $route The route URL
     * @param array     $params Parameters (controller, action, etc.)
     * @return void
     */
    public function addHttpGetPost($route, $params = [])
    {
        $params[self::RESERVED_HTTP_METHOD] = ['POST', 'GET'];
        $this->add($route, $params);
    }

    /**
     * Enforces a HTTP PUT method on the route
     * @param string    $route The route URL
     * @param array     $params Parameters (controller, action, etc.)
     * @return void
     */
    public function addHttpPut($route, $params = [])
    {
        $params[self::RESERVED_HTTP_METHOD] = ['PUT'];
        $this->add($route, $params);
    }

    /**
     * Enforces a HTTP DELETE method on the route
     * @param string    $route The route URL
     * @param array     $params Parameters (controller, action, etc.)
     * @return void
     */
    public function addHttpDelete($route, $params = [])
    {
        $params[self::RESERVED_HTTP_METHOD] = ['DELETE'];
        $this->add($route, $params);
    }

    /**
    *    Get all the routes from the routing table
    *   @return object[] Routes registered
    */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     * @return bool True if a match found, false otherwise
     **/
    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                // Get named capture group values
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    /**
     * Get the currently matched parameters
     * @return array The parameters to be passed to the called method
     **/
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Dispatch the route, creating the controller object and running the action method
     * @param string $url The route URL
     * @throws \Exception
     * Throws an exception if:
     *      # the method does not exist
     *      # the controller does not exist
     *      # no rout is matched
     * @return void
     **/
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);
        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                $this->params[self::RESERVED_APPLICATION] = $this->application;
                $controllerObject = new $controller($this->params);

                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (is_callable([$controllerObject, $action])) {
                    $controllerObject->$action();
                } else {
                    throw new \Exception("Method $action (in controller $controller) not found");
                }
            } else {
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    /**
     * Convert the string with hyphens to StudlyCaps
     * e.g. post-authors => PostAuthors
     * @param string $string The string to convert
     * @return string
     **/
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase
     * e.g. add-new => addNew
     * @param string $string The string to convert
     * @return string
     **/
    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Remove the query string variables from the URL (if any).
     *
     * As the full query string is used for the route, any variables at the
     * end will need to be removed before the route is matched to the routing
     * table. For example:
     *
     * URL                           $_SERVER['QUERY_STRING']  Route
     * -------------------------------------------------------------------
     * localhost                     ''                        ''
     * localhost/?                   ''                        ''
     * localhost/?page=1             page=1                    ''
     * localhost/posts?page=1        posts&page=1              posts
     * localhost/posts/index         posts/index               posts/index
     * localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     * @param string $url The full URL
     * @return string The URL with the query string variables removed
     **/
    protected function removeQueryStringVariables($url)
    {
        if ($url !== '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     * @return string The request URL
     **/
    protected function getNamespace()
    {
        $namespace = $this->baseNameSpace . '\\';
        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}