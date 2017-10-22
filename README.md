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
Berikut ini adalah code bahasa yang tersedia :

af = Afrikaans<br>sq = Albanian<br>am = Amharic<br>ar = Arabic<br>hy = Armenian<br>az = Azerbaijani<br>eu = Basque<br>be = Belarusian<br>bn = Bengali<br>bs = Bosnian<br>bg = Bulgarian<br>ca = Catalan<br>ceb = Cebuano<br>ny = Chichewa<br>zh-CN = Chinese (Simplified)<br>zh-TW = Chinese (Traditional)<br>co = Corsican<br>hr = Croatian<br>cs = Czech<br>da = Danish<br>nl = Dutch<br>en = English<br>eo = Esperanto<br>et = Estonian<br>tl = Filipino<br>fi = Finnish<br>fr = French<br>fy = Frisian<br>gl = Galician<br>ka = Georgian<br>de = German<br>el = Greek<br>gu = Gujarati<br>ht = Haitian Creole<br>ha = Hausa<br>haw = Hawaiian<br>iw = Hebrew<br>hi = Hindi<br>hmn = Hmong<br>hu = Hungarian<br>is = Icelandic<br>ig = Igbo<br>id = Indonesian<br>ga = Irish<br>it = Italian<br>ja = Japanese<br>jw = Javanese<br>kn = Kannada<br>kk = Kazakh<br>km = Khmer<br>ko = Korean<br>ku = Kurdish (Kurmanji)<br>ky = Kyrgyz<br>lo = Lao<br>la = Latin<br>lv = Latvian<br>lt = Lithuanian<br>lb = Luxembourgish<br>mk = Macedonian<br>mg = Malagasy<br>ms = Malay<br>ml = Malayalam<br>mt = Maltese<br>mi = Maori<br>mr = Marathi<br>mn = Mongolian<br>my = Myanmar (Burmese)<br>ne = Nepali<br>no = Norwegian<br>ps = Pashto<br>fa = Persian<br>pl = Polish<br>pt = Portuguese<br>pa = Punjabi<br>ro = Romanian<br>ru = Russian<br>sm = Samoan<br>gd = Scots Gaelic<br>sr = Serbian<br>st = Sesotho<br>sn = Shona<br>sd = Sindhi<br>si = Sinhala<br>sk = Slovak<br>sl = Slovenian<br>so = Somali<br>es = Spanish<br>su = Sundanese<br>sw = Swahili<br>sv = Swedish<br>tg = Tajik<br>ta = Tamil<br>te = Telugu<br>th = Thai<br>tr = Turkish<br>uk = Ukrainian<br>ur = Urdu<br>uz = Uzbek<br>vi = Vietnamese<br>cy = Welsh<br>xh = Xhosa<br>yi = Yiddish<br>yo = Yoruba<br>zu = Zulu<br>