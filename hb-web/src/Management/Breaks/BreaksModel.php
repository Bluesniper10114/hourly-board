<?php
namespace Management\Breaks;

use DAL\BreaksDAL;

/**
 * Model for breaks
 */
class BreaksModel extends \Common\Model
{
    /** @var string|null */
    public $xmlCurrentWeek;

    /** @var string|null */
    public $xmlNextWeek;


    /** @var string */
    public $xmlOutput;

    /** @var string */
    public $error = '';

    /** @var array|null */
    public $translationTitles;

    /** @var array|null */
    public $translationErrors;

    public $breaksOptions;

    /** @var BreaksDAL */
    public $dal;



    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new BreaksDAL();
    }
    /**
     * Loads BreaksModel
     * @param int|null $profileId Profile of user attempting the load
     * @return void
     */
    public function load($profileId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Breaks");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations["LabelsTitle"];
        $this->translationErrors = $translations["Errors"];
        $messages = $translations["JsValidateMessages"];
        $this->breaksOptions = ['location' => 'TM', 'breaksDurationPerShift' => 40, 'messages' => $messages ];
        if (!is_null($profileId)) {
            $this->xmlCurrentWeek = $this->dal->getBreaks($profileId, 0);
            $this->xmlNextWeek = $this->dal->getBreaks($profileId, 1);
        }
    }
    /**
     * Save breaks xml
     *
     * @param int|null $profileId Profile of user attempting the load
     * @return bool True on success
     */
    public function save($profileId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Breaks");
        $result = $this->dal->saveBreaks($profileId, $this->xmlOutput);
        if (!$result['success']) {
            $this->error = $result['error'];
            return false;
        }
        return true;
    }
}