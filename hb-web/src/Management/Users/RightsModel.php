<?php
namespace Management\Users;
use DAL\UsersDAL;

/**
    * Model for profile management
 */
class RightsModel extends \Common\Model
{
    /** @var string|null */
    public $xmlInput;

    /** @var string */
    public $xmlOutput;

    /** @var array|null */
    public $translationTitles;

    /** @var array|null */
    public $translationErrors;

    /** @var UsersDAL */
    public $dal;



    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new UsersDAL();
    }
    /**
     * Loads RightsModel
     * @param int|null $profileId Profile of user attempting the load
     * @return void
     */
    public function load($profileId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Rights");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations["LabelsTitle"];
        $this->translationErrors = $translations["Errors"];

        if (!is_null($profileId)) {
            $this->xmlInput = $this->dal->getUserRights($profileId);
        }
    }
    /**
    * Save rights xml
    *
    * @param int|null $profileId Id of the user attempting to save
    * @return bool True on success
    */
    public function save($profileId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Rights");
        if (is_null($profileId)) {
            return false;
        }
        if ($this->dal->saveUserRights($profileId, $this->xmlOutput)) {
            $this->getApplication->onError($translations["Errors"]["Save"]);
            return false;
        }
        return true;
    }
}