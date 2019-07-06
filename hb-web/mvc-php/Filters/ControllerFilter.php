<?php
namespace Core\Filters;

/**
 * Provides a mechanism to block any calls depending on the filter decision
 */
abstract class ControllerFilter
{
    /**
     * Application
     *
     * @var \Core\Application|null
     */
    public $application;

    /**
     * Url to redirect when "before" failed
     *
     * @var string
     */
    public $redirectUrl;

    /**
     * Errors message if "before" failed
     *
     * @var string
     */
    public $message;
    /**
     * The controller to which this filter will be applied
     *
     * @var \Core\Controller
     */
    protected $controller;

    /**
     * Builds the filter
     *
     * @param \Core\Controller $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return bool True if execution should continue
     **/
    abstract public function before();

    /**
     * What to do if the before filter fails
     *
     * @return void
     */
    public function onFail()
    {
        redirectTo($this->redirectUrl);
    }

    /**
     * Gets the application owning this controller
     *
     * @return \Core\IApplication
     */
    protected function getApplication()
    {
        return $this->controller->getApplication();
    }
}