<?php
namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;

/**
 * Loads the available planning types and their associated content
 */
class PlanningTypesServiceProvider extends ServiceProvider
{
    /** @var mixed[] Configured planning types */
    public $planningTypes = [];

    /** @var mixed[] List of all supported planning types. Not all might be configured */
    public $supportedPlanningTypes = [];

    /** @var bool Indicates whether the planning types have been loaded at least once */
    private $loaded = false;

    /**
     * Loads the planning types from a file, checks them against the allowed ones and loads the translation for them
     * 
     * @param string $pathToJson Planning types are stored here
     * @return void
     */
    public function load($pathToJson) 
    {
        $jsonSource = file_get_contents($pathToJson);
        $this->supportedPlanningTypes = json_decode($jsonSource);

        $this->loadOnlyAllowedPlanningTypes($this->supportedPlanningTypes);
        $this->loaded = true;
    }

    /**
     * Gets a list of feature keys for planning types not configured, yet available
     * 
     * @return string[]
     * @throws Exception if service provider did not load
     */
    public function getPlanningFeaturesNotConfigured()
    {
        if (!$this->loaded) {
            throw new \Exception("Internal error. This service provider needs to load data from a file first. 
                                Call the <load> method before calling this method");
        }

        $configuredPlanningTypes = getSetting("configuredPlanningTypes");
        $restrictedFeatures = [];
        foreach ($this->supportedPlanningTypes as $type) {
            if (!in_array($type->name, $configuredPlanningTypes, TRUE)) {
                $restrictedFeatures[] = $type->feature;
            }
        }
        return $restrictedFeatures;        
    }

    /**
     * Loads the language translations for all available planning types.
     * E.g. It loads the localized titles for all planning types 
     *
     * @param \ServiceProviders\LocalisationServiceProvider $localisationsProvider
     * @return void
     */
    public function loadLanguageTranslations($localisationsProvider)
    {
        foreach ($this->planningTypes as $type) {
            $translations = $localisationsProvider->getTranslationsForKey($type->translationKey);
            $type->title = $translations["Title"];
        }
    }

    /**
     * Loads only planning types which have been configured in the "config" settings file 
     * 
     * @param object[] $availablePlanningTypes Planning type codes available in the settings file
     * @return void
     */
    private function loadOnlyAllowedPlanningTypes($availablePlanningTypes)
    {
        $this->planningTypes = [];
        $configuredPlanningTypes = getSetting("configuredPlanningTypes");

        foreach ($availablePlanningTypes as $type) {
            if (in_array($type->name, $configuredPlanningTypes)) {
                $this->planningTypes[] = $type;
            }
        }
    }
}
