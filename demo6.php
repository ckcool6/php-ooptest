<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=demo;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die($e->getMessage());
}

//var_dump($pdo);
$sql = 'insert into user(name,password,money) values ("lucy","000",555)';
$res = $pdo->exec($sql);

if ($res > 0) {
    echo 'insert success';
}

