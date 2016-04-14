<?php

namespace Drupal\file_link;

/**
 * Provides an interface for File Link field.
 */
interface FileLinkInterface {

  /**
   * Sets the last HTTP response code.
   *
   * @param string $code
   *   The HTTP response code to be stored.
   */
  public function setLastHttpResponseCode($code);

  /**
   * Gets the last HTTP response code.
   *
   * @return string
   *   The last HTTP response code.
   */
  public function getLastHttpResponseCode();

}
