<?php

use Core\Application;
use ServiceProviders\AccountStorageServiceProvider;
use ServiceProviders\AuthenticationServiceProvider;
use ServiceProviders\SessionVariablesProvider;
use ServiceProviders\InfoMessageServiceProvider;
use ServiceProviders\LocalisationServiceProvider;
use ServiceProviders\HeaderServiceProvider;
use ServiceProviders\FooterServiceProvider;
use ServiceProviders\MainMenuServiceProvider;
use ServiceProviders\FeatureServiceProvider;
use ServiceProviders\PaginationServiceProvider;
use ServiceProviders\PaginationRendererServiceProvider;
use ServiceProviders\VersionServiceProvider;
use ServiceProviders\PlanningTypesServiceProvider;

/**
 * Class HourlyBoardApplication supports the management tool
 */
class HourlyBoardApplication extends Application
{
    /**
     * @inheritDoc
     */
    protected function registerServiceProviders()
    {
        $authentication = new AuthenticationServiceProvider();
        $authentication->register($this);

        $accountStorage = new AccountStorageServiceProvider();
        $accountStorage->register($this);

        $session = new SessionVariablesProvider();
        $session->register($this);

        $localization = new LocalisationServiceProvider();
        $localization->register($this);

        $infoMessaging = new InfoMessageServiceProvider();
        $infoMessaging->register($this);

        $header = new HeaderServiceProvider();
        $header->register($this);

        $footer = new FooterServiceProvider();
        $footer->register($this);

        $mainMenu = new MainMenuServiceProvider();
        $mainMenu->register($this);

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

        $accountStorage = $this->getServiceProvider("ServiceProviders\AccountStorageServiceProvider");
        // gets information about current user from the cookie session (if any)
        $accountStorage->loadCurrentUser();

        $token = $accountStorage->getToken();
        if (!empty($token)) {
            // the authentication service could read here the token from the HTTP header
            // it uses the token from the session information
            $authentication = $this->getServiceProvider("ServiceProviders\AuthenticationServiceProvider");
            $authentication->setToken($token);
            $authentication->loadFromToken();
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

    /**
     * This service provides message queing (messages are stored until dismissed)
     * @return \ServiceProviders\InfoMessageServiceProvider|null
     */
    public function getMessagingProvider()
    {
        /** @var \ServiceProviders\InfoMessageServiceProvider|null */
        $messagingProvider = $this->getServiceProvider("ServiceProviders\InfoMessageServiceProvider");
        return $messagingProvider;
    }

    /**
     * Gets the last message stored
     * 
     * @return string|null The info / warning or error message
     */
    public function displayMessage()
    {
        $messagingProvider = $this->getMessagingProvider();
        if (!is_null($messagingProvider) && $messagingProvider->hasMessage()) {
            $this->messageHtml = $messagingProvider->display();
        }
        return null;
    }

    /**
     * Adds an explicit error with full description and informs the messaging provider
     *
     * @param string $errorText The user firendly error text
     * @return void
     **/
    public function onError($errorText)
    {
        $messagingProvider = $this->getMessagingProvider();
        if (!is_null($messagingProvider)) {
            $messagingProvider->onError($errorText);
        }
    }

    /**
     * Clears the last action message and informs the messaging provider
     * @return void
     **/
    public function clearMessages()
    {
        $messagingProvider = $this->getMessagingProvider();
        if (!is_null($messagingProvider)) {
            $messagingProvider->clear();
        }
    }

    /**
     * Adds a warning message and informs the messaging provider
     *
     * @param string $warningText - The user friendly text
     * @return void
     **/
    public function onWarning($warningText)
    {
        $messagingProvider = $this->getMessagingProvider();
        if (!is_null($messagingProvider)) {
            $messagingProvider->onWarning($warningText);
        }
    }

    /**
     * Adds a success message and informs the messaging provider
     *
     * @param string $successText - The user friendly text
     * @return void
     **/
    public function onSuccess($successText)
    {
        $messagingProvider = $this->getMessagingProvider();
        if (!is_null($messagingProvider)) {
            $messagingProvider->onSuccess($successText);
        }
    }

    /**
     * Adds a general information message and informs the messaging provider
     *
     * @param string $infoText - The user friendly text
     * @return void
     **/
    public function onInfo($infoText)
    {
        $messagingProvider = $this->getMessagingProvider();
        if (!is_null($messagingProvider)) {
            $messagingProvider->onInfo($infoText);
        }
    }
}
