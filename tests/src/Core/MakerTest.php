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

}
