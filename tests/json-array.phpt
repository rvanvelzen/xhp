--TEST--
JSON style arrays
--FILE--
<?php
$arr = false ? [1, 2, 3] : [4, 5, 6];
echo $arr[1];
--EXPECT--
5
