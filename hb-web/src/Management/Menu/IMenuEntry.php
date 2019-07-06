<?php
namespace Management\Menu;

/**
 * A single menu entry
 */
interface IMenuEntry
{
    /**
     * Constructs the entry for a role and pointing to the route using a base URL
     *
     * @param string $baseUrl Url to which all routes are relative to
     */
    public function __construct($baseUrl);

    /**
     * Renders the menu entry
     *
     * @return string Menu entry HTML
     */
    public function render();

}
?>