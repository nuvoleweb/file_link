<?php

namespace Drupal\file_link\Plugin\Validation\Constraint;

use Drupal\file_link\Plugin\Field\FieldType\FileLinkItem;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Validation constraint for file_link, checking that URI points to a file.
 *
 * @Constraint(
 *   id = "LinkToFile",
 *   label = @Translation("Checks that URI links to a file.", context = "Validation"),
 * )
 */
class LinkToFileConstraint extends Constraint implements ConstraintValidatorInterface {

  public $message = "The path '@uri' doesn't point to a file or the file requires an extension.";

  /**
   * Validation execution context.
   *
   * @var \Symfony\Component\Validator\Context\ExecutionContextInterface
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  public function initialize(ExecutionContextInterface $context) {
    $this->context = $context;
  }

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return get_class($this);
  }

  /**
   * {@inheritdoc}
   */
  public function validate($link, Constraint $constraint) {
    /** @var \Drupal\file_link\Plugin\Field\FieldType\FileLinkItem $link */
    if ($link->isEmpty()) {
      return;
    }

    $is_valid = TRUE;

    // Try to resolve the given URI to a URL. It may fail if it's schemeless.
    try {
      $url = $link->getUrl()->toString();
    }
    catch (\InvalidArgumentException $e) {
      $is_valid = FALSE;
    }

    if ($is_valid) {
      if (!$this->hasPath($url)) {
        if ($this->needsExtension($link)) {
          $is_valid = FALSE;
        }
        else {
          // No path and the field accepts URLs without extension. We're done.
          return;
        }
      }

      if ($is_valid) {
        $name = \Drupal::service('file_system')->basename($this->getPath($url));
        if (empty($name) || ($this->needsExtension($link) && !$this->hasExtension($url))) {
          $is_valid = FALSE;
        }
        if ($is_valid && $this->hasExtension($url)) {
          $is_valid = $this->hasValidExtension($name, $link);
        }
      }
    }

    // If not valid construct error message.
    if (!$is_valid) {
      $this->context->addViolation($this->message, ['@uri' => $link->get('uri')->getValue()]);
    }
  }

  /**
   * Check whereas given URL has a path.
   *
   * @param string $url
   *   URL.
   *
   * @return bool
   *   Whereas given URL has a path.
   */
  protected function hasPath($url) {
    return !empty($this->getPath($url));
  }

  /**
   * Get URL path.
   *
   * @param string $url
   *   URL.
   *
   * @return string
   *   URL path.
   */
  protected function getPath($url) {
    return trim((string) parse_url($url, PHP_URL_PATH), '/');
  }

  /**
   * Check whereas given URL has an extension.
   *
   * @param string $url
   *   URL.
   *
   * @return bool
   *   Whereas given URL has an extension.
   */
  protected function hasExtension($url) {
    return !empty(pathinfo($this->getPath($url), PATHINFO_EXTENSION));
  }

  /**
   * Check whereas given link field needs an extension.
   *
   * @param \Drupal\file_link\Plugin\Field\FieldType\FileLinkItem $link
   *   Link item.
   *
   * @return bool
   *   Whereas link item needs an extension.
   */
  protected function needsExtension(FileLinkItem $link) {
    return !$link->getFieldDefinition()->getSetting('no_extension');
  }

  /**
   * Check whereas basename has a valid extension.
   *
   * @param string $basename
   *   URL path basename.
   * @param \Drupal\file_link\Plugin\Field\FieldType\FileLinkItem $link
   *   Link item.
   *
   * @return bool
   *   Whereas basename has a valid extension.
   */
  protected function hasValidExtension($basename, FileLinkItem $link) {
    $extensions = trim($link->getFieldDefinition()->getSetting('file_extensions'));
    if (!empty($extensions)) {
      $regex = '/\.(' . preg_replace('/ +/', '|', preg_quote($extensions)) . ')$/i';
      return (bool) preg_match($regex, $basename) !== FALSE;
    }
    return TRUE;
  }

  /**
   * Check whereas given response is supported by field type.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   Response object.
   *
   * @return bool
   *   TRUE if supported, FALSE otherwise.
   */
  protected function isSupportedResponse(ResponseInterface $response) {
    return in_array($response->getStatusCode(), [
      '200',
      '301',
      '302',
      '304',
    ]);
  }

}
