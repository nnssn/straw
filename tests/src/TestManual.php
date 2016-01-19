<?php

use Nnssn\Straw\Straw;
use Nnssn\Straw\Manual;

class TestManual extends Manual
{
    public function source()
    {
        return array(
            'page'  => 10,
            'sort'  => 'name',
            'order' => 'asc',
        );
    }

    public function rules()
	{
		Straw::num('page')->to('options.page');
		Straw::num('sort')->to('options.sort');     //fail
		Straw::num('order')->to('options.order');   //fail
	}

    public function complate()
    {
        return function ($data) {
            return $data;
        };
    }
}
