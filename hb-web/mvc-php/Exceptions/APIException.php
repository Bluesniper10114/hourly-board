<?php 
namespace Core\Exceptions;
use Core\HttpResponse;

/**
 * Abstract API Exception defines a HTTP response code and 
 * error details.
 * 
 * Used with HttpResponse it can automatically output a detailed response
 */
abstract class APIException extends \Exception
{
    /** @var string[] List of errors detailing the exception */
    public $errors = [];

    /**
     * @return int Returns the HTTP response code for this exception: 200, 400, 500
     */
    public abstract function getHttpResponseCode();

    /**
     * @return string[] Array of error messages detailing the exception
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string[] Array of error messages detailing the exception, including the original message of the exception
     */
    protected function compileErrors()
    {
        return array_merge(
            [$this->getMessage()],
            $this->getErrors()
        );
    }

    /**
     * Creates a Http response from the exception
     * @return HttpResponse
     */
    public function getHttpResponse()
    {
        $errors = $this->compileErrors();
        $response = new HttpResponse([], false, $this->getHttpResponseCode(), $errors);
        return $response;
    }

    
}