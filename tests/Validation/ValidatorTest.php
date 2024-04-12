<?php

declare(strict_types=1);

namespace LarmiasTest\Validation;

use Larmias\Contracts\ValidatorInterface;
use Larmias\Validation\Rules;
use Larmias\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @return void
     */
    public function testCheckData(): void
    {
        $validator = Validator::make();
        $validator->rule([
            'name' => 'required|max:10',
            'age' => 'required|integer|between:0,100|max:100|min:0',
        ])->failException(false);

        $validator->data([
            'name' => 'test123',
            'age' => 23,
        ]);

        $this->validate($validator, true);
    }

    /**
     * @return void
     */
    public function testMakeRules(): void
    {
        $rules1 = array_values((new Rules('required|between:1,2'))->getRules());
        $this->assertSame($rules1[0]->getName(), 'required');
        $this->assertEmpty($rules1[0]->getArgs());
        $this->assertSame($rules1[1]->getName(), 'between');
        $this->assertSame($rules1[1]->getArgs(), ['1', '2']);
        $rules2 = array_values((new Rules(['required', 'length:6', 'between' => [1, 2]]))->getRules());
        $this->assertSame($rules2[0]->getName(), 'required');
        $this->assertEmpty($rules2[0]->getArgs());
        $this->assertSame($rules2[1]->getName(), 'length');
        $this->assertSame($rules2[1]->getArgs(), ['6']);
        $this->assertSame($rules2[2]->getName(), 'between');
        $this->assertSame($rules2[2]->getArgs(), [1, 2]);
    }

    /**
     * @return void
     */
    public function testHasRules(): void
    {
        $rules = new Rules('required|between:1,2');
        $this->assertTrue($rules->has('required'));
        $this->assertTrue($rules->has('between'));
        $this->assertTrue($rules->has(['required', 'between']));
        $this->assertFalse($rules->has(['length']));
    }

    /**
     * @return ValidatorInterface
     */
    protected function make(): ValidatorInterface
    {
        $validator = Validator::make();

        $validator->rule([
            'name' => 'required',
            'age' => 'required',
            'custom' => ['required', 'checkCustom' => function (mixed $value) {
                return false;
            }],
        ]);

        return $validator->scenes([
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
        ])->message(['name.required' => '请填写姓名'])
            ->attribute(['age' => '年龄'])
            ->batch(true)
            ->failException(true);
    }

    protected function validate(Validator $validator, bool $check)
    {
        $fails = $validator->fails();
        $errors = $validator->errors();
        if (!empty($errors)) {
            var_dump($errors);
        }
        $this->assertSame($fails, $check);
    }
}