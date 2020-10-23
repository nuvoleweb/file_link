<?php

namespace Drupal\file_link;

use Drupal\Core\Language\Language;

/**
 * Data object for a queue item to update a file link data.
 */
final class FileLinkQueueItem {

  /**
   * @var int
   */
  private $time;

  /**
   * @var string
   */
  private $type;

  /**
   * @var int
   */
  private $id;

  /**
   * @var string
   */
  private $lang;

  public function __construct(string $type, int $id, string $lang = Language::LANGCODE_NOT_SPECIFIED, int $time = NULL) {
    $this->type = $type;
    $this->id = $id;
    $this->lang = $lang;
    if ($time === NULL) {
      $time = \Drupal::time()->getRequestTime();
    }
    $this->time = $time;
  }

  /**
   * Get the time of the queue.
   *
   * @return int
   *   The timestamp.
   */
  public function getTime(): int {
    return $this->time;
  }

  /**
   * Get the entity type.
   *
   * @return string
   *   The entity type.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Get the entity id.
   *
   * @return int
   *   The entity id.
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * Get the entity language.
   *
   * @return string
   *   The entity language
   */
  public function getLang(): string {
    return $this->lang;
  }

  /**
   * Get a key to keep track of queued items.
   *
   * @return string
   *   The key which identifies the queued entity variation.
   */
  public function getKey(): string {
    return $this->getType() . $this->getId() . $this->getLang();
  }

}
