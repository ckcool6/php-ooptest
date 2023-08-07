<?php

class Father
{
    public function jump()
    {
        echo '3';
    }
}

class Son extends Father
{
    public function jump()
    {
        parent::jump();
        echo 'run';
    }
}

$jack = new Son();
$jack->jump();

