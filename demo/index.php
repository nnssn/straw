<?php

require_once '../vendor/autoload.php';
require_once './functions.php';

use Straw\Straw;

$s = Straw::open();

//normal
$s->alpha('a');
$s->alnum('an');
$s->number('n');

//list
$s->alphaList('al');
$s->alnumList('anl');
$s->numberList('nl');

//pair
$s->alphaPair('ap');
$s->alnumPair('anp');
$s->numberPair('np');

//range
$s->numberRange('nr');
$s->numberRange('nr2');
$s->datetimeRange('dr');

//set&enum
$s->set('set', null, array('set1', 'set2', 'set3',));
$s->enum('enum', null, array('e1', 'e2', 'e3',));

//change key
$s->alnum('before')->to('after');
$s->alnum('top')->to('array.inner');
$s->number('val1')->to('values.');
$s->number('val2')->to('values.');

//mix
$s->alphaList(array('a1', 'a2'));
$s->numberPair(array('min', 'max'))->to('min_to_max');
$s->numberList('arr');

//filter
$s->alnum('name')
    ->to('username')
    ->format(function ($value) {
        return '%' . $value . '%';
    });

$res = $s->make();

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Straw Demo</title>
		<meta charset="UTF-8">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	</head>
    <body class="container">
        <div class="row">
            <div class="col-sm-6">
                <h2>link1</h2>
                <ul>
                    <li><a href="?a=abc&an=abc123&n=123">normal</a></li>
                    <li><a href="?al=abc,def&anl=abc123,def456&nl=123,456">list</a></li>
                    <li><a href="?ap=abc:def:many&anp=abc123:def456&np=123:456">pair</a></li>
                    <li><a href="?dr=20160320-20160331&nr=50-100&nr2=50-49">range</a></li>
                    <li><a href="?set=set1;set3&enum=e2">set&enum</a></li>
                </ul>
            </div>
            <div class="col-sm-6">
                <h2>link2</h2>
                <ul>
                    <li><a href="?before=change&top=in&val1=10&val2=20">changeKey</a></li>
                    <li><a href="?a1=abc&a2=def&min=10&max=20&arr[]=123&arr[]=456">mix</a></li>
                    <li><a href="?name=git">format</a></li>
                </ul>
            </div>
            <hr>

            <div class="col-sm-12">
                <h2>input</h2>
                <p><?php echo currentUri(); ?></p>
                <pre><?php var_dump(queryString()); ?></pre>
                <hr>
            </div>

            <div class="col-sm-12">
                <h2>output</h2>
                <pre><?php var_dump($res); ?></pre>
            </div>
        </div>
	</body>
</html>
