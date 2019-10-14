<?php
$str = "";
for ($i=1; $i <= 100; $i++) { 
    
    $str .= ($i % 3 == 0 && $i % 5 == 0 ? "foobar," : ($i % 3 == 0 ? "foo," : ($i % 5 == 0 ? "bar," :  $i.",")));
}

$str = trim($str, ',');

echo $str."\n";