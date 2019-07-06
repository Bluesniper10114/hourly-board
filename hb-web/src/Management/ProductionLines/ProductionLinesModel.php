<?php
namespace Management\ProductionLines;

use DAL\LayoutDAL;

/**
 * Model for ProductionLines
 */
class ProductionLinesModel extends \Common\Model
{
    /** @var string|null */
    public $xmlInput;

    /** @var string|null */
    public $location;
    
    /** @var array|null */
    public $translationTitles;

    /** @var array|null */
    public $translationErrors;

    /** @var LayoutDAL */
    public $dal;



    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new LayoutDAL();
        $this->location = 'TM';
    }
    /**
     * Loads BreaksModel
     * @param int|null $profileId Profile of user attempting the load
     * @return void
     */
    public function load($profileId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Lines");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations["LabelsTitle"];
        $this->translationErrors = $translations["LabelsErrors"];
        if (!is_null($profileId)) {
            $this->xmlInput = $this->dal->getMachines($profileId);
        }
    }
}