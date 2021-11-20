<?php

namespace Src\Controller;

use CoreDB\Kernel\ServiceController;
use Src\Lib\CaptchaService;

class CaptchaController extends ServiceController
{
    public function checkAccess(): bool
    {
        return true;
    }
    public function generateCaptchaImage()
    {
        $this->response_type = self::RESPONSE_TYPE_RAW;
        $captchaService = new CaptchaService();
        $image = $captchaService->generateCaptchaImage();
        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
}
