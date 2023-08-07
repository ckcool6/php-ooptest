<?php

class Page
{
    protected int $number;
    protected int $totalCount;
    protected int $page;
    protected int $totalPage;
    protected string $url;

    public function __construct($number, $totalCount)
    {
        $this->number = $number;
        $this->totalCount = $totalCount;

        $this->totalPage = $this->getTotalPage();
        $this->page = $this->getPage();
        $this->url = $this->getUrl();
    }

    protected function getTotalPage(): int
    {
        return ceil($this->totalCount / $this->number);
    }

    protected function getPage(): int
    {
        if (empty($_GET['page'])) {
            $page = 1;
        } else if ($_GET['page'] > $this->totalPage) {
            $page = $this->totalPage;
        } else if ($_GET['page'] < 1) {
            $page = 1;
        } else {
            $page = $_GET['page'];
        }

        return $page;
    }

    protected function getUrl(): string
    {
        $scheme = $_SERVER['REQUEST_SCHEME'];
        $host = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        $uri = $_SERVER['REQUEST_URI'];

        $uriArray = parse_url($uri);
        $path = $uriArray['path'];

        if (!empty($uriArray['query'])) {
            parse_str($uriArray['query'], $array);
            unset($array['page']);
            $query = http_build_query($array);

            if ($query != '') {
                $path = $path . '?' . $query;
            }
        }

        return $scheme . '//' . $host . ':' . $port . $path;
    }

    protected function setUrl($str): string
    {
        if (str_contains($this->url, '?')) {
            $url = $this->url . '&' . $str;
        } else {
            $url = $this->url . '?' . $str;
        }

        return $url;
    }

    public function allUrl(): array
    {
        return [
            'first' => $this->first(),
            'prev' => $this->prev(),
            'next' => $this->next(),
            'end' => $this->end(),
        ];
    }

    public function first(): string
    {
        return $this->setUrl('page=1');
    }

    public function next(): string
    {
        if ($this->page + 1 > $this->totalPage) {
            $page = $this->totalPage;
        } else {
            $page = $this->page + 1;
        }
        return $this->setUrl('page=' . $page);
    }

    public function prev(): string
    {
        if ($this->page - 1 < 1) {
            $page = 1;

        } else {
            $page = $this->page - 1;
        }

        return $this->setUrl('page=' . $page);
    }

    public function end(): string
    {
        return $this->setUrl('page=' . $this->totalPage);
    }

    public function limit(): string
    {
        $offset = ($this->page - 1) * $this->number;
        return $offset . ',' . $this->number;
    }

}

$page = new Page(5, 60);