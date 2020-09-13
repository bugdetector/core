<?php

namespace CoreDB\Kernel;

use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;

interface SearchableInterface{


    /**
     * Return table headers.
     * @return array
     *  Table headers.
     */
    public function getTableHeaders(bool $translateLabel = true) : array;
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
    public function getTableQuery() : SelectQueryPreparerAbstract;

    /**
     * Process a result row.
     */
    public function postProcessRow(&$row) : void;
}