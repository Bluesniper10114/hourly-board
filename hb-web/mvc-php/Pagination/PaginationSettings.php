<?php
namespace Core\Pagination;

class PaginationSettings
{
    /** @var int Current page Index */
    public $pageIndex;

    /** @var string User string search filter */
    public $filter;

    /** @var string Base page URL to use on the pagination buttons */
    public $baseUrl;

    /** @var string Relative URL to be appended to the baseURL on pagination buttons */
    public $relativeUrl;

    /** @var int Maximum number of items to load on one page */
    public $itemsOnPage;

    /**
     * Initializes $pageIndex and $filter from the $_GET global
     * @param array $params The page index and search filter params (usually the $_GET object)
     * @return void
     */
    public function init($params)
    {
        $this->pageIndex = isset($params["page"]) ? $params["page"] : 0;
        $this->filter = isset($params["SearchFilter"]) ? $params["SearchFilter"] : '';
    }

    /**
     * Calculates number of items to skip when loading the page based on
     * pageIndex and itemsOnPage
     * @return int
     */
    public function getSkipItems()
    {
        return $this->pageIndex * $this->itemsOnPage;
    }
}