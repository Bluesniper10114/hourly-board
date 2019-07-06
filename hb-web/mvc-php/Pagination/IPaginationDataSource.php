<?php
namespace Core\Pagination;

/**
 * Interface for a pagination data source object.
 * It allows the pagination to control what objects are loaded by page and filter (e.g. single or multiple search)
 **/
interface IPaginationDataSource
{
	/**
	 * Override this to get the total number of items from the server!!!;
	 * @param \Core\Pagination\PaginationSettings $paginationSettings
	 * @return int Total number of items on the server.
	 **/
	public function getTotalItemsOnServer($paginationSettings);

	/**
	 * Override this method tot load the items on this page from the Data Layer
	 *
	 * @param \Core\Pagination\PaginationSettings $paginationSettings
	 * @return object[] An array of items for the current page
	 **/
	public function getItemsOnPage($paginationSettings);
}