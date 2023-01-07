<?php

require './init.php';
require './repository.php';

$repos = new MenuRepository();

//$model = $repos->create([
//    'name' => '菜单1'
//]);

dump($repos->where(['name' => 1])->get());