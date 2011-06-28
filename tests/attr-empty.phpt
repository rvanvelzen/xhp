--TEST--
No attribute value
--FILE--
<?php
class xhp_a {}
$foo = <a b="cheese" c d="onion" />;
echo "pass";
--EXPECT--
pass
