<?php

namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;
use Management\Layout\FooterModel;
use \Core\View;

/**
 * Renders the footer globally
 */
class FooterServiceProvider extends ServiceProvider
{

    protected $footers = [
        'footerAuth' => 'Management/Layout/footerAuth.php',
        'footerPublic' => 'Management/Layout/footerPublic.php',
        'footerShopfloor' => 'Management/Layout/footerShopfloor.php',
    ];

    /**
     * Renders the footer
     *
     * @param array $footers List of footers to be rendered. Only the first one will be rendered
     * @return void
     */
    public function render($footers)
    {
        if (is_null($this->application)) {
            return;
        }
        /** @var VersionServiceProvider */
        $versionService = $this->application->getServiceProvider("ServiceProviders\VersionServiceProvider");

        $data = [
            "softwareVersionLine" => $versionService->getSoftwareVersionLine(),
            "copyrightLine" => $versionService->getCopyrightLine(),
            "datababaseVersion" => $versionService->getDatabaseVersion()
        ];
        $footerKeys = array_keys($this->footers);
        $renderedKeys = array_values(array_intersect($footerKeys, $footers));
        if (!empty($renderedKeys)) {
            $footer = $renderedKeys[0];
            View::render($this->footers[$footer], $data);

        }
    }
}