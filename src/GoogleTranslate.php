<?php

namespace GoogleTranslate;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @version 0.0.1
 */
final class GoogleTranslate
{
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
	private $result;

	/**
	 * @var string
	 */
	private $cookiefile;

	/**
	 * Constructor.
	 *
	 * @param string $text
	 * @param string $from
	 * @param string $to
	 */
	public function __construct($text, $from, $to)
	{
		$this->text = $text;
		$this->from = $from;
		$this->to   = $to;
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
			if (! is_dir(data."/google_translate_data")) {
				throw new \Exception("Cannot create directory!");
			}
			$this->cookiefile = realpath(data."/google_translate_data")."/cookiefile";
		} else {
			is_dir("google_translate_data") or mkdir("google_translate_data");
			if (!is_dir("google_translate_data")) {
				throw new \Exception("Cannot create directory!");
			}
			$this->cookiefile = realpath("google_translate_data")."/cookiefile";
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

	private function translate()
	{
		$ch = curl_init("https://translate.google.com/m?hl=en&sl={$this->from}&tl={$this->to}&ie=UTF-8&prev=_m&q=".urlencode($this->text));
		curl_setopt_array($ch, 
			[
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_COOKIEFILE => $this->cookiefile,
				CURLOPT_COOKIEJAR => $this->cookiefile,
			]
		);
		$out = curl_exec($ch);
		$no = curl_errno($ch) and $out = "Error (".$no.") ".curl_error($ch);
		curl_close($ch);
		return $out;
	}

	/**
	 * Run translate and get result.
	 *
	 * @return string
	 */
	public function exec()
	{
		file_put_contents("a.tmp", $this->translate());
	}
}
