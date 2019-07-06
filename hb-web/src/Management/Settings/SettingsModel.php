<?php
namespace Management\Settings;
use DAL\SettingsDAL;

/**
 * Model for Settings management
 */
class SettingsModel extends \Common\Model
{
    /** @var SettingsDAL */
    public $dal;

    public $key;
    public $value;
    public $note;
    public $settingsList;

    public $translationTitles;
    public $translationPlaceholder;

    /**
     *Connects to the DAL and sets key value
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new SettingsDAL();
        $this->rules = [
            'value' => 'required',
        ];
        $this->errorMessages = [
            'required' => 'The :attribute field is required.',
        ];
    }

    /**
     * Loads SettingsModel
     * @return void
     */
    public function load()
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Settings");
        $this->translationTitles = $translations["LabelsTitle"];
        $this->translationPlaceholder = $translations["LabelsPlaceholder"];
        $this->title = $translations["TitleGeneral"];
        if (isset($this->key)) {
            $this->value = $this->dal->getSettingForKey($this->key);
        }
    }

    /**
     *
     * initialize list of settings
     * @return void
     */
    public function getSettingList()
    {
        $this->settingsList = $this->dal->getListOfSettings();
    }

    /**
     *
     * Save Setting row
     * @return true
     */
    public function save()
    {
        $this->dal->updateSettings($this->key, $this->value, $this->note);
        return true;
    }
}