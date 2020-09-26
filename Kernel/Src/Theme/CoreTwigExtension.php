<?php
namespace Src\Theme;

use Src\Entity\Translation;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CoreTwigExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getHashedFilemTime', [$this, 'getHashedFilemTime']),
            new TwigFunction('t', [Translation::class, 'getTranslation']),
            new TwigFunction("language", [Translation::class, "getLanguage"]),
            new TwigFunction("user", [\CoreDB::class, "currentUser"]),
        ];
    }

    public function getHashedFilemTime($file_path)
    {
        return hash("MD5", filemtime($file_path));
    }
}
