<?php

namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;
use Common\FullBarPaginationRenderer;
use Common\ShortBarPaginationRenderer;

/**
 * PaginationServiceProvider calculates pagination details for a UI pagination control to display
 *
 * @note: all indexes start at 0; to get the page number, add +1
 **/
class PaginationRendererServiceProvider extends ServiceProvider
{
    /**
     * Render the pagination
     *
     * @param \Core\Pagination\IPagination $pagination
     * @param \Core\Pagination\PaginationSettings $paginationSettings details about this particular page
     * @return string
     */
    public function render($pagination, $paginationSettings)
    {
        $renderer = new FullBarPaginationRenderer;
        $path = $paginationSettings->baseUrl . $paginationSettings->relativeUrl . "?";
        return $renderer->generatePagination($path, $pagination);
    }
}