<?php

class Upload
{
    protected string $path = './upload/';
    protected array $allowSuffix = ['jpg', 'jpeg', 'gif', 'wbmp', 'png'];
    protected array $allowMime = ['image/jpeg', 'image/gif', 'image/wbmp', 'image/png'];
    protected int $maxSize = 200000;
    protected bool $isRandName = true;
    protected string $prefix = 'up_';

    protected int $errorNumber;
    protected string $errorInfo;

    protected string $oldName;
    protected string $suffix;
    protected int $size;
    protected string $mime;
    protected string $tmpName;

    protected string $newName;

    public function __construct($arr = [])
    {
        foreach ($arr as $key => $value) {
            $this->setOption($key, $value);
        }
    }

    public function __get(string $name)
    {
        if ($name == 'errorNumber'){
            return $this->errorNumber;
        } else if ($name == 'errorInfo'){
            return $this->getErrorInfo();
        }
        return 0;
    }

    public function upload($key): false|string
    {
        if (empty($this->path)) {
            $this->setOption('errorNumber', -1);
            return false;
        }

        if (!$this->check()) {
            $this->setOption('errorNumber', -2);
            return false;
        }

        $error = $_FILES[$key]['error'];
        if ($error) {
            $this->setOption('errorNumber', $error);
            return false;
        } else {
            $this->getFileInfo($key);
        }

        if (!$this->checkSize() || !$this->checkMime() || !$this->checkSuffix()) {
            return false;
        }

        $this->newName = $this->createNewName();

        if (is_uploaded_file($this->tmpName)) {
            if (move_uploaded_file($this->tmpName, $this->path . $this->newName)) {
                return $this->path . $this->newName;
            } else {
                $this->setOption('errorNumber', -7);
                return false;
            }
        } else {
            $this->setOption('errorNumber', -6);
            return false;
        }
    }

    private function setOption(int|string $key, mixed $value): void
    {
        $keys = array_keys(get_class_vars(__CLASS__));
        if (in_array($key, $keys)) {
            $this->$key = $value;
        }
    }

    private function check(): bool
    {
        if (!file_exists($this->path) || !is_dir($this->path)) {
            return mkdir($this->path, 0777, true);
        }

        if (!is_writeable($this->path)) {
            return chmod($this->path, 0777);
        }

        return true;
    }

    private function getFileInfo($key): void
    {
        $this->oldName = $_FILES[$key]['name'];
        $this->mime = $_FILES[$key]['type'];
        $this->tmpName = $_FILES[$key]['tmp_name'];
        $this->size = $_FILES[$key]['size'];
        $this->suffix = pathinfo($this->oldName)['extension'];
    }

    private function checkSize(): bool
    {
        if ($this->size > $this->maxSize) {
            $this->setOption('errorNumber', -3);
            return false;
        }
        return true;
    }

    private function checkSuffix(): bool
    {
        if (!in_array($this->suffix, $this->allowSuffix)) {
            $this->setOption('errorNumber', -5);
            return false;
        }
        return true;
    }

    private function checkMime(): bool
    {
        if (!in_array($this->mime, $this->allowMime)) {
            $this->setOption('errorNumber', -4);
            return false;
        }
        return true;
    }

    private function createNewName(): string
    {
        if ($this->isRandName) {
            $name = $this->prefix . uniqid() . '.' . $this->suffix;
        } else {
            $name = $this->prefix . $this->oldName;
        }
        return $name;
    }

    private function getErrorInfo(): string
    {
        $str = null;
        switch ($this->errorNumber){
            case -1:
                $str = '文件路径没有设置';
                break;
            case -2:
                $str = '文件路径不是目录或没有权限';
                break;
            case -3:
                $str = '文件大小超出指定范围';
                break;
            case -4:
                $str = '文件Mime类型不合法';
                break;
            case -5:
                $str = '文件后缀不合法';
                break;
            case -6:
                $str = '不是上传文件';
                break;
            case -7:
                $str = '文件上传失败';
                break;
        }
        return $str;
    }

}

$up = new Upload();
$up->upload('fm');