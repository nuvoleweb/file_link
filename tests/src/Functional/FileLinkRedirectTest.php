<?php

namespace Drupal\Tests\file_link\Functional;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\BrowserTestBase;

/**
 * Provides functional tests for 'file_link' field type.
 *
 * @group file_link
 */
class FileLinkRedirectTest extends BrowserTestBase {

  protected $htmlOutputEnabled = FALSE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file_link',
    'file_link_test',
    'entity_test',
    'link',
    'field',
    'user',
    'system',
  ];

  /**
   * Tests redirects.
   */
  public function testRedirects() {
    $entity = EntityTest::create(['name' => 'Foo', 'type' => 'article']);
    $entity->set('url_without_extension', ['uri' => Url::fromUri('base:/test/redirect/302', ['absolute' => TRUE])->toString()]);
    $entity->save();
  }
}
