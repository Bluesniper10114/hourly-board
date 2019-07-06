<?php

namespace Common;

use Core\Pagination\PaginationResponse;

/**
 * Handles error messages
 */
abstract class Model extends \Core\Model
{
    /** @var \HourlyBoardApplication $application Redefining to change type */
    public $application;

    /**
     * Form validation rules used by Former
     *
     * @var string[]
     */
    public $rules;

    /**
     * Error encountered in form validation
     *
     * @var string[]
     */
    public $errors = [];

    /**
     * Error messages used in form validation
     *
     * @var string[]
     */
    public $errorMessages;

    /**
     * All pagination details, including the page index, next page, previous page
     * and the items on the current page
     *
     * @var \Core\Pagination\IPagination
     **/
    public $pagination;

    /**
     * Holds the HTML with pagination controls
     *
     * @var string
     */
    public $paginationView;

    /**
     * Holds any message
     *
     * @var string|null
     */
    public $messageHtml;

    /**
     * Loads the pagination for the model
     *
     * @param \Core\Pagination\PaginationSettings $paginationSettings
     * @param \Core\Pagination\IPaginationDataSource $dataSource
     * @return array
     */
    protected function loadPagination($paginationSettings, $dataSource)
    {
        $itemsList = $dataSource->getItemsOnPage($paginationSettings);
        $paginationResponse = new PaginationResponse;
        $paginationResponse->currentPageTotalItems = count($itemsList);
        $paginationResponse->totalItemsOnServer = $dataSource->getTotalItemsOnServer($paginationSettings);

        $this->pagination->preparePagination($paginationSettings, $paginationResponse);
        return $itemsList;
    }

    /**
     * This service handles localization
     *
     * @return \ServiceProviders\LocalisationServiceProvider
     * @throws \Exception If provider does not exist
     */
    public function getLocalisationProvider()
    {
        /** @var \ServiceProviders\LocalisationServiceProvider|null */
        $localisationServiceProvider = $this->application->getServiceProvider("ServiceProviders\LocalisationServiceProvider");
        if (is_null($localisationServiceProvider)) {
            throw new \Exception("Internal error: Localisation provider is missing");
        }
        return $localisationServiceProvider;
    }

    /**
     * Initialize model properties from Given data
     * @param array $data - The data variable
     * @return void
     */
    public function initByData($data)
    {
        $cls = get_class($this);
        foreach ($data as $key => $value) {
            if (property_exists($cls, $key)) {
                $this->$key = $value;
            }
        }
    }
}