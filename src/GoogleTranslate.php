<?php

namespace GoogleTranslate;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @version 0.0.1
 */
final class GoogleTranslate
{

	const VERSION = "0.0.1";

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
	private $hash;

	/**
	 * @var string
	 */
	private $result;

	/**
	 * @var string
	 */
	private $cookiefile;

	/**
	 * @var string
	 */
	private $dataDir;

	/**
	 * @var bool
	 */
	private $isError = false;

	/**
	 * @var bool
	 */
	private $noRomanji = false;

	/**
	 * @var bool
	 */
	private $isResultGetFromCache = false;

	/**
	 * Constructor.
	 *
	 * @param string $text
	 * @param string $from
	 * @param string $to
	 */
	public function __construct($text, $from, $to)
	{
		$this->text = strtolower($text);
		$this->from = strtolower($from);
		$this->to   = $to;
		$this->hash = sha1($this->text.$this->from.$this->to);
		$this->__init__();
	}

	/**
	 * Init google translate cookie.
	 */
	private function __init__()
	{
		if (defined("data")) {
			is_dir(data) or mkdir(data);
			is_dir(data."/google_translate_data") or mkdir(data."/google_translate_data");
			is_dir(data."/google_translate_data/cache") or mkdir(data."/google_translate_data/cache");
			if (
				! is_dir(data."/google_translate_data") ||
				! is_dir(data."/google_translate_data/cache")
			) {
				throw new \Exception("Cannot create directory!");
			}
			$this->cookiefile = ($this->dataDir = realpath(data."/google_translate_data"))."/cookiefile";
		} else {
			is_dir("google_translate_data") or mkdir("google_translate_data");
			is_dir("google_translate_data/cache") or mkdir("google_translate_data/cache");
			if (
				! is_dir("google_translate_data")  ||
				! is_dir("google_translate_data/cache")
			) {
				throw new \Exception("Cannot create directory!");
			}
			$this->cookiefile = ($this->dataDir = realpath("google_translate_data"))."/cookiefile";
		}
		if (! file_exists($this->cookiefile)) {
			$handle = fopen($this->cookiefile, "w");
			fwrite($handle, "");
			fclose($handle);
			if (! file_exists($this->cookiefile)) {
				throw new \Exception("Cannot create cookie file!");
			}
		}
	}

	/**
	 * Translate.
	 */
	private function translate()
	{
		if ($this->isCached() && $this->isPerfectCache()) {
			$this->isResultGetFromCache = true;
			return $this->getCache();
		} else {
			$ch = curl_init("https://translate.google.com/m?hl=en&sl={$this->from}&tl={$this->to}&ie=UTF-8&prev=_m&q=".urlencode($this->text));
			curl_setopt_array($ch, 
				[
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_CONNECTTIMEOUT => 30,
					CURLOPT_HTTPHEADER => [
						"Host: translate.google.com",
						"User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:56.0) Gecko/20100101 Firefox/56.0",
						"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
						"Accept-Language: en-US,en;q=0.5"
					],
					CURLOPT_COOKIEFILE => $this->cookiefile,
					CURLOPT_COOKIEJAR => $this->cookiefile,
					CURLOPT_REFERER => "https://translate.google.com/m",
					CURLOPT_TIMEOUT	=> 30
				]
			);
			$out = curl_exec($ch);
			$no = curl_errno($ch) and $out = "Error (".$no.") ".curl_error($ch) and $this->isError = true;
			curl_close($ch);
			file_put_contents("a.tmp", $out);
			return $out;
		}
	}

	/**
	 * Give result with no romanji.
	 */
	public function noRomanji()
	{
		$this->noRomanji = true;
	}

	/**
	 * Parse result.
	 */
	private static function parseResult($result)
	{
		$_result = "";
		$segment = explode("<div dir=\"ltr\" class=\"t0\">", $result, 2);
		if (isset($segment[1])) {
			$segment = explode("<", $segment[1], 2);
			$_result.= html_entity_decode($segment[0], ENT_QUOTES, 'UTF-8');
		} else {
			return "Error while parsing data!";
		}
		if (! $this->noRomanji) {
			$segment = explode("<div dir=\"ltr\" class=\"o1\">", $result, 2);
			if (count($segment) > 1) {
				$segment = explode("<", $segment[1], 2);
				$_result.= "\n(".html_entity_decode($segment[0], ENT_QUOTES, 2).")";
			}
		}
		$this->result = $_result xor $this->cacheControl();
		return $_result;
	}

	/**
	 * Cache control
	 */
	private function cacheControl()
	{

	}

	/**
	 * Run translate and get result.
	 *
	 * @return string
	 */
	public function exec()
	{	
		$out = $this->translate();
		return 
			$this->isError ? $out : (
				$this->isResultGetFromCache ? 
					$out : self::parseResult($out));
	}
}
