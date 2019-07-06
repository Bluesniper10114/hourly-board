<?php
namespace Management\Users;

use Former\Facades\Former;

use Illuminate\Validation;
use Illuminate\Filesystem;
use Illuminate\Translation;
use Core\Pagination\PaginationSettings;
/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class UsersController extends \Management\ManagementAuthenticatedController
{
    /** @var UsersModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new UsersModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Shows the users listing page
     *
     * @return void
     */
    public function indexAction()
    {
        /** @var \Core\Pagination\IPagination $paginationProvider */
        $paginationProvider = $this->application->getServiceProvider("ServiceProviders\PaginationServiceProvider");
        $this->model->pagination = $paginationProvider;

        // pageIndex
        $paginationSettings = $this->getPaginationSettings();
        $this->model->load($paginationSettings);
        $data = $this->model->serialize();

        /** @var \ServiceProviders\PaginationRendererServiceProvider $paginationRendererProvider */
        $paginationRendererProvider = $this->application->getServiceProvider("ServiceProviders\PaginationRendererServiceProvider");
        $data["paginationView"] = $paginationRendererProvider->render($paginationProvider, $paginationSettings);
        $this->render('Management/Users/UsersView.php', $data);
    }


    /**
     * Set language page
     *
     * @param string $languageCode
     * @return void
     */
    public function setLanguageAction($languageCode)
    {
        /** @var \ServiceProviders\LocalisationServiceProvider $localisationServiceProvider */
        $localisationServiceProvider = $this->application->getServiceProvider("ServiceProviders\LocalisationServiceProvider");
        $localisationServiceProvider->setCurrentLanguage($languageCode);
        header('location:' . $_SERVER['HTTP_REFERER']);
    }

    /**
     * @return PaginationSettings
     */
    private function getPaginationSettings()
    {
        $paginationSettings = new PaginationSettings;
        $paginationSettings->init($_GET);
        $paginationSettings->baseUrl = SITE_URL;
        $paginationSettings->relativeUrl = "management/users";

        return $paginationSettings;
    }

    /**
     * Edit user page
     *
     * @param int $userId The user id
     * @return void
     */
    public function editAction($userId)
    {
        if (count($_POST) > 0) {
            $filesystem = new Filesystem\Filesystem();
            $fileLoader = new Translation\FileLoader($filesystem, '');
            $translator = new Translation\Translator($fileLoader, 'en_US');

            $factory = new Validation\Factory($translator);
            if (isset($_POST['password']) && isset($_POST['password_confirmation'])) {
                $validator = $factory->make($_POST, $this->model->rulesPassword, $this->model->errorMessages);
                if ($validator->fails()) {
                    $this->model->errors = $validator->errors()->all();
                    Former::withErrors($validator);
                } else {
                    $password = $_POST['password'];
                    $profileId = $_POST['profileId'];
                    $this->changePassword($profileId, $password);
                }
            } else {
                $validator = $factory->make($_POST, $this->model->rules, $this->model->errorMessages);
                if ($validator->fails()) {
                    $this->model->errors = $validator->errors()->all();
                    Former::withErrors($validator);
                } else {
                    $user = new \DAL\Entities\User();
                    $user->init($_POST);
                    $this->model->updateUserData($user);
                }
            }

        }
        $this->model->loadSingle($userId);
        $data = $this->model->serialize();
        $this->render('Management/Users/EditUserView.php', $data);
    }

    /**
     * Delete user page
     *
     * @param int $profileId The profile id
     * @return void
     */
    public function deleteAction($profileId)
    {
        if ($this->model->deleteUser($profileId)) {
            \Extension\Zeus\Url::navigateToPage("management/users");
        }
    }

    /**
     * Changes the password for the current user
     * 
     * @param int $profileId The profile id
     * @param string $password New password via post
     * @return bool True if successful
     **/
    private function changePassword($profileId, $password)
    {
        if (empty($password)) {
            return false;
        }
        $res = $this->model->changePassword($profileId, $password);
        return $res;
    }

}