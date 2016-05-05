<?php

namespace Drupal\file_link\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ExecutionContextInterface;

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
   * @var \Symfony\Component\Validator\ExecutionContextInterface
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
    if (!isset($link)) {
      return;
    }

    $is_valid = TRUE;

    // Try to resolve the given URI to a URL. It may fail if it's schemeless.
    try {
      $url = $link->getUrl();
    }
    catch (\InvalidArgumentException $e) {
      $is_valid = FALSE;
    }

    $uri = $url->toString();
    if ($is_valid) {
      $needs_extension = !$link->getFieldDefinition()->getSetting('no_extension');
      $path = trim((string) parse_url($uri, PHP_URL_PATH), '/');
      if (empty($path)) {
        if ($needs_extension) {
          $is_valid = FALSE;
        }
        else {
          // No path and the field accepts URLs without extension. We're done.
          return;
        }
      }

      if ($is_valid) {
        $name = \Drupal::service('file_system')->basename($path);
        $has_extension = !empty(pathinfo($path, PATHINFO_EXTENSION));
        if (empty($name) || ($needs_extension && !$has_extension)) {
          $is_valid = FALSE;
        }
        if ($is_valid && $has_extension) {
          $extensions = trim($link->getFieldDefinition()
            ->getSetting('file_extensions'));
          if (!empty($extensions)) {
            $regex = '/\.(' . preg_replace('/ +/', '|', preg_quote($extensions)) . ')$/i';
            if (!preg_match($regex, $name)) {
              $is_valid = FALSE;
            }
          }
        }
      }
    }
    if (!$is_valid) {
      $this->context->addViolation($this->message, ['@uri' => $uri]);
    }
  }

}
