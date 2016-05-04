<?php

namespace Drupal\Tests\file_link\Kernel;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\KernelTestBase;

/**
 * Provides kernel tests for 'file_link' field type.
 *
 * @group file_link
 */
class FileLinkTest extends KernelTestBase {

  /**
   * Tests file_link field metadata storage.
   */
  public function testMetadata() {
    $this->enableModules([
      'file_link',
      'file_link_test',
      'entity_test',
      'link',
      'field',
      'user',
      'system',
    ]);
    $this->installConfig(['file_link_test']);
    $this->installEntitySchema('entity_test');

    /** @var \Drupal\entity_test\Entity\EntityTest $entity */
    $entity = EntityTest::create(['name' => 'Foo', 'type' => 'article']);

    $entity->set('doc', ['uri' => static::getFullUrl('')]);
    $violations = $entity->get('doc')->validate();
    $this->assertSame(static::getViolationMessage(''), (string) $violations->get(0)->getMessage());

    $entity->set('doc', ['uri' => static::getFullUrl('/')]);
    $violations = $entity->get('doc')->validate();
    $this->assertSame(static::getViolationMessage('/'), (string) $violations->get(0)->getMessage());

    $entity->set('doc', ['uri' => static::getFullUrl('/foo')]);
    $violations = $entity->get('doc')->validate();
    $this->assertSame(static::getViolationMessage('/foo'), (string) $violations->get(0)->getMessage());

    $entity->set('doc', ['uri' => static::getFullUrl('/foo.pdf')]);
    $violations = $entity->get('doc')->validate();
    $this->assertSame(static::getViolationMessage('/foo.pdf'), (string) $violations->get(0)->getMessage());

    $entity->set('doc', ['uri' => static::getFullUrl('/foo.txt')]);
    $violations = $entity->get('doc')->validate();
    $this->assertSame(0, $violations->count());
  }

  /**
   * Provides a full URL given a path relative to file_link_test module.
   *
   * @param string $path
   *   A path relative to file_link_test module.
   *
   * @return string
   *   An absolute URL.
   */
  protected static function getFullUrl($path) {
    return Url::fromUri('base:/' . drupal_get_path('module', 'file_link_test') . $path, ['absolute' => TRUE])->toString();
  }

  /**
   * Provides the violation message for the URl returned by ::getFullUrl().
   *
   * @param string $path
   *   A path relative to file_link_test module.
   *
   * @return string
   *   The translated violation message.
   */
  protected static function getViolationMessage($path) {
    return (new TranslatableMarkup("The path '@uri' doesn't point to a file or the file requires an extension.", ['@uri' => static::getFullUrl($path)]))->__toString();
  }

}
