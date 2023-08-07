<?php

class Code
{
    protected int $number;
    protected int $codeType;
    protected int $width;
    protected int $height;
    protected GdImage $image;
    protected string $code;

    public function __construct($number, $codeType, $width, $height)
    {
        $this->number = $number;
        $this->codeType = $codeType;
        $this->width = $width;
        $this->height = $height;

        $this->code = $this->createCode();
    }

    public function __destruct()
    {
        imagedestroy($this->image);
    }

    public function __get($name)
    {
        if ($name == 'code') {
            return $this->code;
        }
        return false;
    }

    protected function createCode()
    {
        switch ($this->codeType) {
            case 0:
                $code = $this->getNumberCode();
                break;
            case 1:
                $code = $this->getCharCode();
                break;
            case 2:
                $code = $this->getNumCharCode();
                break;
            default:
                die('不支持');
        }
        return $code;
    }

    protected function getNumberCode(): string
    {
        $str = join('', range(0, 9));
        return substr(str_shuffle($str), 0, $this->number);
    }

    protected function getCharCode(): string
    {
        $str = join('', range('a', 'z'));
        $str = $str . strtoupper($str);
        return substr(str_shuffle($str), 0, $this->number);
    }

    protected function getNumCharCode(): string
    {
        $numstr = join('', range(0, 9));
        $str = join('', range('a', 'z'));

        $str = $numstr . $str . strtoupper($str);

        return substr(str_shuffle($str), 0, $this->number);
    }

    protected function createImage(): void
    {
        $this->image = imagecreatetruecolor($this->width, $this->height);
    }

    protected function fillback(): void
    {
        imagefill($this->image, 0, 0, $this->lightColor());
    }

    protected function lightColor(): false|int
    {
        return imagecolorallocate($this->image, mt_rand(130, 255), mt_rand(130, 255), mt_rand(130, 255));
    }

    protected function darkColor(): false|int
    {
        return imagecolorallocate($this->image, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
    }

    protected function drawChar(): void
    {
        $width = ceil($this->width / $this->number);
        for ($i = 0; $i < $this->number; $i++) {
            $x = mt_rand($i * $width - 5, ($i + 1) * $width - 5);
            $y = mt_rand(0, $this->height - 15);
            imagechar($this->image, 5, $x, $y, $this->code[$i], $this->darkColor());
        }
    }

    protected function drawDisturb(): void
    {
        for ($i = 0; $i < 150; $i++) {
            $x = mt_rand(0, $this->width);
            $y = mt_rand(0, $this->height);
            imagesetpixel($this->image, $x, $y, $this->lightColor());
        }
    }

    protected function show(): void
    {
        header('Content-Type:image/png');
        imagepng($this->image);
    }

    public function outImage(): void
    {
        $this->createImage();
        $this->fillback();
        $this->drawChar();
        $this->drawDisturb();
        $this->show();
    }

}

//
$testcode = new Code(4, 2, 100, 50);

$testcode->outImage();
