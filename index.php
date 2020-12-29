<?php

/**
 * I am running PHP 8.0 - PHPUnit only supports PHP 7.4 >
 * This is how I ran my tests: I can't run them any other way so please
 * provide feedback if any major tests fail.
 */

require 'WallysWidgetsCalculator.php';

$widgetsRequired = 2001;

$packs = (new WallysWidgetsCalculator)
        ->getPacks($widgetsRequired, array(250, 500, 1000, 2000, 1999, 5000));

$cost = 0;

foreach($packs as $pack => $quantity):
    $cost += $pack * $quantity;
endforeach;

echo "Widgets Required: {$widgetsRequired} <br /> <br />";

var_dump($packs);

$extra = $cost - $widgetsRequired;

echo "<br /> <br />Total: {$cost} - you will recieve an extra {$extra} widgets due to buying in our packs.";