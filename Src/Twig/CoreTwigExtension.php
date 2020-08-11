<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CoreTwigExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getHashedFilemTime', [$this, 'getHashedFilemTime']),
        ];
    }

    public function getHashedFilemTime($file_path)
    {
        return hash("MD5", filemtime($file_path));
    }
}
