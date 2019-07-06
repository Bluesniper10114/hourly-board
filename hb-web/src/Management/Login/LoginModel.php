<?php
namespace Management\Login;
/**
*     Model for the management login
*/
class LoginModel extends \Common\Login\LoginModel
{
    public $labelsPlaceholderTranslations;
    public $labelsTitleTranslations;
    public $labelsHelpTranslations;

    /** @var int|null User role */
    public $role;

    /** @var string web component copyright information */
    public $softwareVersionLine;

    /** @var string database version information */
    public $datababaseVersion;

    /** @var string Copyright information */
    public $copyrightLine;

    /**
     * Loads the model localized information
     *
     * @return void
     */
    public function load()
    {
        $translationsMain = $this->getLocalisationProvider()->getTranslationsForKey("Main");
        $translationsLogin = $this->getLocalisationProvider()->getTranslationsForKey("Login");
        $this->header = $translationsLogin["Header"];
        $this->title = $translationsLogin["Title"];
        $this->labelsTitleTranslations = $translationsLogin['LabelsTitle'];
        $this->labelsPlaceholderTranslations = $translationsLogin['LabelsPlaceholder'];
        $this->labelsHelpTranslations = $translationsLogin['LabelsHelp'];
        $this->loadVersion();
    }

    /**
     * Loads software and database version
     */
    protected function loadVersion()
    {
        /** @var \ServiceProviders\VersionServiceProvider */
        $versionService = $this->application->getServiceProvider("ServiceProviders\VersionServiceProvider");

        $this->softwareVersionLine = $versionService->getSoftwareVersionLine();
        $this->copyrightLine = $versionService->getCopyrightLine();
        $this->datababaseVersion = $versionService->getDatabaseVersion();
    }

    /**
     * @inheritDoc
     */
    public function login()
    {
        if (empty($this->username) || empty($this->password)) {
            $this->loginResult = -1;
            return false;
        } else {
            $md5Password = md5($this->password);
            $resultArray = $this->dal->login($this->username, $md5Password);

            $result = isset($resultArray['result']) ? $resultArray['result'] : -1; // general error at login
            if ($result >= 0) {
                $this->setToken($resultArray["token"]);
                $this->setProfileId($resultArray["profileId"]);
                $this->setRole($resultArray["role"]);
                return true;
            }
            $translations = $this->getLocalisationProvider()->getTranslationsForKey("Login");
            $this->message = sprintf($translations["Errors"]["InvalidCredentials"], $result);
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function logout()
    {
        return parent::logout();
    }

    /**
     * @inheritDoc
     */
    public function isTokenValidForProfile()
    {
        return parent::isTokenValidForProfile();
    }

    /**
     * Gets the role
     * @return int|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Sets the role
     *
     * @param int $role
     * @return void
     */
    public function setRole($role)
    {
        $this->role = $role;
    }
}