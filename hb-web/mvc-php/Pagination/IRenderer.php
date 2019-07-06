<?php
namespace Core\Pagination;

/**
 * An interface to transform the IPagination data into various HTML representations
 **/
interface IRenderer
{
    /**
     * Generates a basic pagination HTML using data from the $args parameter; it provides support for filtered pagination (search)
     *
     * @param string $path Base path for all links in the pagination
     * @param IPagination $paginationHandler The calculated indexes and formatted texts for the pagination
     * @return string The pagination HTML
    */
    public function generatePagination($path = '?', IPagination $paginationHandler);
}