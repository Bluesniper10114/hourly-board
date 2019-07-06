<?php
namespace Common;
use Core\Pagination\IRenderer;

/**
 * Renders the pagination bar in two ways: generatePagination generates a short bar, while
 * generateFullPagination generates a long bar
 */
class FullBarPaginationRenderer implements IRenderer
{
    /** @var int */
    protected $adjacents;
    /** @var string */
    protected $path = "?";
    /** @var int */
    protected $counter;

    /** @var \Core\Pagination\IPagination */
    protected $args;

    /** @var string */
    protected $pagination = "";
    /** @var string */
    protected $lpm1;

    /**
     * @inheritDoc
     */
    public function generatePagination($path = '?', \Core\Pagination\IPagination $args = null)
    {

        if (is_null($args)) {
            return "";
        }
        $this->path = $path;
        $this->args = $args;
        $this->adjacents = 1;

        $this->lpm1 = $this->args->lastPageIndex - 1;

        $this->counter = 0;
        $pagination = "";
        if ($this->args->lastPageIndex > 1) {
            $pagination .= "<ul class='pagination'>";
            $previousPageIndex = $this->args->previousPageIndex;
            $previousPageText = $this->args->previousPageText;
            if ($this->args->pageIndex > 1) {
                $pagination .= "<li><a href='" . $this->path . "page=$previousPageIndex" . "'>$previousPageText</a></li>";
            } else {
                $pagination .= "<li><span class='disabled'>$previousPageText</span></li>";
            }
            $pagination = $this->generateCorePagination($pagination);
            $nextPageIndex = $this->args->nextPageIndex;
            $nextPageText = $this->args->nextPageText;
            if ($this->args->pageIndex < $this->counter - 1) {
                $pagination .= "<li><a href='" . $this->path . "page=$nextPageIndex" . "'>$nextPageText</a></li>";
            } else {
                $pagination .= "<li><span class='disabled'>$nextPageText</span></li>";
                $pagination .= "</ul>";
            }
        }
        return $pagination;
    }

    private function generateShortCore($pagination)
    {
        for ($this->counter = 1; $this->counter <= $this->args->lastPageIndex; $this->counter++) {
            // display current page
            if ($this->counter === $this->args->pageIndex) {
                $pagination .= "<li><span class='current'>$this->counter</span></li>";
            } else {
                $pagination .= "<li><a href='" . $this->path . "page=$this->counter " . "'>$this->counter</a></li>";
            }
        }
        return $pagination;
    }

    private function generateLongCore($pagination)
    {
        $lastPageIndex = $this->args->lastPageIndex;
        $lastPageText = $this->args->lastPageText;
        if ($this->args->pageIndex < 1 + ($this->adjacents * 2)) {
            for ($this->counter = 1; $this->counter < 4 + ($this->adjacents * 2); $this->counter++) {
                if ($this->counter === $this->args->pageIndex) {
                    $pagination .= "<li><span class='current'>$this->counter</span></li>";
                } else {
                    $pagination .= "<li><a href='" . $this->path . "page=$this->counter" . "'>$this->counter</a></li>";
                }
            }
            $pagination .= "<li><a href='#'>...</a></li>";
            $pagination .= "<li><a href='" . $this->path . "page=$this->lpm1" . "'>$this->lpm1</a></li>";
            $pagination .= "<li><a href='" . $this->path . "page=$lastPageIndex" . "'>$lastPageText</a></li>";
        } elseif ($this->args->lastPageIndex - ($this->adjacents * 2) > $this->args->pageIndex && $this->args->pageIndex > ($this->adjacents * 2)) {
            $pagination .= "<li><a href='" . $this->path . "page=1" . "'>1</a></li>";
            $pagination .= "<li><a href='" . $this->path . "page=2" . "'>2</a></li>";
            $pagination .= "...";
            for ($this->counter = $this->args->pageIndex - $this->adjacents; $this->counter <= $this->args->pageIndex + $this->adjacents; $this->counter++) {
                if ($this->counter === $this->args->pageIndex) {
                    $pagination .= "<li><span class='current'>$this->counter</span></li>";
                } else {
                    $pagination .= "<li><a href='" . $this->path . "page=$this->counter" . "'>$this->counter</a></li>";
                }
            }
            $pagination .= "<li><a href='#'>..</a></li>";
            $pagination .= "<li><a href='" . $this->path . "page=$this->lpm1" . "'>$this->lpm1</a></li>";
            $pagination .= "<li><a href='" . $this->path . "page=$lastPageIndex" . "'>$lastPageText</a></li>";
        } else {
            $pagination .= "<li><a href='" . $this->path . "page=1" . "'>1</a></li>";
            $pagination .= "<li><a href='" . $this->path . "page=2" . "'>2</a></li>";
            $pagination .= "<li><a href='#'>..</a></li>";
            for ($this->counter = $this->args->lastPageIndex - (2 + ($this->adjacents * 2)); $this->counter <= $this->args->lastPageIndex; $this->counter++) {
                if ($this->counter === $this->args->pageIndex) {
                    $pagination .= "<li><span class='current'>$this->counter</span></li>";
                } else {
                    $pagination .= "<li><a href='" . $this->path . "page=$this->counter" . "'>$this->counter</a></li>";
                }
            }
        }
        return $pagination;
    }

    private function generateCorePagination($pagination)
    {
        if ($this->args->lastPageIndex < 7 + ($this->adjacents * 2)) {
            $pagination = $this->generateShortCore($pagination);
        } elseif ($this->args->lastPageIndex > 5 + ($this->adjacents * 2)) {
            $pagination = $this->generateLongCore($pagination);
        }
        return $pagination;
    }
}