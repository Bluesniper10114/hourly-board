<?php
namespace Core\Pagination;

/**
 * FullRenderer renders a pagination element with multiple pages, Prev / Next buttons
 **/
class FullRenderer implements IRenderer
{

    /**
     * @var string $searchFilter
     * The search filter passed from one page to another via GET (e.g. '?color=red')
     * The search filter will be appended to the URLs on the UI controls of the pagination
     **/
    public $searchFilter = "";

    /**
     * @inheritDoc
     **/
    public function generatePagination($path = "?", IPagination $paginationHandler = null)
    {
        if (is_null($paginationHandler)) {
            return "";
        }
        $prev = $paginationHandler->PreviousPageIndex;
        $next = $paginationHandler->NextPageIndex;
        $lastPage = $paginationHandler->LastPageIndex;
        $pageIndex = $paginationHandler->PageIndex;

        $adjacents = "1";
        $counter = 0;
        $lpm1 = $lastPage - 1;
        $pagination = "";
        if ($lastPage > 1) {
            $pagination .= "<ul class='pagination pagination-sm no-margin'>";
            if ($pageIndex > 1) {
                $pagination .= "<li><a href='" . $path . "PageIndex=$prev" . "'>$paginationHandler->PreviousPageText</a></li>";
            } else {
                $pagination .= "<li><span class='disabled'>$paginationHandler->PreviousPageText</span></li>";
            }
            if ($lastPage < 7 + ($adjacents * 2)) {
                for ($counter = 1; $counter <= $lastPage; $counter++) {
                    if ($counter === $pageIndex) {
                        $pagination .= "<li><span class='current'>$counter</span></li>";
                    } else {
                        $pagination .= "<li><a href='" . $path . "PageIndex=$counter" . "'>$counter</a></li>";
                    }
                }
            } elseif ($lastPage > 5 + ($adjacents * 2)) {
                if ($pageIndex < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        if ($counter === $pageIndex) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='" . $path . "PageIndex=$counter" . "'>$counter</a></li>";
                        }
                    }
                    $pagination .= "<li><a href='#'>...</a></li>";
                    $pagination .= "<li><a href='" . $path . "PageIndex=$lpm1" . "'>$lpm1</a></li>";
                    $pagination .= "<li><a href='" . $path . "PageIndex=$lastPage" . "'>$lastPage</a></li>";
                } elseif ($lastPage - ($adjacents * 2) > $pageIndex && $pageIndex > ($adjacents * 2)) {
                    $pagination .= "<li><a href='" . $path . "PageIndex=1'>1</a></li>";
                    $pagination .= "<li><a href='" . $path . "PageIndex=2'>2</a></li>";
                    $pagination .= "...";
                    for ($counter = $pageIndex - $adjacents; $counter <= $pageIndex + $adjacents; $counter++) {
                        if ($counter === $pageIndex) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='" . $path . "PageIndex=$counter" . "'>$counter</a></li>";
                        }
                    }
                    $pagination .= "<li><a href='#'>..</a></li>";
                    $pagination .= "<li><a href='" . $path . "PageIndex=$lpm1" . "'>$lpm1</a></li>";
                    $pagination .= "<li><a href='" . $path . "PageIndex=$lastPage" . "'>$lastPage</a></li>";
                } else {
                    $pagination .= "<li><a href='" . $path . "PageIndex=1'>1</a></li>";
                    $pagination .= "<li><a href='" . $path . "PageIndex=2'>2</a></li>";
                    $pagination .= "<li><a href='#'>..</a></li>";
                    for ($counter = $lastPage - (2 + ($adjacents * 2)); $counter <= $lastPage; $counter++) {
                        if ($counter === $pageIndex) {
                            $pagination .= "<li><span class='current'>$counter</span></li>";
                        } else {
                            $pagination .= "<li><a href='" . $path . "PageIndex=$counter'>$counter</a></li>";
                        }
                    }
                }
            }
            if ($pageIndex < $counter - 1) {
                $pagination .= "<li><a href='" . $path . "PageIndex=$next'>$paginationHandler->NextPageText</a></li>";
            } else {
                $pagination .= "<li><span class='disabled'>$paginationHandler->NextPageText</span></li>";
                $pagination .= "</ul>";
            }
        }
        return $pagination;
    }

}