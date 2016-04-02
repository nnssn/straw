<?php

namespace Straw;

class RegexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Straw\Rule\Regex
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new \Straw\Rule\Regex();
    }

    protected function reflectionMethod($method)
    {
        $ref    = new \ReflectionClass($this->object);
        $method = $ref->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers \Straw\Rule\Regex::makeRepeatPattern
     * @test
     */
    public function makeRepeatPattern()
    {
        $method = $this->reflectionMethod('makeRepeatPattern');
        $this->assertEquals(array(1, ''),  $method->invokeArgs($this->object, array(null)));
        $this->assertEquals(array(10, 10), $method->invokeArgs($this->object, array(10)));
        $this->assertEquals(array(1, 10),  $method->invokeArgs($this->object, array(array(1, 10))));
    }

    /**
     * @covers \Straw\Rule\Regex::makePart
     * @test
     */
    public function makePart()
    {
        $method = $this->reflectionMethod('makePart');
        $input  = array(
            'alpha' => 'alpha', 'alnum' => 'al2', 'number' => 1,
            'original' => 'original', 'original_list' => 'value,value'
        );
        $s = Straw::open($input);

        $alpha = $s->alpha('alpha');
        $this->assertEquals('[a-zA-Z_]{1,}',  $method->invokeArgs($this->object, array($alpha)));

        $alnum = $s->alnum('alnum');
        $this->assertEquals('[a-zA-Z0-9_]{1,}',  $method->invokeArgs($this->object, array($alnum)));

        $number = $s->number('number');
        $this->assertEquals('[0-9]{1,}',  $method->invokeArgs($this->object, array($number)));

        $original_normal = $s->original('original', null, 'abc-_');
        $this->assertEquals('abc-_',  $method->invokeArgs($this->object, array($original_normal)));

        $original_list   = $s->originalList('original_list', null, 'abc-_');
        $this->assertEquals('[abc-_]{1,}',  $method->invokeArgs($this->object, array($original_list)));
    }
}
