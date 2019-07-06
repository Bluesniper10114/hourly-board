<?php
namespace Management\Menu;

/**
 * A single menu entry
 */
class MenuEntry implements IMenuEntry
{
    public $title;
    public $link;
    public $icon;
    public $children;

    /** @var bool This menu item is selected or expanded if it has children */
    public $selected = false;
    public $baseUrl;
    public $fullUrl;

    /**
     * @inheritDoc
     **/
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $protocol = "http://";
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] === 'off') {
                $protocol = "http://";
            } else {
                $protocol = "https://";
            }    
        };
        
        $this->serverUrl =  $protocol . $_SERVER['SERVER_NAME'] . (!empty($_SERVER['SERVER_PORT']
        && !in_array((string)$_SERVER['SERVER_PORT'], ['80', '443'])) ?
        ":" . $_SERVER['SERVER_PORT'] : "");


    }

    /**
     * @inheritDoc
     *
     * Gets the HTML type based on indent
     *
     * @return string HTML menu
     */
    public function render()
    {
        $childMenu = '';
        $addClass = [];
        $iconRight = '';
        $icon = !empty($this->icon) ? '<i class="fa fa-' . $this->icon . '"></i>' : '';

        // this menu item has children
        if (count($this->children) > 0) {
            $childMenu = $this->renderTree();
            $addClass[] = 'treeview';
            // it should be expanded
            if ($this->selected) {
                $addClass[] = 'active  menu-open';
            }

            $iconRight = '<i class="fa fa-angle-left pull-right"></i>';
        }
        $activeClass = $this->isActiveMenuItem();
        if ($activeClass !== '') {
            $addClass[] = $activeClass;
        }
        $addClass = implode(' ', $addClass);
        return "<li class=\"$addClass\"><a href=" . '"' .
        $this->baseUrl . $this->link .
        '"' . "> $icon <span>$this->title</span> $iconRight</a>$childMenu </li>";
    }

    /**
     * Render the submenu
     *
     * @return string HTML menu
     */
    private function renderTree()
    {
        $html = '<ul class="treeview-menu">';
        foreach ($this->children as $menu) {
            $html .= $menu->render();
        }
        $html .= "</ul>";
        return $html;
    }

    /**
     * If the menu item is selected it has an "ative" class, otherwise it has no class
     *
     * @return string active class or empty string
     */
    private function isActiveMenuItem()
    {
        $class = '';
        if ($this->selected) {
            $class = 'active';
        }
        return $class;
    }

}
