<?php

class Template
{
    protected string $viewDir = './view/';
    protected string $cacheDir = './cache/';
    protected int $lifetime = 3600;
    protected array $vars = [];

    public function __construct($viewDir = null, $cacheDir = null, $lifetime = null)
    {
        if (!empty($viewDir)) {
            if ($this->checkDir($viewDir)) {
                $this->viewDir = $viewDir;
            }
        }

        if (!empty($cacheDir)) {
            if ($this->checkDir($cacheDir)) {
                $this->cacheDir = $cacheDir;
            }
        }

        if (!empty($lifetime)) {
            $this->lifetime = $lifetime;
        }
    }

    public function assign($name, $value): void
    {
        $this->vars[$name] = $value;
    }

    public function display($viewName, $isInclude = true, $uri = null): void
    {
        $viewPath = rtrim($this->viewDir, '/') . '/' . $viewName;
        if (!file_exists($viewPath)) {
            die("模板文件不存在");
        }

        $cacheName = md5($viewName . $uri) . '.php';
        $cachePath = rtrim($this->cacheDir, '/') . '/' . $cacheName;
        if (!file_exists($cachePath)) {
            $php = $this->compile($viewPath);
            file_put_contents($cachePath, $php);
        } else {
            $isTimeout = (filectime($cachePath) + $this->lifetime) > time() ? false : true;
            $isChange = filemtime($viewPath) > filemtime($cachePath) ? true : false;

            if ($isTimeout || $isChange) {
                $php = $this->compile($viewPath);
                file_put_contents($cachePath, $php);
            }
        }

        if ($isInclude) {
            extract($this->vars);
            include $cachePath;
        }
    }

    private function checkDir(mixed $dirPath): bool
    {
        if (!file_exists($dirPath) || !is_dir($dirPath)) {
            return mkdir($dirPath, 0755, true);
        }

        if (!is_writeable($dirPath) || !is_readable($dirPath)) {
            return chmod($dirPath, 0755);
        }

        return true;
    }

    private function compile(string $filePath): array|string|null
    {
        $html = file_get_contents($filePath);

        $array = [
            //replace var
            '{$%%}' => '<?=$\1; ?>',
            //replace foreach
            '{foreach %%}' => '<?php foreach (\1): ?>',
            '{/foreach}' => '<?php endforeach?>',
            '{include %%}' => '',
        ];

        foreach ($array as $key => $value) {
            $pattern = '#' . str_replace('%%', '(.+?)', preg_quote($key, '#')) . '#';
            if (str_contains($pattern, 'include')) {
                $html = preg_replace_callback($pattern, [$this, 'parseInclude'], $html);
            } else {
                $html = preg_replace($pattern, $value, $html);
            }
        }
        return $html;
    }

    private function parseInclude($data): string
    {
        $fileName = trim($data[1], '\'"');
        $this->display($fileName, false);
        $cacheName = md5($fileName) . 'php';
        $cachePath = rtrim($this->cacheDir, '/') . '/' . $cacheName;

        return '<?php include "' . $cachePath . '"?>';
    }

}