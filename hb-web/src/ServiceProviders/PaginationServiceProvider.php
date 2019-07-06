<?php

namespace ServiceProviders;

use Core\Pagination\IPagination;
use Core\ServiceProviders\ServiceProvider;

/**
 * PaginationServiceProvider calculates pagination details for a UI pagination control to display
 *
 * @note: all indexes start at 0; to get the page number, add +1
 **/
class PaginationServiceProvider extends ServiceProvider implements IPagination
{
    /** @var int $PageIndex Current page Index (read-only) */
    public $pageIndex;

    /** @var int $ItemsToSkip Number of items to skip when loading current page from the database */
    public $itemsToSkip;

    /** @var int $ItemsOnPage Maximum number of items on a single Page [read-only] */
    public $itemsOnPage;

    /** @var int $FirstItemOnPage Index of first item shown on the page */
    public $firstItemOnPage;

    /** @var int $LastItemOnPage Index of last item shown on page */
    public $lastItemOnPage;

    /**
     * @var string $ShowingItemsFromToText
     * User friendly text detailing items shown on page:
     * e.g.Showing items x to y
     **/
    public $showingItemsFromToText;

    /** @var int $TotalItemsOnServer Counts total number of items on server */
    public $totalItemsOnServer;

    /** @var string $CurrentPageText Name of the current page: e.g. Page X of Y */
    public $currentPageText;

    /** @var int $CurrentPageTotalItems How many items are currently on the selected page? */
    public $currentPageTotalItems;

    /** @var int $TotalPages
     * Total number of pages considering:
     *         total items available and
     *         items on page setting
     **/
    public $totalPages;

    /** @var string $TotalPagesText Total number of pages user friendly text */
    public $totalPagesText;

    /** @var bool $HasNextPage True if another page is available to the right*/
    public $hasNextPage;

    /** @var int $NextPageIndex Index of the next page to the right (null if none exists) */
    public $nextPageIndex;

    /** @var string $NextPageText User friendly next page text to the right (just "Next")*/
    public $nextPageText;

    /** @var bool $HasPreviousPage True if another page is available to the left*/
    public $hasPreviousPage;

    /** @var int $PreviousPageIndex Index of the previous page to the left (null if none exists) */
    public $previousPageIndex;

    /** @var string $PreviousPageText User friendly previous page text to the left (just "Previous")*/
    public $previousPageText;

    /** @var int $LastPageIndex Index of the last page to the right (minimum 0)*/
    public $lastPageIndex;

    /** @var string $LastPageText User friendly text for the button pointing to the last page*/
    public $lastPageText;

    /** @var int $FirstPageIndex Index of the first page to the left (0)*/
    public $firstPageIndex;

    /** @var string $LastPageText User friendly text for the button pointing to the last page*/
    public $firstPageText;

    /** @var string $PageFormattingSetting*/
    public $pageFormattingSetting = "Page %u/%u";

    /**
     * @var string[] $language An array of language mappings for the various user texts
     **/
    public $language;

    const DEFAULT_LANGUAGE = [
        "Next" => "Next", //Text on button to next page
        "Previous" => "Previous", //Text on button to previous page
        "Page_empty" => "-", //Text formatting for the current page when there are no items
        "Page" => "Page %u/%u", //Text formatting for the current page: default "Page %u/%u"
        "Showing_empty" => "-", //Text showing items x of y when there are no items to show
        "Showing" => "Items %u to %u (total %u)", //Text showing items x of y (total z)
        "FirstPage" => "%u", //Text for the first page button
        "LastPage" => "%u", //Text for the last page button
    ];

    /**
     * Constructs the pagination using a default language
     **/
    public function __construct()
    {
        $this->language = self::DEFAULT_LANGUAGE;
    }

