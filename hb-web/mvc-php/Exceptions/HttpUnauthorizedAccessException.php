<?php 
namespace Core\Exceptions;
use Core\HttpResponse;

/**
 * A bad request is a HTTP Response with code 400
 * It usually signals the input from the call was not as expected (missing parameters or wrong ranges)
 */
class HttpUnauthorizedAccessException extends APIException
{
    /**
     * @inheritDoc
     */
    public function getHttpResponseCode()
    {
        return HttpResponse::HTTP_OK;
    }

    /**
     * @inheritDoc
     */
    public function getErrors()
    {
        return ["Unauthorized access"];
    }
}