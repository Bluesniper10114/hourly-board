<?php
namespace ServiceProviders;

use Core\Persistence\PersistentObject;
use Core\ServiceProviders\ServiceProvider;

/**
 * Handles localization throughout the app
 * Localization content is stored in several files and dedicated language folder.
 * A language folder (e.g. "en") contains individual files (e.g. feature.php), mapped to a localization key.
 * Each file contains an array with localized terms
 */
class LocalisationServiceProvider extends ServiceProvider
{
    /**
     * Provides a valid path to the language folder structure
     * /language/en/
     *         deals.php
     *         deals_help.php
     *        ...
     * @var string
     **/
    public $path = "./";

    /**
     * All available languages for translation
     *
     * @var array
     */
    public $availableLanguages = ["en", "ro"];

    /**
     * Currently selected language for all translations
     * A language code like "en", "de" etc.
     * @var string
     **/
    protected $currentLanguage;

    /**
     * The default language if none is selected
     * @var string
     */
    public $defaultLanguage = "en";

    /**
     * @var array Maps each key to its corresponding file name for translation
     * Keys for translations are mapped to their corresponding php file. E.g. "Login" maps to /language/[lan]/login.php
     */
    public $keyMappings = [];

    /**
     * Gets the selected language of the currently logged in user.
     * If none is set, it uses the default language.
     * @return string
     * @throws Exception If service is not registered
     */
    public function getCurrentLanguage()
    {
        if (!isset($this->currentLanguage)) {
            if (!is_null($this->application)) {
                /** @var \ServiceProviders\SessionVariablesProvider | null */
                $session = $this->application->getServiceProvider("ServiceProviders\SessionVariablesProvider");
                if (!is_null($session)) {
                    $storedLanguage = $session->getSessionValue("language");
                } 
                if (!isset($storedLanguage)) {
                    $storedLanguage = $this->defaultLanguage;
                }
                $this->currentLanguage = $storedLanguage;
            }
        }
        return $this->currentLanguage;
    }

    /**
     * Sets the language of the currently logged in user
     *
     * @param string $language Language code to set (e.g. en)
     * @return void
     * @throws Exception if language is not supported
     */
    public function setCurrentLanguage($language)
    {
        if (!$this->languageIsAvailable($language)) {
            throw new \Exception("Trying to set a language which is not supported: $language");
        }

        $this->currentLanguage = $language;
        if (!is_null($this->application)) {
            /** @var \ServiceProviders\SessionVariablesProvider|null */
            $session = $this->application->getServiceProvider("ServiceProviders\SessionVariablesProvider");

            if (!is_null($session)) {
                $session->storeSessionValue("language", $language);
            }
        }
    }

    /**
     * Checks if $language is available.
     * 
     * @param string $language Language code to check (e.g. en)
     * @return bool True if language is between supported languages
     */
    protected function languageIsAvailable($language)
    {
        return in_array($language, $this->availableLanguages, TRUE);
    }

    /**
     * Includes help for a given $key, using a translation $languageCode
     *
     * @param string $key Identifies the translation section
     * @param string $default Default help text if none found. Default is empty string.
     * @return string The help HTML content
     */
    public function getHelpForKey($key, $default = "")
    {
        $fileName = $this->keyMappings[$key];
        $currentLanguage = $this->getCurrentLanguage();
        $path = $this->path . $currentLanguage . "/" . $fileName . "_help.php";
        if (file_exists($path)) {
            return file_get_contents($path);
        } else {
            return $default;
        }
    }

    /**
     * @brief Loads a subset of the translations array from the corresponding language file, given a subkey
     * @param string $key Identifies the translation section
     * @return array[string,string] of strings e.g. key => translation
     */
    public function getTranslationsForKey($key)
    {
        $fileName = $this->keyMappings[$key];
        $currentLanguage = $this->getCurrentLanguage();
        $path = $this->path . $currentLanguage . DIRECTORY_SEPARATOR . $fileName . ".php";

        $language = self::getTranslationArrayFromFile($path);
        return isset($language[$key]) ? $language[$key] : [];
    }

    /**
     * Gets an array with all the translations in a translation file
     *
     * @param string $path Path to translation file
     * @return array Translation objects by key. Each key leads to a translation array.
     */
    private function getTranslationArrayFromFile($path)
    {
        if (file_exists($path)) {
            return include($path);
        }
        return [];
    }

}
