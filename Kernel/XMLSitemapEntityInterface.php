<?php

namespace CoreDB\Kernel;

interface XMLSitemapEntityInterface
{
    /**
     * Get all xml sitemap urls for xml sitemap.
     * @return XMLSitemapUrl[]
     */
    public static function getXmlSitemapUrls(): array;
}
