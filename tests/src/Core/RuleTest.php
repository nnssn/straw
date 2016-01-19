<?php

namespace Nnssn\Straw\Core;

class RuleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Rule
     */
    protected $object;

    protected function setUp()
    {
        $filter = function ($value) {
            return $value;
        };
        $this->object = new Rule('input_key', null, '/.*/', null);
    }

    /**
     * @covers Nnssn\Straw\Core\Rule::to
     * @test
     */
    public function 出力先キーを変更()
    {
        $output_key = 'output_key';
        $this->object->to($output_key);
        $rule = $this->object;
        $key_value = $rule('abc');
        $this->assertEquals($output_key, $key_value['key']);
    }

    /**
     * @covers Nnssn\Straw\Core\Rule::format
     * @test
     */
    public function チェックを通った後の値を編集する()
    {
        $value  = 'sample';
        $add    = 'hogehoge';

        $rule   = $this->object;
        $result = $rule($value);

        $this->object->format(function ($value) use ($add) {
            return $value . $add;
        });
        $result2 = $rule($value);

        $this->assertEquals($result['value'] . $add, $result2['value']);
    }

    /**
     * @covers Nnssn\Straw\Core\Rule::__invoke
     * @test
     */
    public function invoke()
    {
        $rule   = $this->object;
        $this->assertArrayHasKey('key', $rule(123));
        $this->assertNull($rule(null));

        $rule2   = new Rule('test', 123, '/\d+/', null);
        $result2 = $rule2(null);
        $this->assertEquals('123', $result2['value']);
    }
}
