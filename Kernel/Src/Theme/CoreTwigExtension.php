<?php

namespace Src\Theme;

use CoreDB\Kernel\Database\DatabaseInstallationException;
use CoreDB\Kernel\Environment;
use Src\Entity\State;
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
            new TwigFunction("defaultLanguage", [Translation::class, "getDefaultLanguage"]),
            new TwigFunction("direction", [Translation::class, "getDirection"]),
            new TwigFunction("getAvailableLanguageList", [Translation::class, "getAvailableLanguageList"]),
            new TwigFunction("getTranslationIcons", [Translation::class, "getTranslationIcons"]),
            new TwigFunction("user", [\CoreDB::class, "currentUser"]),
            new TwigFunction("variable", [$this, "getVariable"]),
            new TwigFunction("state", [$this, "getState"]),
            new TwigFunction("isDev", [Environment::class, "isDev"]),
            new TwigFunction("isStaging", [Environment::class, "isStaging"]),
            new TwigFunction("isProd", [Environment::class, "isProd"]),
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

    public function getState($key)
    {
        try {
            return State::getByKey($key);
        } catch (DatabaseInstallationException $ex) {
            return null;
        }
    }
}
