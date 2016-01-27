<?php

namespace Nnssn\Straw\Core;

require_once __DIR__ . '/../TestManual.php';

class MakerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Maker
     */
    protected $object;

    protected function setUp()
    {
        $test_manual  = new \TestManual;
        $this->object = new Maker($test_manual);
    }

    /**
     * @covers Nnssn\Straw\Core\Maker::__construct
     * @covers Nnssn\Straw\Core\Maker::readManual
     * @test
     */
    public function コンストラクタでマニュアルをセット()
    {
        $manual = new \TestManual;
        $maker  = new Maker($manual);
    }

    /**
     * @covers Nnssn\Straw\Core\Maker::complate
     * @test
     */
    public function complateメソッドでデータを編集()
    {
        $this->object->complate(function ($data) {
            $data['add_key'] = 'add_value';
            return $data;
        });
        $result = $this->object->make();
        $this->assertArrayHasKey('add_key', $result);
    }

    /**
     * @covers Nnssn\Straw\Core\Maker::make
     * @test
     */
    public function 結果を取得()
    {
        $result = $this->object->make();
        $this->assertArrayHasKey('options', $result);
        $this->assertArrayNotHasKey('sort', $result['options']);
    }

    /**
     * @covers Nnssn\Straw\Core\Maker::source
     * @covers Nnssn\Straw\Core\Maker::getInputValue
     * @test
     */
    public function ソースを変更()
    {
        $source = array('test' => 'test');
        $maker = new Maker;
        $maker->source($source);
        $ref = new \ReflectionClass($maker);

        $property = $ref->getProperty('source');
        $property->setAccessible(true);
        $set_source = $property->getValue($maker);
        $this->assertEquals($source, $set_source);

        $method = $ref->getMethod('getInputValue');
        $method->setAccessible(true);
        $set_value = $method->invokeArgs($maker, array('test'));
        $this->assertEquals('test', $set_value);
    }

    /**
     * @covers Nnssn\Straw\Core\Maker::getComplate
     * @test
     */
    public function complateコールバックの取得()
    {
        $ref = new \ReflectionClass($this->object);
        $method = $ref->getMethod('getComplate');
        $method->setAccessible(true);
        $callback = $method->invoke($this->object);
        $this->assertInternalType('callable', $callback);

        $maker = new Maker;
        $maker->complate(function ($data) {
            return $data;
        });
        $ref = new \ReflectionClass($maker);

        $method = $ref->getMethod('getComplate');
        $method->setAccessible(true);
        $callback = $method->invoke($maker);
        $this->assertInternalType('callable', $callback);
    }

    /**
     * @covers Nnssn\Straw\Core\Maker::build
     * @test
     */
    public function 結果配列の組み立て()
    {
        $datum = array(
            array(
                'key' => 'normal',
                'value' => 'value',
            ),
            array(
                'key' => 'array_put.',
                'value' => 'value',
            ),
            array(
                'key' => 'array.in',
                'value' => 'value',
            ),
        );
        $maker = new Maker;
        $ref = new \ReflectionClass($maker);

        $method = $ref->getMethod('build');
        $method->setAccessible(true);
        $result = $method->invokeArgs($maker, array($datum));
        $this->assertArrayHasKey('normal', $result);
        $this->assertArrayHasKey('array_put', $result);
        $this->assertArrayHasKey('in', $result['array']);
    }
}
