<?php
require "src/GoogleTranslate.php";
// require "vendor/autoload.php"; // use composer

use GoogleTranslate\GoogleTranslate;

$text = "Apa kabar kalian semua di sini?";
$from = "auto";
$to   = "en";

$st = new GoogleTranslate($text, $from, $to);
$result = $st->exec();

echo $result; // How are you?
echo "\n";