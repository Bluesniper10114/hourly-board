<?php
namespace Core;

/**
 * 
 */
class HttpResponse
{

    const HTTP_OK = 200;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * @var int $httpResponseCode HTTP response code  
     */
    public $httpResponseCode = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * @var mixed $content Content array to be serialized 
     */
    public $content = null;

    /**
     * @var bool $success True if operation succeeded
     */
    public $success = false;

    /**
     * @var array $responseHeaders 
     */
    public $responseHeaders = [];

    /**
     * @var string[] $errors Detailed error messages explaining a failure
     */
    public $errors = [];

    /**
     * @param mixed $content An array containing information to be serialized in a JSON
     * @param bool $success True if operation succeeded
     * @param int $httpResponseCode One of the standard HTTP response codes
     * @param string[] $errors A List of error messages explaining the failed outcome
     * @param array $headers Additional headers to be posted in the response
     */
    public function __construct($content = null, $success = true, $httpResponseCode = 200, $errors = [], $headers = [])
    {   
        $this->httpResponseCode = $httpResponseCode;
        $this->responseHeaders = array_merge(
            $headers,
            ["Content-Type" => "application/json"]
        );
        $this->errors = $errors;
        $this->success = $success;
        $this->content = $content;
    }

    /**
     * Prepares the error messages for JSON encoding
     * @return array Error messages in array format
     */
    public function getErrorMessagesArray()
    {
        $result = [];
        $index = 0;
        foreach ($this->errors as $error) {
            $result[$index] = ["error" => $error];
            $index++;
        }
        return $result;
    }

    /**
     * Generates the output of the response.
     */
    public function output()
    {
        header($this->message, true, $this->httpResponseCode);
        foreach ($this->responseHeaders as $key => $header) {
            header("$key: $header");
        }

        $errorMessages = $this->getErrorMessagesArray();
        $jsonContent = json_encode(
            [
                "success" => $this->success,
                "content" => $this->content,
                "errors" => $errorMessages
            ]
        );
        echo $jsonContent;
    }

    

}
