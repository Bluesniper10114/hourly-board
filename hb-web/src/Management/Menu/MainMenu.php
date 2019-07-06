<?php
namespace Management\Menu;

use DataAccessLayer\Model\User;

/**
 * Creates the main menu
 *
 * How to use:
 * @source
 * $menu = new \Management\Menu\MainMenu();
 * $role = \Common\Login\LoginStorage::GetRoleId();
 * echo $menu->loadForRole($role);
 *
 * $result = $menu->AccessToRoutePermitted($_SERVER['QUERY_STRING']);
 */
class MainMenu
{
    /**
     * Holds the menu items after loadForRole was called.
     * They can be rendered using getHtml or another renderer
     *
     * @var array
     */
    public $menuItems = [];

    /**
     * Checks if features are permitted for a given role
     *
     * @var \ServiceProviders\FeatureServiceProvider
     */
    public $featureServiceProvider;

    /**
     * Localisation data
     * @var array
     */
    public $translations;
    /**
     * Loads the menu for a given role
     *
     * @param int $role Role of the user viewing the menu
     * @param string $siteUrl The base site url for the menu
     * @param string $pathToJson The path to the JSON fail containing the menu contents
     */
    public function loadForRole($role, $siteUrl, $pathToJson)
    {
        if (file_exists($pathToJson)) {
            $json = file_get_contents($pathToJson);
            $menuItems = json_decode($json);
        } else {
            $menuItems = [];
            return;
        }
        $this->menuItems = [];
        foreach ($menuItems as $menuItem) {
            // Check if the menu has any features permitted by role, otherwise ignore the menu entry
            // It is useless to display the menu entry if none of the minimum features required are present
            if ($this->featureServiceProvider->anyFeaturePermitted($menuItem->minimumFeatures, $role)) {
                $mObj = $this->loadMenuEntry($role, $siteUrl, $menuItem);
                $this->menuItems[] = $mObj;
            }
        }
        $this->applyMenuOpen($this->menuItems);
        $this->growParentMenu();
    }

    /**
     * Loads the menu open or grow
     *
     * @param array $menuItems list of menuItems
     */
    private function applyMenuOpen($menuItems)
    {
        foreach ($menuItems as $menuItem) {
            if (!$menuItem->selected) {
                $selected = false;
                $compareUrl = explode("?", $menuItem->serverUrl . $_SERVER['REQUEST_URI']);
                $compareUrl = $compareUrl[0];
                $selected = $menuItem->baseUrl . $menuItem->link === $compareUrl ? 1 : 0;
                $menuItem->selected = $selected;
            }
            if (count($menuItem->children) > 0) {
                $this->applyMenuOpen($menuItem->children);
            }
        }
    }


    /**
     * Loads the parent menu open or grow by using child menu
     *
     */
    private function growParentMenu()
    {
        foreach ($this->menuItems as $menuItem) {
            if (count($menuItem->children) > 0) {
                $children = $menuItem->children;
                foreach ($children as $menuItem1) {
                    if ($menuItem1->selected) {
                        $menuItem->selected = true;
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Loads the menu entry
     *
     * @param int $role Role of the user viewing the menu
     * @param string $siteUrl The base site url for the menu
     * @param object $menuItem The json object of menuitem
     * @return IMenuEntry object
     */
    private function loadMenuEntry($role, $siteUrl, $menuItem)
    {
        $mObj = new MenuEntry($siteUrl);
        $translationsMenu = $this->translations;
        $title = isset($translationsMenu[$menuItem->title]) ? 
        $translationsMenu[$menuItem->title] : $menuItem->title;
        $mObj->title = $title;
        $mObj->link = $menuItem->link;
        $mObj->icon = isset($menuItem->icon) ? $menuItem->icon : '';
        $mObj->children = [];
        if (isset($menuItem->children)) {
            foreach ($menuItem->children as $cmenuItem) {
                if ($this->featureServiceProvider->anyFeaturePermitted($cmenuItem->minimumFeatures, $role)) {

                    $cmObj = $this->loadMenuEntry($role, $siteUrl, $cmenuItem);
                    $mObj->children[] = $cmObj;
                }
            }
        }
        $mObj->grow = isset($menuItem->grow) ? $menuItem->grow : false;
        return $mObj;
    }
}