    /**
     * @inheritDoc
     **/
    public function preparePagination($paginationSettings, $paginationResponse)
    {
        $this->itemsOnPage = $paginationSettings->itemsOnPage;
        $this->pageIndex = $paginationSettings->pageIndex;
        $this->itemsToSkip = $paginationSettings->getSkipItems();

        $this->totalItemsOnServer = $paginationResponse->totalItemsOnServer;
        $this->currentPageText = $this->getCurrentPageFormatted();
        $this->currentPageTotalItems = $paginationResponse->currentPageTotalItems;

        $this->firstItemOnPage = $this->itemsToSkip + 1;
        $this->lastItemOnPage = $this->firstItemOnPage + $this->currentPageTotalItems - 1;
        if ($this->currentPageTotalItems === 0) {
            $this->showingItemsFromToText = $this->language["Showing_empty"];
        } else {
            $this->showingItemsFromToText = sprintf($this->language["Showing"], $this->firstItemOnPage, $this->lastItemOnPage, $this->totalItemsOnServer);
        }

        $this->totalPages = $this->getTotalPages();
        $this->totalPagesText = "$this->totalPages";

        $this->hasNextPage = $this->hasNextPage();
        $this->nextPageIndex = $this->getNextPage();
        $this->nextPageText = $this->language["Next"];

        $this->hasPreviousPage = $this->hasPreviousPage();
        $this->previousPageIndex = $this->getPreviousPage();
        $this->previousPageText = $this->language["Previous"];

        $this->firstPageIndex = $this->getFirstPage();
        $this->firstPageText = sprintf($this->language["FirstPage"], $this->firstPageIndex + 1);

        $this->lastPageIndex = $this->getLastPage();
        $this->lastPageText = sprintf($this->language["LastPage"], $this->lastPageIndex + 1);
    }

    /**
     * Calculates items to skip in the database to get to the first item on the current page
     *
     * @param int $pageIndex Index of the page to be loaded
     * @param int $itemsOnPage Items to display on a page
     * @return int Number of items to skip
     **/
    protected function getItemsToSkip($pageIndex, $itemsOnPage)
    {
        return $pageIndex * $itemsOnPage;
    }

    /**
     * Formatted string which points the current page and the total number of pages with results
     *
     * @return string Gets the current page name
     **/
    protected function getCurrentPageFormatted()
    {
        $currentPageIndex = $this->pageIndex + 1;
        $totalNumberOfPages = $this->getTotalPages();
        if ($totalNumberOfPages === 0) {
            return $this->language["Page_empty"];
        }
        return sprintf($this->language["Page"], $currentPageIndex, $totalNumberOfPages);
    }

    /**
     * Checks if there are more pages to load. If totalPages is not set, it assumes that a full page means there is more data to load.
     *
     * @return int Number of pages in total
     **/
    protected function getTotalPages()
    {
        if ($this->itemsOnPage === 0) {
            return 0;
        }
        return ceil($this->totalItemsOnServer / $this->itemsOnPage);
    }

    /**
     * Determines if there is a next page
     *
     * @return bool True if a next page exists
     **/
    protected function hasNextPage()
    {
        if (!isset($this->pageIndex)) {
            return false;
        }
        if (!isset($this->totalPages)) {
            return false;
        }
        return $this->pageIndex < $this->totalPages - 1;
    }

    /**
     * Determines if there is a previous page
     *
     * @return bool True if a previous page exists
     **/
    protected function hasPreviousPage()
    {
        if (!isset($this->pageIndex)) {
            return false;
        }
        return $this->pageIndex > 0;
    }

    /**
     * Gets the next page index
     *
     * @return int Next page index
     **/
    protected function getNextPage()
    {
        if ($this->hasNextPage()) {
            return $this->pageIndex + 1;
        }
        return $this->pageIndex;
    }

    /**
     * Gets the previous page index
     *
     * @return int Previous page index
     **/
    protected function getPreviousPage()
    {
        if ($this->hasPreviousPage()) {
            return $this->pageIndex - 1;
        }
        return $this->pageIndex;
    }

    /**
     * Gets the last page index
     *
     * @return int Returns the index
     **/
    protected function getLastPage()
    {
        $totalPages = $this->getTotalPages();
        return $totalPages > 0 ? $totalPages - 1 : 0;
    }

    /**
     * Gets the first page index
     *
     * @return int Index = 0(not page number)
     **/
    protected function getFirstPage()
    {
        return 0;
    }
}
