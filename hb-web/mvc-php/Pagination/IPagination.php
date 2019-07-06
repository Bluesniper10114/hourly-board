<?php
namespace Core\Pagination;

/**
 * IPagination Implements a pagination mechanism applied on a data source
 *
 * It containts all the information necessary to display (in any way) a paginated UI
 * @note: Due to php languagerestrictions properties cannot be part of interfaces,
 * this is why we're missing some properties like: page index, next page index etc.
 * @see SimplePagination as a baseline.
 **/
interface IPagination
{
    /**
     * Prepares the necessary data to populate the pagination view.
     *
     * @param \Core\Pagination\PaginationSettings $paginationSettings Contains both the pagination
     * settings and useful info after the page was loaded
     * @param \Core\Pagination\PaginationResponse $paginationResponse The data collected after the
     * server was reached: total items on the server and current page no of items
     * @return void
     */
    public function preparePagination($paginationSettings, $paginationResponse);
}