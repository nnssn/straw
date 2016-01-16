Straw
====
Straw will make a bridge of query string\(URL parameter\) and output.

## Demo
[demo page](https://github.com/nnssn/straw/blob/master/demo/index.php)

## Requirement
PHP 5.3.*

## Install
```
composer require nnssn/straw:@0.9.*
```

## Usage
### Basic
```
require 'vendor/autoload.php';

use Nnssn\Straw\Straw;

Straw::alpha('type');
Straw::num('page', 1);      //Second parameter is default value.
Straw::alphanum('missing');
Straw::numList('point');
$result = Straw::open()->make();

access http://example.com/?type=all&point=10,20

//result
array
  'type'  => 'all'
  'page'  => 1
  'point' => array
    0 => 10
    1 => 20
```

### Change the output key.
```
Straw::alphanum('s')->to('search');
Straw::num('page')->to('condtions.limit');
Straw::num('min')->to('price.');
Straw::num('max')->to('price.');

//result
array
  'search' => 's_value'
  'conditions' =>
    'limit' => page_value
  'price' =>
    0 => min_value
    1 => max_value
```

### Basic rule
```
Straw::alpha    //a-zA-z
Straw::num      //0-9
Straw::alphanum //a-zA-z0-9
```

### List rule
```
Straw::alphaList    //alpha or alpha,alpha...
Straw::numList      //num or num,num...
Straw::alphanumList //alphanum or alphanum,alphanum...

Straw::numList('values');
access http://example.com/?values=1,2,2
//result
array
  'values' =>
    0 => 1
    1 => 2
    2 => 2
```

### Set rule
```
Straw::alphaSet    //alpha or alpha;alpha...
Straw::numSet      //num or num;num...
Straw::alphanumSet //alphanum or alphanum;alphanum...

Straw::numSet('values');
access http://example.com/?values=1;2;3
//result
array
  'values' =>
    0 => 1
    1 => 2
    2 => 3

//Set rule is not allow duplicate values.
access http://example.com/?values=1;2;2
//result
null
```

### Range rule
```
Straw::numRange      //1-10
Straw::datetimeRange //20160101-20160116

Straw::numRange('yes');
Straw::numRange('no');
access http://example.com/?yes=1-10&no=10-1
//result
array
  'yes' =>
    0 => 1
    1 => 10
```

## Licence

[MIT](http://opensource.org/licenses/mit-license.php)
