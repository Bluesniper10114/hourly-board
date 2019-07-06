<?php
namespace Management\Planning\ByDay;

use DAL\PlanningDAL;

/**
 * Model for ByDay
 */
class ByDayModel extends \Common\Model
{
    /** @var array */
    public $xmlData;

    /** @var string */
    public $error = '';

    /** @var string|null */
    public $search = null;

    /** @var string|null */
    public $searchDate = "";

    /** @var array|null */
    public $translationHeader;

    /** @var array|null */
    public $translationMonths;

    /** @var array|null */
    public $translationDays;

    /** @var array|null */
    public $translationError;

    /** @var PlanningDAL */
    public $dal;

    /** @var boolean */
    public $debug = false;

    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new PlanningDAL();
    }

    /**
     * Loads ByDayModel
     * @param int $profileId id of user
     * @param string|null $tags search tags
     * @return void
     */
    public function load($profileId, $tags)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("DailyPlanning");
        $this->title = $translations["Title"];
        $this->translationHeader = $translations["HeaderLabels"];
        $this->translationError = $translations["Errors"];
        $this->translationMonths = $translations["Months"];
        $this->translationDays = $translations["Days"];
        $this->xmlData = $this->dal->getByDayData($profileId, $tags);
    }

    /**
     * Save Daily Planning
     *
     * @param int|null $profileId
     * @param string $xml
     * @return string|null
     */
    public function saveByDay($profileId, $xml)
    {
        $error = $this->dal->saveByDay($profileId, $xml);
        return $error;
    }

    /**
     * Get line name by id
     *
     * @param int|null $lineId
     * @return string|null
     */
    public function getLineName($lineId)
    {
        $data = $this->dal->getLineName($lineId);
        return $data;
    }
}