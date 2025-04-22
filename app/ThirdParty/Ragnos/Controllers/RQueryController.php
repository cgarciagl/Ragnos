<?php

/**
 * YQueryController class
 *
 * This class extends rdatasetcontroller and is responsible for handling 
 * query-related operations. It provides methods to set a base query and 
 * retrieve data for an AJAX grid.
 *
 * @package App\ThirdParty\Ragnos\Controllers
 */

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class RQueryController extends RDatasetController
{
    /**
     * @var string $baseQuery The base SQL query to be used for data retrieval.
     */
    protected $baseQuery;

    /**
     * Sets the base query and configures the controller.
     *
     * @param string $query The SQL query to be set as the base query.
     * @return self
     */
    public function setQuery($query)
    {
        $this->baseQuery = $query;

        $this->setTableName('Yquery');
        $this->setCanInsert(false);
        $this->setCanUpdate(false);
        $this->setCanDelete(false);
        return $this;
    }

    /**
     * Retrieves data for an AJAX grid based on the base query.
     *
     * This method checks if the request is an AJAX request, applies filters,
     * retrieves data using the base query, and returns the data as a JSON response.
     *
     * @return void
     */
    function getAjaxGridData()
    {
        checkAjaxRequest($this->request);
        $this->applyFilters();
        $ajaxTableResponse = $this->modelo->getTableAjaxBySQL($this->baseQuery);
        returnAsJSON($ajaxTableResponse);
    }
}