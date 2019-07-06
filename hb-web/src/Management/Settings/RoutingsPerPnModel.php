<?php
namespace Management\Settings;
use DAL\SettingsDAL;

/**
 * Model for Settings management
 */
class RoutingsPerPnModel extends \Common\Model
{
    /** @var SettingsDAL */
    public $dal;

    /** @var array List of filters to be sent to the database */
    public $filterList;

    /** @var array */
    public $routingsList;
    public $orderByField;
    public $orderDirection = 'asc';

    public $translationTitles;
    public $translationPlaceholders;

    public $sortClasses;
    public $sortLinks;

    public $baseUrl = "/management/settings/routings-per-pn";
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new SettingsDAL();
        $this->sortClasses = ['ID' => '', 'PartNumber' => '', 'Description' => '', 'Routing' => ''];
        $this->sortLinks = ['ID' => '', 'PartNumber' => '', 'Description' => '', 'Routing' => ''];
    }

    /**
     * Loads SettingsModel
     * @return void
     */
    public function load()
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("RoutingPn");
        $this->translationTitles = $translations["LabelsTitle"];
        $this->translationPlaceholders = $translations["LabelsPlaceholder"];
        $this->title = $translations["Title"];
        $this->routingsList =  $this->dal->getRoutingsList($this->filterList, $this->orderByField, $this->orderDirection);
        if (!empty($this->orderByField)){
            $this->sortClasses[$this->orderByField] = $this->orderDirection;
        }

        foreach ($this->sortLinks as $key => $val){
            $query = ['orderByField' => $key];
            if ($this->orderByField === $key && !empty($this->orderDirection)){
                $query['orderDirection'] = $this->orderDirection === 'desc' ? 'asc' : 'desc';
            }
            $this->sortLinks[$key] = urlGenerate($this->baseUrl, $query);

        }

    }

}