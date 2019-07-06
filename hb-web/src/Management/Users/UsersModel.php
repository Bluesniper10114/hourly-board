<?php
namespace Management\Users;

use DAL\UsersDAL;
use Core\Pagination\IPagination;
use Core\Pagination\PaginationResponse;
use Core\Pagination\PaginationSettings;
use DAL\Entities\User;

/**
 * Model for multiple users management
 */
class UsersModel extends \Common\Model implements \Core\Pagination\IPaginationDataSource
{
    /** @var UsersDAL */
    public $dal;

    /** @var array|null */
    public $usersList;

    /** @var \DAL\Entities\User|null */
    public $user;

    public $dataItemsFromToText;

    /** @var array|null */
    public $translationTitles;

    /** @var array|null */
    public $translationForm;

    /** @var array|null */
    public $translationMessages;

    /** @var array|null */
    public $levelsList;

    /** @var string|null */
    public $message;

    /**
     * Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new UsersDAL();
        $this->rules = [
            'firstName' => 'required',
            'lastName' => 'required',
            'levelId' => 'required',
        ];
        $this->rulesPassword = [
            'password' => 'required|confirmed',
        ];
        $this->errorMessages = [
            'required' => 'The :attribute field is required.',
            'Integer' => 'The :attribute field should be an integer',
            'confirmed' => ':attribute does not match.',
        ];
    }
    /**
     * Loads ProfileModel
     * @param \Core\Pagination\PaginationSettings $paginationSettings
     * @return void
     */
    public function load($paginationSettings)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Users");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations['LabelsTitle'];
        $this->translationForm = $translations['Form'];
        $this->translationMessages = $translations['Messages'];
        $paginationSettings->itemsOnPage = 10;
        $this->usersList = $this->loadPagination($paginationSettings, $this);
    }

    /**
     * Gets all levels list
     * 
     * @return array
     */
    public function getLevelsList()
    {
        $levels = $this->dal->getLevelsList();
        $result = [];
        foreach ($levels as $level) {
            $result[$level['id']] = $level['name'];
        }
        return $result;
    }

    /**
     * Loads ProfileModel
     * @param int $userId The user id
     * @return void
     */
    public function loadSingle($userId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Users");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations['LabelsTitle'];
        $this->translationForm = $translations['Form'];
        $this->translationMessages = $translations['Messages'];
        $this->user = $this->dal->getUser($userId);
        $this->levelsList = $this->getLevelsList();
    }

    /**
     * @inheritDoc
     **/
    public function getTotalItemsOnServer($paginationSettings)
    {
        return $this->dal->countUsers($paginationSettings->filter);
    }

    /**
     * @inheritDoc
     **/
    public function getItemsOnPage($paginationSettings)
    {
        return $this->dal->getUsersList(
            $paginationSettings->getSkipItems(),
            $paginationSettings->itemsOnPage,
            $paginationSettings->filter
        );
    }

    /**
     * Changes the password for current user
     *
     * @param int $profileId The profile id
     * @param string $password The new password
     * @return bool True on success
     */
    public function changePassword($profileId, $password)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Users");
        $password = md5($password);
        $result = $this->dal->changePassword($profileId, $password);

        if ($result < 0) {
            $this->getApplication->onError($translations["Errors"]["Password"]);
            return false;
        }
        $this->message = $translations["Messages"]["UpdatePassword"];
        return true;
    }

    /**
     * delete user by profile id
     *
     * @param int $profileId The profile id
     * @return bool True on success
     */
    public function deleteUser($profileId)
    {
        $result = $this->dal->deleteUser($profileId);
        if ($result < 0) {
            return false;
        }
        return true;
    }

    /**
     * Update user data by using user entity object
     *
     * @param \DAL\Entities\User $user
     * @return bool True on success
     */
    public function updateUserData($user)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Users");
        $result = $this->dal->updateUserData($user);

        if (!$result) {
            $this->getApplication->onError($translations["Errors"]["Update"]);
            return false;
        }
        $this->message = $translations["Messages"]["UpdateProfile"];
        return true;
    }
}
