<?php

namespace CoreDB\Kernel;

use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Theme\ResultsViewer;

interface SearchableInterface{
    public const PAGE_LIMIT = 100;

    /**
     * Return page size limit.
     * @return int
     * Page limit
     */
    public function getPaginationLimit(): int;
    /**
     * Return a ResultsViewer object.
     * @return ResultsViewer
     * ResultsViewer
     */
    public function getResultsViewer() : ResultsViewer;
    /**
     * Return table headers.
     * @return array
     *  Table headers.
     */
    public function getResultHeaders(bool $translateLabel = true) : array;
    /**
     * Return table search widgets.
     * @return array
     *  Table search widgets.
     */
    public function getSearchFormFields(bool $translateLabel = true) : array;
    /**
     * Return a query which will filtered by search form.
     * @return SelectQueryPreparerAbstract
     *  Query.
     */
    public function getResultQuery() : SelectQueryPreparerAbstract;
    /**
     * Process a result row.
     */
    public function postProcessRow(&$row) : void;
}