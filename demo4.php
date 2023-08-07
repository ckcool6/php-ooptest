<?php

class Animal
{
    public $name = 'little Dog';
    protected $age = '3';
    private $height = 120;

    public function __get($name)
    {
        if ($name == 'age') {
            return $this->age;
        }
    }

    public function __set($name, $value)
    {
        var_dump($name, $value);
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}

$a = new Animal();
//echo $a->age;


