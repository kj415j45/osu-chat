<?php

$in = fopen('php://stdin', 'r');
echo trim(fgets($in, 4096));

?>
