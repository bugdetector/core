<?php

namespace CoreDB\Kernel;

interface ControllerInterface
{

    /**
     * Check is page accessible. If returns false not found page will shown
     *
     * @return bool
     *  Is accessible
     */
    public function checkAccess(): bool;

    /**
     * Process page, for example use argument. Do your own operation
     * Or define theme page build actions and fields.
     */
    public function processPage();

    /**
     * Set page title
     * @param string $title
     *  Page Title
     */
    public function setTitle(string $title) : void;
    /**
     * Print messages
     */
    public function printMessages();
    /**
     * Return template file name. Ex: "page.twig"
     * @return string
     *  Template file name
     */
    public function getTemplateFile() : string;

    /**
     * Return Url
     * @return string
     *  URL
     */
    public static function getUrl() : string;
}
