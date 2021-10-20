<?php

namespace Src\Controller;

use CoreDB\Kernel\ServiceController;
use CoreDB\Kernel\XMLSitemapEntityInterface;
use SimpleXMLElement;
use Symfony\Component\Yaml\Yaml;

class SitemapXmlController extends ServiceController
{
   
    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        if (!$this->method) {
            $this->method = "generateSitemapXml";
        }
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function generateSitemapXml()
    {
        $this->response_type = self::RESPONSE_TYPE_RAW;
        $xmlEntityClasses = Yaml::parseFile(__DIR__ . "/../../../config/xmlsitemap_config.yml");
        $xml = new SimpleXMLElement(
            "<?xml version='1.0' encoding='utf-8'?>" .
            "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'></urlset>"
        );
        
        /**
         * @var XMLSitemapEntityInterface
         */
        foreach ($xmlEntityClasses as $xmlEntity) {
            foreach ($xmlEntity::getXmlSitemapUrls() as $xmlSitemapUrl) {
                $url = $xml->addChild("url");
                $url->addChild("loc", $xmlSitemapUrl->loc);
                $url->addChild("lastmod", $xmlSitemapUrl->lastmod);
            }
        }
        return $xml->asXML();
    }
}
