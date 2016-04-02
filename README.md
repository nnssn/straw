Straw
====
Straw will make a bridge of query string\(URL parameter\) and output.

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

use Straw\Straw;

$s = Straw::open();
$s->alpha('type');
$s->num('page', 1);      //Second parameter is default value.
$s->alphanum('missing');
$s->numList('point');
$result = $s->make();

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
$s->alphanum('s')->to('search');
$s->num('page')->to('condtions.limit');
$s->num('min')->to('price.');
$s->num('max')->to('price.');

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
$s->alpha    //a-zA-z_
$s->num      //0-9
$s->alphanum //0-9a-zA-z_
```

### List rule
```
$s->alphaList    //alpha or alpha,alpha...
$s->numList      //num or num,num...
$s->alphanumList //alphanum or alphanum,alphanum...

$s->numList('values');
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
$s->alphaSet    //alpha or alpha;alpha...
$s->numSet      //num or num;num...
$s->alphanumSet //alphanum or alphanum;alphanum...

$s->numSet('values');
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
$s->numRange      //1-10
$s->datetimeRange //20160101-20160116

$s->numRange('yes');
$s->numRange('no');
access http://example.com/?yes=1-10&no=10-1
//result
array
  'yes' =>
    0 => 1
    1 => 10
```

## Licence

[MIT](http://opensource.org/licenses/mit-license.php)
