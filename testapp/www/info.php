<?php

phpinfo();

$processUser = posix_getpwuid(posix_geteuid());
$processUser2 = posix_getpwuid(posix_getuid());
echo "<!-- user: ". $processUser['name']." - ". $processUser2['name']."-->\n";
