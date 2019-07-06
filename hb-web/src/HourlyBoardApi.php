<?php

use Core\Application;
use ServiceProviders\AccountStorageServiceProvider;
use ServiceProviders\AuthenticationServiceProvider;
use ServiceProviders\LocalisationServiceProvider;
use ServiceProviders\FeatureServiceProvider;
use ServiceProviders\PaginationServiceProvider;
use ServiceProviders\PaginationRendererServiceProvider;
use ServiceProviders\VersionServiceProvider;
use ServiceProviders\PlanningTypesServiceProvider;

/**
 * Class HourlyBoardApi supports the API
 */
class HourlyBoardApi extends Application
{
    /**
     * @inheritDoc
     */
    protected function registerServiceProviders()
    {
        $authentication = new AccountStorageServiceProvider();
        $authentication->register($this);

        $account = new AuthenticationServiceProvider();
        $account->register($this);

        $localization = new LocalisationServiceProvider();
        $localization->register($this);

        $features = new FeatureServiceProvider();
        $features->register($this);

        $pagination = new PaginationServiceProvider();
        $pagination->register($this);

        $paginationRenderer = new PaginationRendererServiceProvider();
        $paginationRenderer->register($this);

        $version = new VersionServiceProvider();
        $version->register($this);

        $planning = new PlanningTypesServiceProvider();
        $planning->register($this);
    }

    /**
     * @inheritDoc
     */
    protected function initServiceProviders()
    {
        $localization = $this->getServiceProvider("ServiceProviders\LocalisationServiceProvider");
        
        // this is where we put all translations, relative to the home folder
        $localization->path = dirname(__DIR__) . DIRECTORY_SEPARATOR . "language" . DIRECTORY_SEPARATOR;
        $localization->keyMappings = [
            "Login" => "login",
            "Main" => "main",
            "Buttons" => "main",
            "Profile" => "profile",
            "RoutingPn" => "routingPn",
            "Settings" => "settings",
            "Rights" => "rights",
            "Users" => "users",
            "Breaks" => "breaks",
            "MonitorsList" => "monitorsList",
            "AddMonitor" => "addMonitor",
            "EditMonitor" => "editMonitor",
            "Datasets" => "datasets",
            "Lines" => "lines",
            "Shopfloor" => "shopfloor",
            "HistoricalBillboard" => "historicalBillboard",
            "DailyPlanning" => "planningByDay",
            "PlanningByNight" => "planningByNight",
            "PlanningByPart" => "planningByPartNumber",
            "__Pagination" => "main",
            "LeftMenuEntries" => "menu",
        ];
        $localization->availableLanguages = ["en", "ro"];
        $localization->defaultLanguage = getSetting("DEFAULT_LANGUAGE");

        $authenticationService = $this->getServiceProvider("ServiceProviders\AuthenticationServiceProvider");
        $authenticationService->extractTokenFromHttpHeader();
        $token = $authenticationService->getToken();
        if (!empty($token)) {
            $authenticationService->loadFromToken();
            // store the token
            $authenticationStorage = $this->getServiceProvider("ServiceProviders\AccountStorageServiceProvider");
            $authenticationStorage->setToken($token);
        }

        $planning = $this->getServiceProvider("ServiceProviders\PlanningTypesServiceProvider");
        $pathToJson = dirname(__FILE__) . '/ServiceProviders/planningTypes.json';
        if (file_exists($pathToJson)) {
            $planning->load($pathToJson);
            $planning->loadLanguageTranslations($localization);
        }

        $features = $this->getServiceProvider("ServiceProviders\FeatureServiceProvider");
        $features->excludedFeatures = $planning->getPlanningFeaturesNotConfigured();
    }

    /**
     * Builds the router object and initializes any filters
     *
     * @param string $namespace The default namespace for router objects.
     * All controllers in the route will have this namespace attached.
     * @return \Core\Router
     */
    public function getRouter($namespace = "")
    {
        $this->registerServiceProviders();
        $this->initServiceProviders();
        $router = new Core\Router();
        $router->application = $this;
        $router->baseNameSpace = $namespace;
        return $router;
    }
}
