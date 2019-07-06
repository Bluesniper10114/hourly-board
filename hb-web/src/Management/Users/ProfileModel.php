<?php
namespace Management\Users;

use DAL\UsersDAL;

/**
 * Model for profile management
 */
class ProfileModel extends \Common\Model
{
    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $lastName;

    /** @var string|null */
    public $fullName;

    /** @var string|null */
    public $barcode;

    /** @var int|null */
    public $profileId;

    /** @var array|null */
    public $translationTitles;

    /** @var array|null */
    public $translationPlaceholder;

    /** @var array|null */
    public $translationHelp;

    /** @var UsersDAL */
    public $dal;

    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new UsersDAL();
        $this->rules = [
            'barcode' => 'required',
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
     * @return void
     * @throws \Exception When user profile not found
     */
    public function load()
    {
        $localisationProvider = $this->getLocalisationProvider();
        $translations = $localisationProvider->getTranslationsForKey("Profile");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations["LabelsTitle"];
        $this->translationPlaceholder = $translations["LabelsPlaceholder"];
        $this->translationHelp = $translations["LabelsHelp"];
        if (isset($this->profileId)) {
            $profile = $this->dal->getProfileByProfileId($this->profileId);
            $this->dal->close();
            if (isset($profile)) {
                $this->firstName = $profile->firstName;
                $this->lastName = $profile->lastName;
                $this->barcode = $profile->userName;
                $this->fullName = $profile->getFullName();
            }
        } else {
            throw new \Exception("Login User Profile not found");
        }
    }

    /**
     * Loads ProfileModel for dashboard page
     * @return void
     * @throws \Exception When user profile not found
     */
    public function loadForDashboard()
    {
        $localisationProvider = $this->getLocalisationProvider();
        $translations = $localisationProvider->getTranslationsForKey("Profile");
        $this->title = $translations["DashboardTitle"];
    }

    /**
     * Changes the password for current user
     *
     * @param string $password The new password
     * @return bool True on success
     * @throws \Exception When the profileid is null
     */
    public function changePassword($password)
    {
        $localisationProvider = $this->getLocalisationProvider();
        $translations = $localisationProvider->getTranslationsForKey("Profile");
        if (is_null($this->profileId)) {
            throw new \Exception("Internal error. ProfileId not set.");
        }
        $password = md5($password);
        $result = $this->dal->changePassword($this->profileId, $password);
        $this->dal->close();

        if ($result < 0) {
            $this->getApplication->onError($translations["Errors"]["Password"]);
            return false;
        }
        return true;
    }
}
