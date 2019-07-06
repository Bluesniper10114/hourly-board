<?php
namespace Core;

/**
 * Abstract base controller for MVC mechanism. Allows a route to be called, parameters to be passed.
 * Provides before and after triggers to allow authentication or other functionality to be developed on top of it.
 **/
abstract class Controller
{

    /** @var \Core\Model $model The model handling business logic for this controller */
    protected $model;

    /** @var array $routeParams Parameters from the matched route */
    protected $routeParams = [];

    /**
     * The application calling the controller
     *
     * @var \Core\IApplication
     */
    protected $application;

    /**
     * "Before" filters for the controller
     *
     * @var \Core\Filters\ControllerFilter[]
     */
    private $executionFilters = [];

        /**
     * The currently executing filter. It is used to pass the object between the before and the onFail functions.
     *
     * @var \Core\Filters\ControllerFilter
     */
    protected $currentFilter = null;


     /**
     * Gets the feature with which this route was called
     *
     * @return string|null
     */
    public function getFeatureBeingExecuted()
    {
        return isset($this->routeParams['feature']) ? $this->routeParams['feature'] : null;
    }

    /**
     * Gets the application object
     *
     * @return \Core\IApplication
     */
    public function getApplication()
    {
        return $this->application;
    }
    /**
     * Class constructor
     *
     * @param array $routeParams Parameters from the route
     * @param \Core\Model|null $model The model
     * @return void
     **/
    public function __construct($routeParams, $model = null)
    {
        $this->routeParams = $routeParams;
        if (!isset($routeParams[Router::RESERVED_APPLICATION]))
        {
            throw new \Exception("Internal error: application not set on controller");
        }
        $this->application = $routeParams[Router::RESERVED_APPLICATION];
        if (isset($model))
        {
            $this->model = $model;
            $this->model->application = $this->application;
        }
    }

    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * with an "Action" suffix, e.g. indexAction, showAction etc.
     *
     * @param string $name  Method name
     * @param string[] $args Arguments passed to the method
     * @return void
     * @throws \Exception Throws exception if the method is not found
     **/
    public function __call($name, $args)
    {
        if (empty($args)) {
            // copy the routeParams array
            // we will pass $args to the method we'll later call
            $args = array_merge([], $this->routeParams);

            // remove extra information, which interferes with the parameters format required by the method call
            unset($args['controller']);
            unset($args['action']);
            unset($args['authentication']);
            unset($args['feature']);
            unset($args['embed']);
            unset($args[Router::RESERVED_APPLICATION]);
            unset($args[Router::RESERVED_HTTP_METHOD]);
        }
        $method = $name . 'Action';
        if (method_exists($this, $method))
        {
            if ($this->before() !== false)
            {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
            else
            {
                $this->onFailBefore();
            }
        }
        else
        {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    /**
     * Before filter - called before an action method.
     *
     * @return bool Always true, override to return false when you don't want the
     * controller to execute anything (e.g when the user is not authenticated)
     **/
    protected function before()
    {
        foreach ($this->executionFilters as $filter) {
            $this->currentFilter = $filter;
            if (!$filter->before()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Called when @see Controller::Before() returns false.
     * Here you can set an error message or make a redirection.
     * @return void
     */
    protected function onFailBefore()
    {
        if (isset($this->currentFilter)) {
            $this->currentFilter->onFail();
        }
    }
    /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function after()
    {
    }

    /**
     * Adds a registers a new filter 
     * 
     * @param \Core\Filters\ControllerFilter $filter
     * @return void
     */
    protected function registerFilter($filter)
    {
        $this->executionFilters[] = $filter;
    }
}
