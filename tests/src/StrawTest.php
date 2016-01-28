<?php

namespace Nnssn\Straw;

class StrawTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Nnssn\Straw\Straw::open
     * @test
     */
    public function Makerインスタンスの生成()
    {
        $maker = Straw::open();
        $this->assertInstanceOf('Nnssn\Straw\Core\Maker', $maker);
    }

    /**
     * @covers Nnssn\Straw\Straw::options
     * @test
     */
    public function 設定を変更()
    {
        $rule1  = Straw::alpha('test');
        $value1 = $rule1('abc-def');

        $options = array(
            'alpha' => 'a-zA-Z-'
        );
        Straw::options($options);
        $rule2  = Straw::alpha('test');
        $value2 = $rule2('abc-def');

        $this->assertNull($value1);
        $this->assertNotNull($value2);
    }

    /**
     * @covers Nnssn\Straw\Straw::newRule
     * @test
     */
    public function 独自ルールの登録()
    {
        $rule = Straw::newRule('test', '/^[a-z]+$/');
        $this->assertNotNull($rule('abcde'));
        $this->assertNull($rule('abcd5'));
    }

    /**
     * @covers Nnssn\Straw\Straw::alpha
     * @test
     */
    public function 英字系ルールはアンダーバーを含めてもよい()
    {
        $alpha    = Straw::alpha('alpha');
        $num      = Straw::num('num');
        $alphanum = Straw::alphanum('alphanum');

        $input_alpha = 'abc_def';
        $input_num   = '123_456';

        $this->assertNull($num($input_num));
        $this->assertNotNull($alpha($input_alpha));
        $this->assertNotNull($alphanum($input_alpha));
        $this->assertNotNull($alphanum($input_num));
    }

    /**
     * @covers Nnssn\Straw\Straw::alpha
     * @test
     */
    public function 英字ルールの登録()
    {
        $rule = Straw::alpha('test');
        $this->assertNotNull($rule('abcde'));
        $this->assertNull($rule('abcd5'));
    }

    /**
     * @covers Nnssn\Straw\Straw::num
     * @test
     */
    public function 数字ルールの登録()
    {
        $rule = Straw::num('test');
        $this->assertNotNull($rule('12345'));
        $this->assertNull($rule('1234e'));
        $this->assertNull($rule('12-34'));
    }

    /**
     * @covers Nnssn\Straw\Straw::alphanum
     * @test
     */
    public function 英数字ルールの登録()
    {
        $rule = Straw::alphanum('test');
        $this->assertNotNull($rule('a1b2c3'));
        $this->assertNull($rule('a1--b2'));
    }

    /**
     * @covers Nnssn\Straw\Straw::alphaList
     * @test
     */
    public function 英字リストルールの登録()
    {
        $rule = Straw::alphaList('test');
        $this->assertNotNull($rule('abc'));
        $this->assertNotNull($rule('abc,def,ghi'));
        $this->assertNull($rule('abc,def,123'));
    }

    /**
     * @covers Nnssn\Straw\Straw::numList
     * @test
     */
    public function 数字リストルールの登録()
    {
        $rule = Straw::numList('test');
        $this->assertNotNull($rule('123'));
        $this->assertNotNull($rule('123,456,789'));
        $this->assertNull($rule('123,456,abc'));
    }

    /**
     * @covers Nnssn\Straw\Straw::alphanumList
     * @test
     */
    public function 英数字リストルールの登録()
    {
        $rule = Straw::alphanumList('test');
        $this->assertNotNull($rule('abc_123'));
        $this->assertNotNull($rule('123,abc,456def'));
        $this->assertNull($rule('123,abc,あいうえお'));
    }

    /**
     * @test
     */
    public function セットルールの値に重複を含めデータの取得に失敗する()
    {
        $rule = Straw::alphaSet('test');
        $this->assertNull($rule('abc;def;def'));

        $rule = Straw::numSet('test');
        $this->assertNull($rule('123;123'));
    }

    /**
     * @covers Nnssn\Straw\Straw::alphaSet
     * @test
     */
    public function 英字セットルールの登録()
    {
        $rule = Straw::alphaSet('test');
        $this->assertNotNull($rule('abc'));
        $this->assertNotNull($rule('hoge;fuga;piyo'));
        $this->assertNull($rule('hoge,fuga,piyo'));
    }

    /**
     * @covers Nnssn\Straw\Straw::numSet
     * @test
     */
    public function 数字セットルールの登録()
    {
        $rule = Straw::numSet('test');
        $this->assertNotNull($rule('123'));
        $this->assertNotNull($rule('123;456;789'));
        $this->assertNull($rule('123;456,789'));
    }

    /**
     * @covers Nnssn\Straw\Straw::alphanumSet
     * @test
     */
    public function 英数字セットルールの登録()
    {
        $rule = Straw::alphanumSet('test');
        $this->assertNotNull($rule('a1'));
        $this->assertNotNull($rule('123;abc;456def'));
        $this->assertNull($rule('123;abc;/%&'));
    }

    /**
     * @covers Nnssn\Straw\Straw::numRange
     * @test
     */
    public function 数字範囲ルールの登録()
    {
        $rule = Straw::numRange('test');
        $this->assertNotNull($rule('123-456'));
        $this->assertNull($rule('123-'));
        $this->assertNull($rule('-'));
        $this->assertNull($rule('10-9')); //a > b

        $rule = Straw::numRange('test', '0-1000');
        $this->assertNotNull($rule('123-'));
        $this->assertNotNull($rule('-456'));
    }

    /**
     * @covers Nnssn\Straw\Straw::datetimeRange
     * @expectedException \RuntimeException
     * @test
     */
    public function 日時範囲ルールの登録()
    {
        $rule = Straw::datetimeRange('test');
        $this->assertNotNull($rule('20160101-20160115'));
        $this->assertNull($rule('20160101a-20160115b'));
        $this->assertNull($rule('-'));
        $this->assertNull($rule('20160115-20160101')); //a > b

        $rule = Straw::datetimeRange('test', null, 'y.m.d');
        $this->assertNotNull($rule('16.01.01-16.01.15'));
        $this->assertNull($rule('160101-160115'));

        $rule = Straw::datetimeRange('delimiter_hyphen', null, 'y-m-d');
    }

    /**
     * @covers Nnssn\Straw\Straw::num
     * @covers Nnssn\Straw\Straw::numList
     * @covers Nnssn\Straw\Straw::numSet
     * @covers Nnssn\Straw\Straw::numRange
     * @test
     */
    public function 数字系ルールの値範囲を制限()
    {
        $num       = Straw::num('num', null, array(0, 100));
        $num_list  = Straw::numList('list', null, array(0, 100));
        $num_set   = Straw::numSet('set', null, array(1, 100));
        $num_range = Straw::numRange('range', null, array(1, 100));

        $this->assertNotNull($num(10));
        $this->assertNull($num(110));

        $this->assertNotNull($num_list('0,99,100'));
        $this->assertNull($num_list('0,100,101'));

        $this->assertNotNull($num_set('1;99;100'));
        $this->assertNull($num_set('1;99;101'));
        $this->assertNull($num_set('1;1'));

        $this->assertNotNull($num_range('1-100'));
        $this->assertNull($num_range('50-110'));
    }

    /**
     * @covers Nnssn\Straw\Straw::bool
     * @test
     */
    public function boolルールの登録()
    {
        $rule = Straw::bool('test', null);
        $this->assertNotNull($rule(0));
        $this->assertNotNull($rule(1));
        $this->assertNull($rule(10));
        $this->assertNull($rule('abc'));
    }

    /**
     * @covers Nnssn\Straw\Straw::repeat
     * @test
     */
    public function 第3引数で文字列の長さを制限()
    {
        $alpha_8     = Straw::alphanum('test1', null, 8);
        $alpha_8to   = Straw::alphanum('test2', null, array(8, null));
        $alpha_8to12 = Straw::alphanum('test3', null, array(8, 12));
        $alpha_to12  = Straw::alphanum('test4', null, array(null, 12));

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
     * @covers Nnssn\Straw\Straw::repeat
     * @test
     */
    public function 第3引数でリストとセットの文字列の長さを制限()
    {
        $list_8    = Straw::alphanumList('list1', null, 8);
        $list_8to  = Straw::alphanumList('list2', null, array(8, null));
        $set_8to12 = Straw::alphanumSet('set1', null, array(8, 12));
        $set_to12  = Straw::alphanumSet('set2', null, array(null, 12));

        $input_single8  = 'a2345678';
        $input_single13 = 'a234567890123';
        $input_list8    = 'a2345678,b2345678';
        $input_list13   = 'a234567890123,a234567890123';
        $input_set8     = 'a2345678;b2345678';
        $input_set13    = 'a234567890123;a234567890123';

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

        //set_8to12
        $this->assertNotNull($set_8to12($input_single8));
        $this->assertNull($set_8to12($input_single13));
        $this->assertNotNull($set_8to12($input_set8));
        $this->assertNull($set_8to12($input_set13));

        //set_to12
        $this->assertNotNull($set_to12($input_single8));
        $this->assertNull($set_to12($input_single13));
        $this->assertNotNull($set_to12($input_set8));
        $this->assertNull($set_to12($input_set13));
    }
}
