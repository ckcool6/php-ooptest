<?php

class Person{
    public $name;
    public $age;

    /**
     * @param $name
     * @param $age
     */
    public function __construct($name, $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

}

class Woman extends Person{

}

$jack = new Person('jack',12);
$lyra = new Person('lyra',14);

