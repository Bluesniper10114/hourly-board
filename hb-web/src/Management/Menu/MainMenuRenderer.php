<?php
namespace Management\Menu;

use DataAccessLayer\Model\User;

/**
 * Renders the main menu
 *
 * How to use:
 * @source
 * $menu = new MainMenu();
 * $role = ...;
 * $menu->loadForRole($role);
 * $renderer = new MainMenuRenderer();
 * $htmel = $renderer->getHtml($menu);
 * echo $html;
 */
class MainMenuRenderer
{
    /**
     * Renders the menu
     *
     * @param MainMenu $menuLoader Loads the menu from a json
     * @return string The menu HTML
     */
    public function getHtml($menuLoader)
    {
        $menu = '<ul class="sidebar-menu">';
        foreach ($menuLoader->menuItems as $menuItem) {
            $menu .= $menuItem->render();
        }
        $menu .= '</ul>';
        return $menu;
    }
}