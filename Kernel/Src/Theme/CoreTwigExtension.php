<?php

namespace Src\Theme;

use CoreDB\Kernel\Database\DatabaseInstallationException;
use Src\Entity\Translation;
use Src\Entity\Variable;
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
            new TwigFunction("variable", [$this, "getVariable"])
        ];
    }

    public function getHashedFilemTime($file_path)
    {
        return hash("MD5", filemtime($file_path));
    }

    public function getVariable($key)
    {
        try {
            return Variable::getByKey($key);
        } catch (DatabaseInstallationException $ex) {
            return null;
        }
    }
}
