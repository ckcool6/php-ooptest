<?php

/*class Person{
    public $age;

    public function eat(){
        echo 'eating.......';
    }
}*/

$lucy = new Person();
$lucy->eat();
$lucy->age = 18;
var_dump($lucy->age);

