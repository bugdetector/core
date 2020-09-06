<?php

namespace CoreDB\Kernel;

interface BaseControllerInterface
{
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
