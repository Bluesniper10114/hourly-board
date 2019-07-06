<?php
namespace Management;

use \Core\View;
/**
 * Manages the login for the app or the management tool
 */
abstract class ManagementController extends \Common\Controller 
{

    /**
     * Renders the view for this MVC
     *
     * @param string $urlToView Path to php view
     * @param array $data Data being rendered in the view
     * @return void
     */
    public function render($urlToView, $data)
    {
        /** @var \ServiceProviders\FooterServiceProvider $footerProvider */
        $footerProvider = $this->application->getServiceProvider("ServiceProviders\FooterServiceProvider");

        /** @var \ServiceProviders\HeaderServiceProvider $headerProvider */
        $headerProvider = $this->application->getServiceProvider("ServiceProviders\HeaderServiceProvider");

        /** @var \ServiceProviders\MainMenuServiceProvider $menuProvider */
        $menuProvider = $this->application->getServiceProvider("ServiceProviders\MainMenuServiceProvider");

        $title = isset($data['title']) ? $data['title'] : "MISSING TITLE";

        // by default, every view has a header and a footer, you can remove them by adding a route param and setting it to false
        $embed = isset($this->routeParams['embed']) ? $this->routeParams['embed'] : 'headerAuth|footerAuth';
        $embedValues = explode('|', $embed);

        // build the Html for the main menu and insert it into the header
        $menu = $menuProvider->getHtml();
        $headerProvider->load($title);
        // build the Html for the main menu and insert it into the header
        $headerProvider->setMenu($menu);
        $headerProvider->render($embedValues);

        View::render($urlToView, $data);
        
        $footerProvider->render($embedValues);
    }

    
}