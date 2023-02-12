<?php

require '../bootstrap.php';

use Larmias\Validation\Validator;
use Larmias\Validation\Exceptions\ValidateException;

$validator = Validator::make();

$data = [
    'id' => '302630808315891712',
    'name' => '2',
    'parent_id' => 0,
    'icon' => 'fa fa-home',
    'route' => '',
    'sort' => 0,
    'type' => 0,
    'open_type' => '_iframe',
    'status' => '1',
    'memo' => '',
];

$validator->rule([
    'name' => 'required',
    'parent_id' => 'required|integer|min:0',
    'icon' => 'required',
    'sort' => 'required|integer|min:0',
    'type' => 'required|in:0,1',
    'open_type' => 'required',
    'status' => 'required|in:0,1',
    'memo' => 'max:200',
])->batch(true);

try {
    $check = $validator->data($data)->fails();
    if (!$check) {
        dump($validator->errors());
    }
} catch (ValidateException $e) {
    println($e->getMessage());
    dump($e->getErrors());
}