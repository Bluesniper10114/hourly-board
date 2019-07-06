<?php
namespace API\Login;

use \Settings\Config;
use \API\APIAssertions;
use \Core\HttpResponse;
use \Core\Exceptions\HttpBadRequestException;


/**
 * Supports login via the API
 */
class LoginController extends \Common\Login\LoginController
{
    /** @var LoginModel $model The model */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams, $model = null)
    {
        if (!isset($model)) {
            $model = new LoginModel();
        }
        parent::__construct($routeParams, $model);
    }

    /**
     * Logs in a user using username and password
     * Returns a token in the JSON payload 
     */
    public function loginProcessAction()
    {
        $data = APIAssertions::assertPost();
        if (!isset($data->username) || !isset($data->password)) {
            $ex = new HttpBadRequestException("Invalid parameters");
            if (!isset($data->username)){
                  $ex->errors[] = "Missing username";
            } 
            if (!isset($data->password)){
                $ex->errors[] = "Missing password";
          } 
          throw $ex;
        }

        $this->model->setUsername($data->username);
        $this->model->setPassword($data->password);
        $success = $this->model->login();
        if ($success) {
            $expires = getSetting("LoginExpiresInSeconds");
            $result = [
                "token" => $this->model->getToken(),
                "expires" => $expires
            ];
            $errors = [];
        } else {
            $result = null;
            $errors = $this->model->errorMessages;
        }
        $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
     * Logs out a user using their token.
     * The username must be provided to verify the token
     */
    public function logoutAction()
    {
        $data = APIAssertions::assertPost();
        if (!isset($data->username)) {
            $ex = new HttpBadRequestException("Invalid parameters");
            if (!isset($data->username)){
                  $ex->errors[] = "Missing username";
            } 
            throw $ex;
        }
        $this->model->setUsername($data->username);

        $token = $this->authenticationProvider->getToken(); // get the token somehow
        if (empty($token)) {
            $success = false;
            $errors = ["Missing or empty token"];
        } else {
            $this->model->setToken($token);
            if (!$this->model->tokenBelongsToUsername()) {
                $success = false;
                $errors = ["Token has either expired or it does not belong to this username"];
            } else {
                $success = $this->model->logout();
                if ($success) {
                    $errors = [];
                } else {
                    $errors = ["Could not logout user or user does not exist"];
                }        
            }            
        }
        $response = new HttpResponse(null, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

}