<?php

namespace Src\Lib;

class CaptchaService
{

    private int $lenght = 6;
    private const CAPTCHA_KEY = "captcha";

    private static function getPermittedChars()
    {
        return '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    private function generateCaptchaString($length)
    {
        $string = substr(
            str_shuffle(
                self::getPermittedChars()
            ),
            0,
            $length
        );
        $_SESSION[self::CAPTCHA_KEY] = $string;
        return $string;
    }

    public function generateCaptchaImage()
    {
        $image = imagecreatetruecolor(200, 50);
        imageantialias($image, true);
        $colors = [];
        $red = rand(125, 175);
        $green = rand(125, 175);
        $blue = rand(125, 175);
        for ($i = 0; $i < 5; $i++) {
            $colors[] = imagecolorallocate(
                $image,
                $red - 20 * $i,
                $green - 20 * $i,
                $blue - 20 * $i
            );
        }
        imagefill($image, 0, 0, $colors[0]);
        for ($i = 0; $i < 10; $i++) {
            imagesetthickness($image, rand(2, 10));
            $rect_color = $colors[rand(1, 4)];
            imagerectangle(
                $image,
                rand(-10, 190),
                rand(-10, 10),
                rand(-10, 190),
                rand(40, 60),
                $rect_color
            );
        }
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        $textcolors = [$black, $white];
        
        $captchaString = $this->generateCaptchaString($this->lenght);
        $initial = 15;
        $letterSpace = 170 / $this->lenght;
        for ($i = 0; $i < $this->lenght; $i++) {
            imagestring(
                $image,
                20,
                $initial + $i * $letterSpace,
                rand(10, 30),
                $captchaString[$i],
                $textcolors[rand(0, 1)]
            );
        }
        return $image;
    }

    /**
     * @param string $captchaValue
     *  Captcha text that user entered.
     */
    public static function validateCaptcha($captchaValue)
    {
        return @$_SESSION[self::CAPTCHA_KEY] == $captchaValue;
    }
}
