# Google Translate

Cara menggunakan :
```php
<?php
require "src/GoogleTranslate.php";
use GoogleTranslate\GoogleTranslate;
// Contoh translate indonesia ke inggris
$text = "Apa kabar?";
$from = "id"; // indonesia
$to   = "en"; // english

$st = new GoogleTranslate($text, $from, $to);
$result = $st->exec();

echo $result; // How are you?
```