<?php

require_once '../vendor/autoload.php';
require_once './functions.php';

use Nnssn\Straw\Straw;

Straw::alpha('al');
Straw::alphaList('allist');
Straw::alphaSet('alset');

Straw::num('num');
Straw::numList('numlist');
Straw::numSet('numset');
Straw::numSet('numset_duplicate');
Straw::numRange('numrange');

Straw::alphanum('before')->to('after');
Straw::alphanum('top')->to('array.inner');
Straw::num('val1')->to('values.');
Straw::num('val2')->to('values.');

Straw::alphaList(array('a1', 'a2'));
Straw::numPair(array('num1', 'num2'));

Straw::alphanum('name')
        ->to('username')
        ->format(function ($value) {
            return '%' . $value . '%';
        });

$res = Straw::open()->make();

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Straw Demo</title>
		<meta charset="UTF-8">
	</head>
	<body>
        <ul>
            <li><a href="?al=a&allist=bc,de&alset=f;g">alpha</a></li>
            <li><a href="?num=1&numlist=2,3&numset=4;5&numset_duplicate=1;1&numrange=6-9">nums</a></li>
            <li><a href="?before=change&top=in&val1=10&val2=20">changeKey</a></li>
            <li><a href="?a1=abc&a2=def&num1=10&num2=20">mix</a></li>
            <li><a href="?name=git">format</a></li>
        </ul>
        <hr>

        <div>
            <h2>input</h2>
            <p><?php echo currentUri(); ?></p>
            <pre><?php var_dump(queryString()); ?></pre>
            <hr>
        </div>

        <div>
            <h2>output</h2>
            <pre><?php var_dump($res); ?></pre>
        </div>
	</body>
</html>
