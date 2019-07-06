<?php
namespace API;
use Core\Exceptions\HttpBadRequestException;

/**
 * Helper class to handle HTTP request predicates and enforce them
 */
class APIAssertions
{
    /**
     * Asserts that this call is a POST
     * 
     * @param bool $decode True if a json decoded object should be returned, otherwise the original JSON
     * string will be returned
     * @return mixed Gets a decoded object from the post
     * @throws HttpBadRequestException
     */
    public static function assertPost($decode = true)
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new HttpBadRequestException("POST message expected");
        }
        $jsonData = file_get_contents('php://input');
        if (is_null($jsonData)) {
            throw new HttpBadRequestException("POST message has no content");
        }
        
        if ($decode) {
            $data = json_decode($jsonData);
            return $data;
        }
        return $jsonData;
    }

    /**
     * Asserts that this call is a GET
     * 
     * @return void
     * @throws HttpBadRequestException
     */
    public static function assertGet()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            throw new HttpBadRequestException("GET message expected");
        }
    }
}