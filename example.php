<?php
require "src/GoogleTranslate.php";
use GoogleTranslate\GoogleTranslate;

$text = "Apa kabar?";
$from = "id";
$to   = "en";

$st = new GoogleTranslate($text, $from, $to);
$result = $st->exec();

echo $result;
echo "\n";