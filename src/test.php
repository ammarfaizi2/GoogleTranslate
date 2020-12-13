<?php

$t = file_get_contents("text.txt");

echo "| Code        | Description |\n";
echo "| ----------- | ----------- |\n";
if (preg_match_all("/<a href=\"\.\/m\?sl\=(..).+?>(.+?)</", $t, $m)) {
  unset($m[1][0], $m[1][1]);
  foreach ($m[1] as $k => $v) {
    echo " | {$v} | {$m[2][$k]} |\n";
  }
}