<?php 
namespace Core\Exceptions;
use Core\HttpResponse;

/**
 * A 500 type exception will be thrown when an unknown error occurs 
 */
class HttpInternalServerErrorException extends APIException
{
    /**
     * @inheritDoc
     */
    public function getHttpResponseCode()
    {
        return HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * @inheritDoc
     */
    public function getErrors()
    {
        return ["Internal Server Error"];
    }

}