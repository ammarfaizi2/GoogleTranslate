<?php

declare(strict_types=1);

namespace GoogleTranslate {

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 1.0.1
 */

use Exception;

final class GoogleTranslateException extends Exception
{
};

final class GoogleTranslate
{
  /**
   * @const string
   */
  public const VERSION = "1.0.1";

  /**
   * @var string
   */
  private $dataDir;

  /**
   * @var string
   */
  private $text;

  /**
   * @var string
   */
  private $from;

  /**
   * @var string
   */
  private $to;

  /**
   * @var string
   */
  private $cacheFile;

  /**
   * @var string
   */
  private $cookieFile;

  /**
   * @var bool
   */
  private $cacheHit = false;

  /**
   * @var resource
   */
  private $cacheFileHandle = NULL;

  /**
   * @param string $text
   * @param string $from
   * @param string $to
   * @throws \GoogleTranslate\GoogleTranslateException
   *
   * Constructor.
   */
  public function __construct(string $text, string $from, string $to)
  {

    $from = strtolower(trim($from));
    $to   = strtolower(trim($to));

    if (!isset(self::LANG_LIST[$from])) {
      if ($from !== "auto") {
        throw new GoogleTranslateException("Invalid `from` language: {$from}");
      } else {
        $from = "au";
      }
    }

    if ($to === "auto") {
      throw new GoogleTranslateException("`to` language cannot be auto");
    }

    if (!isset(self::LANG_LIST[$to])) {
      throw new GoogleTranslateException("Invalid `to` language: {$to}");
    }

    $text = strtolower(trim($text));
    $hash = sha1($text.$from.$to);
    $this->constructDir($hash);
    $this->cacheHit = $this->cacheCheck();
    $this->text     = $text;
    $this->from     = $from;
    $this->to       = $to;
  }


  /**
   * Destructor.
   */
  public function __destruct()
  {
    if ($this->cacheFileHandle) {
      flock($this->cacheFileHandle, LOCK_UN);
      fclose($this->cacheFileHandle);
    }
  }


  /**
   * @param string $hash
   * @throws \GoogleTranslate\GoogleTranslateException
   * @return void
   */
  private function constructDir(string $hash): void
  {
    if (defined("GOOGLE_TRANSLATE_DATA_DIR")) {
      $this->dataDir = GOOGLE_TRANSLATE_DATA_DIR;
    } else {
      $this->dataDir = getcwd()."/google_translate_data";
    }

    if (!(is_dir($this->dataDir) or mkdir($this->dataDir))) {
      throw new GoogleTranslateException("Cannot create directory: {$this->dataDir}");
    }

    $indexDir         = $this->genIndexDir($hash);
    $this->cacheFile  = "{$indexDir}/{$hash}.dat";
    $this->cookieFile = "{$this->dataDir}/cookie.txt";

    if (!is_writeable($indexDir)) {
      throw new GoogleTranslateException("Index dir is not writeable {$indexDir}");
    }

    if (!is_readable($indexDir)) {
      throw new GoogleTranslateException("Index dir is not readable {$indexDir}");
    }
  }


  /**
   * @param string $hash
   * @throws \GoogleTranslate\GoogleTranslateException
   * @return string
   */
  private function genIndexDir(string $hash): string
  {
    $retVal = $this->dataDir;
    foreach (array_slice(str_split($hash, 2), 0, 5) as $v) {
      $retVal .= "/{$v}";
      if (!(is_dir($retVal) or mkdir($retVal))) {
        throw new GoogleTranslateException("Cannot create directory: {$retVal}");
      }
    }
    return $retVal;
  }


  /**
   * @return bool
   */
  private function cacheCheck(): bool
  { 
    /* Check whether the cache file exists or not. */
    if (!file_exists($this->cacheFile)) {
      return false;
    }

    $handle = fopen($this->cacheFile, "rb");
    flock($handle, LOCK_SH);

    /* Check whether the cache file is accessible or not. */
    if (!$handle) {
      return false;
    }

    $expired = fread($handle, 8);

    /* Check whether the cache file has valid expiry date or not. */
    if (strlen($expired) !== 8) {
      fclose($handle);
      return false;
    }

    $expired = unpack("P", $expired)[1];
    $curTime = time();

    /* Check whether the cache file is expired or not. */
    if ($curTime >= $expired) {
      fclose($handle);
      unlink($this->cacheFile);
      return false;
    }

    $this->cacheFileHandle = $handle;

    /* return true if the cache file is valid and is not expired yet. */
    return true;
  }


  /**
   * @throws \GoogleTranslate\GoogleTranslateException
   * @return ?string
   */
  private function scrapeTranslateData(): string
  {
    $from = $this->from;
    $to   = $this->to;
    $text = urlencode($this->text);
    $ch   = curl_init("https://translate.google.com/m?sl={$from}&tl={$to}&hl=en&q={$text}");
    curl_setopt_array($ch,
      [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_COOKIEFILE     => $this->cookieFile,
        CURLOPT_COOKIEJAR      => $this->cookieFile,
        CURLOPT_USERAGENT      => "Mozilla/5.0 (S60; SymbOS; Opera Mobi/SYB-1103211396; U; es-LA; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 Opera 11.00",
        CURLOPT_REFERER        => "https://translate.google.com/m",
      ]
    );
    $out = curl_exec($ch);
    if (false === $out) {
      $err = curl_error($ch);
      $ern = curl_errno($ch);
      curl_close($ch);
      throw new GoogleTranslateException("Curl error ({$ern}): {$err}");
    }
    curl_close($ch);

    return self::parseOutput($out);
  }


