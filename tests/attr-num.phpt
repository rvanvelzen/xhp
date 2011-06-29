--TEST--
Number attribute value
--FILE--
<?php
class xhp_a {}
$foo = <a b=5.5e10 />;
echo "pass";
--EXPECT--
pass
