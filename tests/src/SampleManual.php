<?php

namespace Straw;

use Straw\Straw;
use Straw\Manual;

class SampleManual extends Manual
{
    public function source()
    {
        return array(
            'test' => 0,
            'key1' => 0,
            'key2' => 0,

            'page'  => 10,
            'sort'  => 'name',
            'order' => 'asc',
        );
    }

    public function rules(Straw $s)
	{
        $s->alpha('alpha');
        $s->number('number');
	}

    public function complate()
    {
        return function (array $values) {
            $values['insert'] = 'insert';
            return $values;
        };
    }
}
