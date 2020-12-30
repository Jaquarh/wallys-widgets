<?php

/**
 * I am running PHP 8.0 - PHPUnit only supports PHP 7.4 >
 * This is how I ran my tests: I can't run them any other way so please
 * provide feedback if any major tests fail.
 */

require 'WallysWidgetsCalculator.php';

var_dump((new WallysWidgetsCalculator)
        ->getPacks(8, [5, 2, 3, 1]));