<?php
namespace Core\Pagination;

class PaginationResponse
{
        /** @var int Total number of items existing on the server */
        public $totalItemsOnServer;

        /** @var int number of items loaded on current page */
        public $currentPageTotalItems;

}