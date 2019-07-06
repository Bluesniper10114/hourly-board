<?php
namespace Core\Pagination;

/**
* SimpleRenderer renders a pagination element curent page, Prev / Next buttons
*/
class SimpleRenderer implements IRenderer
{

	/**
	 * @var string $SearchFilter
	 * The search filter passed from one page to another via GET (e.g. '?color=red')
	 * The search filter will be appended to the URLs on the UI controls of the pagination
	 **/
	public $searchFilter = "";

	/**
	 * @inheritDoc
	 */
	public function generatePagination($path = '?', IPagination $paginationHandler = null)
	{
		$pagination = "";
		if (is_null($paginationHandler)) {
			return "";
		}

		if ($paginationHandler->lastPageIndex >= 1) {
			$pagination .= "<ul class='pagination pagination-sm no-margin'>";
			if ($paginationHandler->hasPreviousPage) {
				$pagination .= "<li><a href='" . $path . "PageIndex=$paginationHandler->previousPageIndex";
				$pagination .= $this->searchFilter . "'>$paginationHandler->previousPageText</a></li>";

				$pagination .= "<li><a href='" . $path . "PageIndex=$paginationHandler->firstPageIndex";
				$pagination .= $this->searchFilter . "'>$paginationHandler->firstPageText</a></li>";
			}

			$pagination .= "<li><span class='current'>$paginationHandler->currentPageText</span></li>";

			if ($paginationHandler->hasNextPage) {
				$pagination .= "<li><a href='" . $path . "PageIndex=$paginationHandler->lastPageIndex";
				$pagination .= $this->searchFilter . "'>$paginationHandler->lastPageText</a></li>";

				$pagination .= "<li><a href='" . $path . "PageIndex=$paginationHandler->nextPageIndex";
				$pagination .= $this->searchFilter . "'>$paginationHandler->nextPageText</a></li>";
			}

			$pagination .= "</ul>";
		}

		return $pagination;
	}

}