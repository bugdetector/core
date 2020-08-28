<?php

namespace CoreDB\Kernel;

interface ControllerInterface
{

    /**
     * Check is page accessible. If returns false not found page will shown
     *
     * @return bool
     *  is_accessible
     */
    public function checkAccess(): bool;

    /**
     * Process page, for example use argument. Do your own operation
     * Or define theme page build actions and fields.
     */
    public function processPage();
}
