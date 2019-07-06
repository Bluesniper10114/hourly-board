<?php
namespace Management\Users;
use Former\Facades\Former;

use Illuminate\Validation;
use Illuminate\Filesystem;
use Illuminate\Translation;
/**
 * Profile controller
 *
 * PHP version 7.0
 */
class ProfileController extends \Management\ManagementAuthenticatedController
{
    /** @var ProfileModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new ProfileModel();
        parent::__construct($routeParams, $model);
    }
    
    /**
     * Shows the dasboard page
     *
     * @return void
     */
    public function dashboardAction()
    {         
        $this->model->loadForDashboard();
        $data = $this->model->serialize();
        $this->render('Management/Users/DashboardView.php', $data);
    }

    /**
     * Shows the profile page
     *
     * @return void
     */
    public function profileAction()
    {
        if (count($_POST) > 0) {
            $filesystem = new Filesystem\Filesystem();
            $fileLoader = new Translation\FileLoader($filesystem, '');
            $translator = new Translation\Translator($fileLoader, 'en_US');

            $factory = new Validation\Factory($translator);
            $validator = $factory->make($_POST, $this->model->rules, $this->model->errorMessages);
            $val = [];

            if ($validator->fails()) {
                $this->model->errors = $validator->errors()->all();
                Former::withErrors($validator);
            } else {
                $password = $_REQUEST['password'];
                $repassword = $_REQUEST['password_confirmation'];

                if ($this->changePassword($password)) {
                    \Extension\Zeus\Url::navigateToPage("management");
                } else {
                    \Extension\Zeus\Url::navigateToPage("management/profile");
                }
            }
        }
        $this->model->profileId = $this->authenticationProvider->getProfileId();
        $this->model->load();
        $data = $this->model->serialize();

        $this->render('Management/Users/ProfileView.php', $data);
    }

    /**
     * Changes the password for the current user
     * @param string $password New password via post
     * @return bool True if successful
     **/
    private function changePassword($password)
    {
        if (empty($password)) {
            return false;
        }
        $this->model->profileId = $this->authenticationProvider->getProfileId();
        $res = $this->model->changePassword($password);

        $this->model->load();
        return $res;
    }

}