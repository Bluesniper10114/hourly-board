<?php

namespace Management\Login;

/**
 * Deals controller
 *
 * PHP version 7.0
 */
class LoginController extends \Common\Login\LoginController
{
    /** @var LoginModel $model The model handling business logic for this controller */
    protected $model;

    public $loginURL;
    public $insideURL;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new LoginModel;
        parent::__construct($routeParams, $model);
        $this->loginURL = 'management/login';
        $this->insideURL = 'management';

        $token = $this->authenticationProvider->getToken();
        $profileId = $this->authenticationProvider->getProfileId();
        if (!is_null($token) && !is_null($profileId)) {
            $model->setToken($token);
            $model->setProfileId($profileId);
        }
    }

    /**
     * Shows the login page or navigates inside the application if a token exists
     *
     * @return void
     */
    public function indexAction()
    {
        if ($this->model->isTokenValidForProfile()) {
            \Extension\Zeus\Url::navigateToPage("management/");
        } else {
            $this->model->load();
            $data = $this->model->serialize();
            $this->render('Management/Login/LoginView.php', $data);
        }
    }

    /**
     * Do the login using username and password.
     * If successful, navigate to the dashboard.
     * If failed, add an error message and show the login screen again.
     * @return void
     */
    public function loginProcessAction()
    {
        $this->model->load();
        if (!empty($_POST)) {
            $this->model->setToken(null);
            $this->model->setUsername($_POST['username']);
            $this->model->setPassword($_POST['password']);
            $success = $this->model->login();

            if ($success) {
                $profileId = $this->model->getProfileId();
                $token = $this->model->getToken();
                $role = $this->model->getRole();
                if (!empty($profileId) && !empty($token) && !empty($role)) {
                    $this->save($profileId, $token, $role);
                    \Extension\Zeus\Url::navigateToPage($this->insideURL);
                } else {
                    $this->model->message = "Internal error: authentication succeeded, however one of profileId or token or role is empty!";
                }
            }
            // the error message is in $this->model->message
            $data = $this->model->serialize();
            $this->render('Management/Login/LoginView.php', $data);
        }
    }

    /**
     * Logout
     *
     * Performs the logout on the model. Clears the session details
     *
     * @return void
     **/
    public function logoutAction()
    {
        $this->logout();
        \Extension\Zeus\Url::navigateToPage($this->loginURL);
    }
}