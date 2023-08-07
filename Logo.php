<?php

class Logo
{
    protected mixed $path;
    protected mixed $isRandName;
    protected mixed $type;

    public function __construct($path = './', $isRandName = true, $type = 'png')
    {
        $this->path = $path;
        $this->isRandName = $isRandName;
        $this->type = $type;
    }

    public function logo($image, $watermark, $position, $transparency = 100, $prefix = 'watermark_')
    {
        if ((!file_exists($image)) || (!file_exists($watermark))) {
            die('图片不存在');
        }

        $imageInfo = self::getImageInfo($image);
        $waterInfo = self::getImageInfo($watermark);

        if (!$this->checkImage($imageInfo, $waterInfo)) {
            exit('图片水印太大');
        }

        $imageRes = self::openAnyImage($image);
        $waterRes = self::openAnyImage($watermark);

        $pos = $this->getPosition($position, $imageInfo, $waterInfo);
        imagecopymerge($imageRes,
            $waterRes,
            $pos['x'],
            $pos['y'],
            0,
            0,
            $waterInfo['width'],
            $waterInfo['height'],
            $transparency
        );

        $newName = $this->createNewName($image, $prefix);

        $newPath = rtrim($this->path, '/') . '/' . $newName;

        $this->saveImage($imageRes, $newPath);

        imagedestroy($imageRes);
        imagedestroy($waterRes);

        return $newPath;
    }

    public function scale()
    {
        //todo
    }

    static function getImageInfo($imagePath): array
    {
        $info = getimagesize($imagePath);

        $data['width'] = $info[0];
        $data['height'] = $info[1];
        $data['mime'] = $info['mime'];

        return $data;
    }

    private function checkImage(array $imageInfo, array $waterInfo): bool
    {
        if (($waterInfo['width'] > $imageInfo['width']) || ($waterInfo['height'] > $imageInfo['height'])) {
            return false;
        }
        return true;
    }

    static function openAnyImage($imagePath)
    {
        $mime = self::getImageInfo($imagePath)['mime'];
        $image = null;
        switch ($mime) {
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($imagePath);
                break;
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/wbmp':
                $image = imagecreatefromwbmp($imagePath);
                break;
        }
        return $image;
    }

    private function getPosition($position, array $imageInfo, array $waterInfo): array
    {
        $x = null;
        $y = null;
        switch ($position) {
            case 1:
                $x = 0;
                $y = 0;
                break;
            case 2:
                $x = ($imageInfo['width'] - $waterInfo['width']) / 2;
                $y = 0;
                break;
            case 3:
                $x = $imageInfo['width'] - $waterInfo['width'];
                $y = 0;
                break;
            case 4:
                $x = 0;
                $y = ($imageInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 5:
                $x = ($imageInfo['width'] - $waterInfo['width']) / 2;
                $y = ($imageInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 6:
                $x = $imageInfo['width'] - $waterInfo['width'];
                $y = ($imageInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 7:
                $x = 0;
                $y = $imageInfo['height'] - $waterInfo['height'];
                break;
            case 8:
                $x = ($imageInfo['width'] - $waterInfo['width']) / 2;
                $y = $imageInfo['height'] - $waterInfo['height'];
                break;
            case 9:
                $x = $imageInfo['width'] - $waterInfo['width'];
                $y = $imageInfo['height'] - $waterInfo['height'];
                break;
            case 0:
                $x = mt_rand(0, ($imageInfo['width'] - $waterInfo['width']));
                $y = mt_rand(0, ($imageInfo['height'] - $waterInfo['height']));
                break;
        }

        return ['x' => $x, 'y' => $y];
    }

    private function createNewName($imagePath, mixed $prefix): string
    {
        if ($this->isRandName) {
            $name = $prefix . uniqid() . '.' . $this->type;
        } else {
            $name = $prefix . pathinfo($imagePath)['filename'] . '.' . $this->type;
        }
        return $name;
    }

    private function saveImage(GdImage|false|null $imageRes, string $newPath): void
    {
        $func = 'image' . $this->type;
        $func($imageRes, $newPath);
    }

}

//$image = new Logo();
//$image->logo('1.jpg','2.png',7);

