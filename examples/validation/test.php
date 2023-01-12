<?php

require '../bootstrap.php';

use Larmias\Validation\Validator;
use Larmias\Validation\Exceptions\ValidateException;

$validator = Validator::make();

$validator->rule([
    'name' => 'required',
    'age' => 'required',
    'custom' => ['required', 'checkCustom' => function (mixed $value) {
        return false;
    }],
]);

$validator->scenes([
    'create' => ['name', 'age'],
    'edit' => function (Validator $validator) {
        $validator->append([
            'name' => ['checkName' => function () {
                return false;
            }]
        ])->remove([
            'name' => ['required']
        ]);
    },
])->message(['name.required' => '请填写姓名'])->attribute(['age' => '年龄'])->batch(true)->failException(true);

$validator->scene('edit');

try {
    $check = $validator->fails();
    if (!$check) {
        dump($validator->errors());
    }
} catch (ValidateException $e) {
    println($e->getMessage());
    dump($e->getErrors());
}