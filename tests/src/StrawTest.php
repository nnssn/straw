<?php

namespace Straw;

require_once 'SampleManual.php';

class StrawTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Straw\Straw
     */
    protected $object;

    protected function setUp()
    {
        $manual = new SampleManual;
        $this->object = Straw::open($manual);
    }

    /**
     * @covers Straw\Straw::open
     * @test
     */
    public function インスタンスの生成()
    {
        $straw = Straw::open();
        $this->assertInstanceOf('Straw\Straw', $straw);
    }

    /**
     * @covers Straw\Straw::bool
     * @test
     */
    public function boolルールの登録()
    {
        $rule = $this->object->bool('test');
        $this->assertNotNull($rule('0'));
        $this->assertNotNull($rule('1'));
        $this->assertNull($rule('10'));
        $this->assertNull($rule('abc'));
    }

    /**
     * @covers Straw\Straw::alpha
     * @test
     */
    public function 英字ルールの登録()
    {
        $rule = $this->object->alpha('test');
        $this->assertNotNull($rule('abcde'));
        $this->assertNull($rule('abcd5'));
    }

    /**
     * @covers Straw\Straw::alnum
     * @test
     */
    public function 英数字ルールの登録()
    {
        $rule = $this->object->alnum('test');
        $this->assertNotNull($rule('a1b2c3'));
        $this->assertNull($rule('a1--b2'));
    }

    /**
     * @covers Straw\Straw::number
     * @test
     */
    public function 数字ルールの登録()
    {
        $rule = $this->object->number('test');
        $this->assertNotNull($rule('12345'));
        $this->assertNull($rule('1234e'));
        $this->assertNull($rule('12-34'));
    }

    /**
     * @covers Straw\Straw::alpha
     * @covers Straw\Straw::alnum
     * @covers Straw\Straw::number
     * @test
     */
    public function 英字系ルールはアンダーバーを含めてもよい()
    {
        $alpha    = $this->object->alpha('test');
        $alphanum = $this->object->alnum('test');
        $num      = $this->object->number('test');

        $input_alpha = 'abc_def';
        $input_num   = '123_456';

        $this->assertNull($num($input_num));
        $this->assertNotNull($alpha($input_alpha));
        $this->assertNotNull($alphanum($input_alpha));
        $this->assertNotNull($alphanum($input_num));
    }

    /**
     * @covers Straw\Straw::alphaList
     * @test
     */
    public function 英字リストルールの登録()
    {
        $rule = $this->object->alphaList('test');
        $this->assertNotNull($rule('abc'));
        $this->assertNotNull($rule('abc,def,ghi'));
        $this->assertNull($rule('abc,def,123'));
    }

    /**
     * @covers Straw\Straw::alnumList
     * @test
     */
    public function 英数字リストルールの登録()
    {
        $rule = $this->object->alnumList('test');
        $this->assertNotNull($rule('abc_123'));
        $this->assertNotNull($rule('123,abc,456def'));
        $this->assertNull($rule('123,abc,あいうえお'));
    }

    /**
     * @covers Straw\Straw::numberList
     * @test
     */
    public function 数字リストルールの登録()
    {
        $rule = $this->object->numberList('test');
        $this->assertNotNull($rule('123'));
        $this->assertNotNull($rule('123,456,789'));
        $this->assertNull($rule('123,456,abc'));
    }

    /**
     * @covers Straw\Straw::alphaPair
     * @test
     */
    public function 英字ペアルールの登録()
    {
        $rule = $this->object->alphaPair('test');
        $this->assertNotNull($rule('abc:def'));
        $this->assertNull($rule('abc:'));
        $this->assertNull($rule('abc:def:ghi'));
    }

    /**
     * @covers Straw\Straw::alnumPair
     * @test
     */
    public function 英数字ペアルールの登録()
    {
        $rule = $this->object->alnumPair('test');
        $this->assertNotNull($rule('abc1:def2'));
        $this->assertNull($rule('abc1:'));
        $this->assertNull($rule('abc1:def2:ghi3'));
    }

    /**
     * @covers Straw\Straw::numberPair
     * @test
     */
    public function 数字ペアルールの登録()
    {
        $rule = $this->object->numberPair('test');
        $this->assertNotNull($rule('123:456'));
        $this->assertNotNull($rule('123:123'));
        $this->assertNull($rule('123:'));
        $this->assertNull($rule('123:456:789'));
    }

    /**
     * @covers Straw\Straw::numberRange
     * @test
     */
    public function 数字範囲ルールの登録()
    {
        $rule = $this->object->numberRange('test');
        $this->assertNotNull($rule('123-456'));
        $this->assertNull($rule('123-'));
        $this->assertNull($rule('-'));
        $this->assertNull($rule('10-9')); //a > b

        $rule = $this->object->numberRange('test', '0-1000');
        $this->assertNotNull($rule('123-'));
        $this->assertNotNull($rule('-456'));
    }

    /**
     * @covers Straw\Straw::datetimeRange
     * @test
     */
    public function 日時範囲ルールの登録()
    {
        $rule = $this->object->datetimeRange('test');
        $this->assertNotNull($rule('20160101-20160115'));
        $this->assertNull($rule('20160101a-20160115b'));
        $this->assertNull($rule('-'));
        $this->assertNull($rule('20160115-20160101')); //a > b
    }

    /**
     * @covers Straw\Straw::number
     * @covers Straw\Straw::numberList
     * @covers Straw\Straw::numberRange
     * @test
     */
    public function 数字系ルールの値範囲を制限()
    {
        $num       = $this->object->number('test', null, array(0, 100));
        $num_list  = $this->object->numberList('test', null, array(0, 100));
        $num_range = $this->object->numberRange('test', null, array(1, 100));

        $this->assertNotNull($num(10));
        $this->assertNull($num(110));

        $this->assertNotNull($num_list('0,99,100'));
        $this->assertNull($num_list('0,100,101'));

        $this->assertNotNull($num_range('1-100'));
        $this->assertNull($num_range('50-110'));
    }

    /**
     * @covers Straw\Straw::alnum
     * @test
     */
    public function 第3引数で文字列の長さを制限()
    {
        $alpha_8     = $this->object->alnum('test', null, 8);
        $alpha_8to   = $this->object->alnum('test', null, array(8, null));
        $alpha_8to12 = $this->object->alnum('test', null, array(8, 12));
        $alpha_to12  = $this->object->alnum('test', null, array(null, 12));

        $length7  = 'a234567';
        $length8  = 'a2345678';
        $length13 = 'a234567890123';

        //8
        $this->assertNull($alpha_8($length7));
        $this->assertNotNull($alpha_8($length8));
        $this->assertNull($alpha_8($length13));

        //8-
        $this->assertNull($alpha_8to($length7));
        $this->assertNotNull($alpha_8to($length8));
        $this->assertNotNull($alpha_8to($length13));

        //8-12
        $this->assertNull($alpha_8to12($length7));
        $this->assertNotNull($alpha_8to12($length8));
        $this->assertNull($alpha_8to12($length13));

        //-12
        $this->assertNotNull($alpha_to12($length7));
        $this->assertNotNull($alpha_to12($length8));
        $this->assertNull($alpha_to12($length13));
    }

    /**
     * @covers Straw\Straw::alnumList
     * @test
     */
    public function 第3引数でリストの文字列の長さを制限()
    {
        $list_8    = $this->object->alnumList('test', null, 8);
        $list_8to  = $this->object->alnumList('test', null, array(8, null));

        $input_single8  = 'a2345678';
        $input_single13 = 'a234567890123';
        $input_list8    = 'a2345678,b2345678';
        $input_list13   = 'a234567890123,a234567890123';

        //list_8
        $this->assertNotNull($list_8($input_single8));
        $this->assertNull($list_8($input_single13));
        $this->assertNotNull($list_8($input_list8));
        $this->assertNull($list_8($input_list13));

        //list_8to
        $this->assertNotNull($list_8to($input_single8));
        $this->assertNotNull($list_8to($input_single13));
        $this->assertNotNull($list_8to($input_list8));
        $this->assertNotNull($list_8to($input_list13));
    }

    /**
     * @test_xxxxxxxxxx
     */
    public function 複数キーからルールを登録()
    {
        $rule = $this->object->alnumPair(array('key1', 'key2'));
        $this->assertNotNull($rule(array('val1', 'val2')));
        $this->assertNull($rule(array('val1')));

        $rule = $this->object->alnumList(array('key1', 'key2'));
        $this->assertNotNull($rule(array('val1', 'val2')));
        $this->assertNotNull($rule(array('val1', 'val2', 'val3')));
    }

    /**
     * @covers Straw\Straw::set
     * @test
     */
    public function setルール()
    {
        $candidates = array('one', 'two', 'three');
        $rule = $this->object->set('test', null, $candidates);
        $this->assertNotNull($rule('one'));
        $this->assertNotNull($rule(array('two', 'three')));
        $this->assertNull($rule(array('one', 'four')));
    }

    /**
     * @covers Straw\Straw::enum
     * @test
     */
    public function enumルール()
    {
        $candidates = array('one', 'two', 'three');
        $rule = $this->object->enum('test', null, $candidates);
        $this->assertNotNull($rule('one'));
        $this->assertNull($rule(array('one')));
        $this->assertNull($rule(array('two', 'three')));
        $this->assertNull($rule(array('one', 'four')));
    }
}
