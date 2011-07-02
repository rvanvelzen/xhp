--TEST--
JSON style array separators
--FILE--
<?php
$arr = ['a': 1, "b": 2, 3, 1 => true];
echo $arr['b'] . $arr[0];
--EXPECT--
23
