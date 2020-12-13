<?php

declare(strict_types=1);

namespace GoogleTranslate {

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @version 1.0.1
 */

use Exception;

class GoogleTraslateException extends Exception
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
   * @param string $text
   * @param string $from
   * @param string $to
   * @throws \GoogleTranslate\GoogleTraslateException
   *
   * Constructor.
   */
  public function __construct(string $text, string $from, string $to)
  {

    $from = strtolower(trim($from));
    $to   = strtolower(trim($to));

    if (!isset(self::LANG_LIST[$from])) {
      throw new GoogleTraslateException("Invalid `from` language: {$from}");
    }

    if (!isset(self::LANG_LIST[$to])) {
      throw new GoogleTraslateException("Invalid `to` language: {$to}");
    }

    $text = strtolower(trim($text));
    $hash = sha1($text);


    $this->text = $text;
    $this->from = $from;
    $this->to   = $to;
  }
};

} // namespace GoogleTranslte
