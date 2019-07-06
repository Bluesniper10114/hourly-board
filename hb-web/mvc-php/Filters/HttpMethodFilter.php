<?php
namespace Core\Filters;
use Core\Exceptions\HttpBadRequestException;

/**
 * Filters route calls based on httpMethod
 */
class HttpMethodFilter extends ControllerFilter
{
    /** @var string[] Allowed Http request methods */
    protected $allowedMethods = ['OPTIONS'];

    /** @var string $allowedOrigin  */
    public $allowedOrigin = "";

    /** @var int $maxAge Maximum preflight request age for the OPTIONS request */
    public $maxAge = 1728000; // 20 days
    
    /**
     * @inheritDoc
     */
    public function onFail()
    {
        throw new HttpBadRequestException();
    }

    /**
     * Sets the allowed methods array
     * @param string[]|null $methods Allowed methods
     * @return void
     */
    public function setAllowedMethods($methods)
    {
        $this->allowedMethods = ['OPTIONS'];
        if (!isset($methods)) {
            return;
        }
        $this->allowedMethods = array_merge($this->allowedMethods, $methods);
    }

    /**
     * @inheritDoc
     */
    public function before()
    {
        $allowedMethods = implode(",", $this->allowedMethods);
        
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'OPTIONS') {
            if (!empty($this->allowedOrigin)) {
                header("Access-Control-Allow-Origin: $this->allowedOrigin");
            }
            header("Access-Control-Allow-Methods: $allowedMethods");
            header("Access-Control-Max-Age: $this->maxAge");
            header("Content-Length: 0");
            header("Content-Type: text/plain");    
            exit(0);
        } elseif (!in_array($method, $this->allowedMethods, true)) {
            die("This HTTP Resource can ONLY be accessed with $allowedMethods");
        }
        return true;
    }
}
