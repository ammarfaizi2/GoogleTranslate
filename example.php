<?php
require "src/GoogleTranslate.php";
use GoogleTranslate\GoogleTranslate;

$text = "Apa kabar?";
$from = "id";
$to   = "ja";

$st = new GoogleTranslate($text, $from, $to);
$result = $st->exec();

echo $result;
echo "\n";