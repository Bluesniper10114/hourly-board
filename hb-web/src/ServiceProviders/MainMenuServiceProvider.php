<?php
namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;
use Management\Menu\MainMenu;
use \Core\View;
use Management\Menu\MainMenuRenderer;

/**
 * Loads and renders the main menu for a user role
 */
class MainMenuServiceProvider extends ServiceProvider
{
    /**
     * Renders the main menu for the current role logged in
     *
     * @return string
     */
    public function getHtml()
    {
        if (is_null($this->application)) {
            return "";
        }

        /** @var \ServiceProviders\AccountStorageServiceProvider|null */
        $authenticationProvider = $this->application->getServiceProvider("ServiceProviders\AccountStorageServiceProvider");
        if (is_null($authenticationProvider)) {
            return "";
        }

        $role =  $authenticationProvider->getRole();
        if (is_null($role)) {
            return "";
        }
        /** @var \ServiceProviders\FeatureServiceProvider|null */
        $featureServiceProvider = $this->application->getServiceProvider("ServiceProviders\FeatureServiceProvider");
        /** @var \ServiceProviders\LocalisationServiceProvider|null */
        $localization = $this->application->getServiceProvider("ServiceProviders\LocalisationServiceProvider");
        if (is_null($featureServiceProvider) || is_null($localization)) {
            return "";
        }

        $siteUrl = SITE_URL . 'management/';
        $pathToJson = dirname(__FILE__) . '/menu.json';
        
        $translations = $localization->getTranslationsForKey("LeftMenuEntries");
        $menu = new MainMenu();
        $menu->featureServiceProvider = $featureServiceProvider;
        $menu->translations = $translations;
        $menu->loadForRole($role, $siteUrl, $pathToJson);

        $menuRenderer = new MainMenuRenderer();
        return $menuRenderer->getHtml($menu);
    }
}