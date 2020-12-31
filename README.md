# Wallys Widgets Solution
## By Kyle Jeynes

Since I have PHP 8.0 on my development node, I used my VPS running 7.3.24 to run all PHPUnit tests.
I had to remove the array|bool and string|null notations as well as the data type notations when declaring
class properties.

You may need to make similar altercations before running any PHPUnit tests.

I ended up rebasing the algorithm the solution used, thus the email about a PHPUnit test failing when correct is
no longer necessary.

## Current solution

Passes: 20
Fails: 5
Version: 2.2