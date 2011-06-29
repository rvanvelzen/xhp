--TEST--
HTML() works
--FILE--
<?php
require dirname(__FILE__) . '/../php-lib/init.php';
echo <p>{HTML('<a></a>')}</p>;
--EXPECT--
<p><a></a></p>
