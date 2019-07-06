<?php
namespace Management\Planning\Datasets;

use DAL\LayoutDAL;

/**
 * Model for Datasets
 */
class DatasetsModel extends \Common\Model
{
    /** @var string|null */
    public $xmlInput;


    /** @var string */
    public $xmlOutput;

    /** @var string */
    public $error = '';

    /** @var array|null */
    public $translationTitles;

    /** @var array|null */
    public $translationErrors;

    /** @var array|null */
    public $xmlOptions;

    /** @var LayoutDAL */
    public $dal;

    public $availablePlanningTypes;

    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new LayoutDAL();
    }

    /**
     * Loads DatasetsModel
     * @param int|null $profileId Profile of user attempting the load
     * @return void
     * @throws Exception if model is not associated with application 
     */
    public function load($profileId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Datasets");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations["LabelsTitle"];
        $translationErrors = $translations["Errors"];
        $this->translationErrors = $translationErrors;
        $this->xmlOptions = ['location' => 'TM', 'messages' => $translationErrors];

        /** @var \ServiceProviders\PlanningTypesServiceProvider $planningTypesServiceProvider */
        $planningTypesServiceProvider = $this->application->getServiceProvider("ServiceProviders\PlanningTypesServiceProvider");
        $this->availablePlanningTypes = $planningTypesServiceProvider->planningTypes;

        if (!is_null($profileId)) {
            $this->xmlInput = $this->dal->getPlanningDataset($profileId);
        }
    }

    /**
     * Set dataset On billboard
     *
     * @param int|null $profileId
     * @param int $dailyTargetID
     * @return string|null
     */
    public function setDatasetOnBillboard($profileId, $dailyTargetID)
    {
        $error = $this->dal->setDatasetOnBillboard($profileId, $dailyTargetID);
        if (!empty($error)) {
            $this->application->onError($error);
        }
    }
}