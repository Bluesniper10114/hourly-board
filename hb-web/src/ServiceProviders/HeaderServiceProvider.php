<?php
namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;
use Management\Layout\HeaderModel;
use \Core\View;
use PHPUnit\Runner\Exception;

/**
 * Renders the header globally
 */
class HeaderServiceProvider extends ServiceProvider
{
    /**
     * Model to load header data
     *
     * @var HeaderModel|null
     */
    public $model = null;

    protected $headers = [
        'headerPublic' => 'Management/Layout/headerPublic.php',
        'headerAuth' => 'Management/Layout/headerAuth.php',
        'headerShopfloor' => 'Management/Layout/headerShopfloor.php',
    ];

    /**
     * Lazy loading the model
     * 
     * @return HeaderModel The model
     */
    protected function getModel()
    {
        if (is_null($this->model)) {
            $this->model = new HeaderModel();
        }
        return $this->model;
    }

    /**
     * @param string $title Page title
     */
    public function load($title)
    {
        if (is_null($this->application)) {
            return;
        }
        /** @var \ServiceProviders\AccountStorageServiceProvider|null */
        $authenticationProvider = $this->application->getServiceProvider("ServiceProviders\AccountStorageServiceProvider");
        /** @var \ServiceProviders\LocalisationServiceProvider|null */
        $localisationProvider = $this->application->getServiceProvider("ServiceProviders\LocalisationServiceProvider");
        if (is_null($authenticationProvider) || is_null($localisationProvider)) {
            return;
        }

        $model = $this->getModel();

        $model->title = $title;
        $model->profileId = $authenticationProvider->getProfileId();
        $translations = $localisationProvider->getTranslationsForKey("Main");
        $model->translations = $translations;

        $language = $localisationProvider->getCurrentLanguage();
        $model->language = $language;
        $model->load();
    }
    /**
     * Renders the header
     *
     * @param array $headers String keys for headers to be rendered. Only the first one will be rendered.
     * @return void
     */
    public function render($headers)
    {
        $model = $this->getModel();
        $data = $model->serialize();
        $headerKeys = array_keys($this->headers);
        
        $renderedKeys = array_values(array_intersect($headerKeys, $headers));
        if (!empty($renderedKeys)) {
            $header = $renderedKeys[0];
            View::render($this->headers[$header], $data);
        }
    }

    /**
     * Sets the menu html
     *
     * @param string $menu Rendered menu
     * @return void
     */
    public function setMenu($menu)
    {
        $model = $this->getModel();
        $model->menu = $menu;
    }
}