  /**
   * @param string $out
   * @throws \GoogleTranslate\GoogleTranslateException
   * @return ?string
   */
  private static function parseOutput(string $out): string
  {
    $out = explode("<div class=\"result-container\">", $out, 2);

    if (count($out) < 2) {
      $err = "Cannot find div with result-container class";
      goto exception;
    }

    $out = explode("</div>", $out[1], 2);
    if (count($out) < 2) {
      $err = "Cannot find </div> pair in HTML output";
      goto exception;
    }

    return html_entity_decode($out[0], ENT_QUOTES, "UTF-8");

  exception:
    throw new GoogleTranslateException($err);
  }


  /**
   * @return void
   */
  private function writeCache(string $data): void
  {
    $handle = fopen($this->cacheFile, "wb");

    $compressed = gzencode($data, 9);
    $hashData   = sha1($compressed, true);

    flock($handle, LOCK_EX);

    fwrite($handle,
      /* Expired: 2592000 seconds (30 days). */
      pack("P", time() + 2592000)
      .$hashData
      .$compressed
    );

    flock($handle, LOCK_UN);
    fclose($handle);
  }


  /**
   * @return string
   */
  private function fetchData(): string
  {
    $data = $this->scrapeTranslateData();
    $this->writeCache($data);
    return $data;
  }


  /**
   * @return string
   */
  private function loadCache(): string
  {
    if (!$this->cacheFileHandle) {
      /* False cache hit. */
      goto fetch_data;
    }

    $handle = $this->cacheFileHandle;
    $hash   = fread($handle, 20);
    if (strlen($hash) < 20) {
      /* Cache has been corrupted! */
      goto fetch_data;
    }

    $compressed = "";
    while (!feof($handle)) {
      $compressed .= fread($handle, 8096); 
    }

    if (sha1($compressed, true) !== $hash) {
      /* Cache has been corrupted! */
      goto fetch_data;
    }

    $compressed = gzdecode($compressed);
    if (!$compressed) {
      /* Cannot decode cache? */
      goto fetch_data;
    }

    fclose($this->cacheFileHandle);
    $this->cacheFileHandle = NULL;
    return $compressed;

  fetch_data:
    fclose($this->cacheFileHandle);
    $this->cacheFileHandle = NULL;
    return $this->fetchData();
  }


  /**
   * @return string
   */
  public function exec(): string
  {
    return $this->cacheHit ? $this->loadCache() : $this->fetchData();
  }

  /**
   * @const array
   */
  public const LANG_LIST = [
    "en" => true,
    "ja" => true,
    "id" => true,
    "af" => true,
    "sq" => true,
    "am" => true,
    "ar" => true,
    "hy" => true,
    "az" => true,
    "eu" => true,
    "be" => true,
    "bn" => true,
    "bs" => true,
    "bg" => true,
    "ca" => true,
    "ce" => true,
    "ny" => true,
    "zh" => true,
    "co" => true,
    "hr" => true,
    "cs" => true,
    "da" => true,
    "nl" => true,
    "en" => true,
    "eo" => true,
    "et" => true,
    "tl" => true,
    "fi" => true,
    "fr" => true,
    "fy" => true,
    "gl" => true,
    "ka" => true,
    "de" => true,
    "el" => true,
    "gu" => true,
    "ht" => true,
    "ha" => true,
    "ha" => true,
    "iw" => true,
    "hi" => true,
    "hm" => true,
    "hu" => true,
    "is" => true,
    "ig" => true,
    "id" => true,
    "ga" => true,
    "it" => true,
    "ja" => true,
    "jw" => true,
    "kn" => true,
    "kk" => true,
    "km" => true,
    "rw" => true,
    "ko" => true,
    "ku" => true,
    "ky" => true,
    "lo" => true,
    "la" => true,
    "lv" => true,
    "lt" => true,
    "lb" => true,
    "mk" => true,
    "mg" => true,
    "ms" => true,
    "ml" => true,
    "mt" => true,
    "mi" => true,
    "mr" => true,
    "mn" => true,
    "my" => true,
    "ne" => true,
    "no" => true,
    "or" => true,
    "ps" => true,
    "fa" => true,
    "pl" => true,
    "pt" => true,
    "pa" => true,
    "ro" => true,
    "ru" => true,
    "sm" => true,
    "gd" => true,
    "sr" => true,
    "st" => true,
    "sn" => true,
    "sd" => true,
    "si" => true,
    "sk" => true,
    "sl" => true,
    "so" => true,
    "es" => true,
    "su" => true,
    "sw" => true,
    "sv" => true,
    "tg" => true,
    "ta" => true,
    "tt" => true,
    "te" => true,
    "th" => true,
    "tr" => true,
    "tk" => true,
    "uk" => true,
    "ur" => true,
    "ug" => true,
    "uz" => true,
    "vi" => true,
    "cy" => true,
    "xh" => true,
    "yi" => true,
    "yo" => true,
    "zu" => true
  ];
};

} // namespace GoogleTranslte
