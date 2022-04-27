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
        $red = random_int(200, 255);
        $green = random_int(200, 255);
        $blue = random_int(200, 255);
        for ($i = 0; $i < 5; $i++) {
            $colors[] = imagecolorallocate(
                $image,
                ($red - 20 * $i) % 255,
                ($green - 20 * $i) % 255,
                ($blue - 20 * $i) % 255
            );
        }
        imagefill($image, 0, 0, $colors[random_int(0, 4)]);
        for ($i = 0; $i < 10; $i++) {
            imagesetthickness($image, random_int(1, 2));
            $rect_color = $colors[random_int(0, 4)];
            imagerectangle(
                $image,
                random_int(0, 200),
                random_int(0, 50),
                random_int(-10, 190),
                random_int(40, 60),
                $rect_color
            );
        }
        for ($i = 0; $i < 10; $i++) {
            imagesetthickness($image, random_int(1, 2));
            $rect_color = $colors[random_int(0, 4)];
            imageellipse(
                $image,
                random_int(0, 200),
                random_int(0, 50),
                random_int(-10, 190),
                random_int(40, 60),
                $rect_color
            );
        }
        $black = imagecolorallocate($image, 20, 20, 20);
        $captchaString = $this->generateCaptchaString($this->lenght);
        $initial = 15;
        $letterSpace = 170 / $this->lenght;
        for ($i = 0; $i < $this->lenght; $i++) {
            imagestring(
                $image,
                20,
                $initial + $i * $letterSpace,
                random_int(5, 35),
                $captchaString[$i],
                $black
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
