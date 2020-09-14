<?php
namespace App\Twig;

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
        ];
    }

    public function getHashedFilemTime($file_path)
    {
        return hash("MD5", filemtime($file_path));
    }
}
