<?php
namespace Common;

use Core\Pagination\IRenderer;


/**
 * Renders the pagination bar in two ways: generatePagination generates a short bar, while
 * generateFullPagination generates a long bar
 */
class ShortBarPaginationRenderer implements IRenderer
{
    /**
     * @inheritDoc
     */
    public function generatePagination($path = '?', \Core\Pagination\IPagination $args = null)
    {
        if (is_null($args)) {
            return "";
        }

        // Prev First  Current  Last Next
        $pagination = "";

        if ($args->lastPageIndex > 1) {
            $pagination .= "<ul class='pagination'>";
            if ($args->hasPreviousPage) {
                $pagination .= "<li><a href='" . $path . "page=$args->previousPageIndex" . "'>$args->previousPageText</a></li>";
                $pagination .= "<li><a href='" . $path . "page=$args->firstPageIndex" . "'>$args->firstPageText</a></li>";
            }

            $pagination .= "<li><span class='current'>$args->currentPageText</span></li>";

            if ($args->hasNextPage) {
                $pagination .= "<li><a href='" . $path . "page=$args->lastPageIndex" . "'>$args->lastPageText</a></li>";
                $pagination .= "<li><a href='" . $path . "page=$args->nextPageIndex" . "'>$args->nextPageText</a></li>";
            }

            $pagination .= "</ul>";
        }

        return $pagination;
    }
}