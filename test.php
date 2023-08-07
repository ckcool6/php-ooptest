<?php

include 'Template.php';

$tpl = new Template();

$title = 'The Best language is PHP';
$data = ['aaa', 'bbb'];

$tpl->assign('title', $title);
$tpl->assign('data', $data);

$tpl->display('test.html');