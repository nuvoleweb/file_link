<?php

namespace Drupal\Tests\file_link\Kernel;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\KernelTestBase;
use Psr\Log\LoggerInterface;

/**
 * Provides kernel tests for 'file_link' field type.
 *
 * @group file_link
 */
class FileLinkRequestMethodSettingTest extends KernelTestBase {

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
   * Test entity.
   *
   * @var \Drupal\entity_test\Entity\EntityTest
   */
  protected $entity;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['file_link_test']);
    $this->installEntitySchema('entity_test');
    $this->entity = EntityTest::create(['name' => 'Foo', 'type' => 'article']);
    $this->logger = $this->prophesize(LoggerInterface::class);
    $this->container->get('logger.factory')
      ->get('file_link')
      ->addLogger($this->logger->reveal());
  }

  /**
   * Tests file_link field metadata storage without extension.
   */
  public function testHttpMethodSetting() {
    $uri = Url::fromUserInput('/test/redirect/301/md', ['absolute' => TRUE])->toString();
    $settings = Settings::getAll();
    $this->entity->set('url_without_extension', ['uri' => $uri]);
    $this->entity->save();
    $request = \Drupal\file_link_test\HttpMiddleware::getLastRequest();
    $this->assertNotNull($request);
    $this->assertEquals('HEAD', $request->getMethod());

    $settings['file_link.http_request_method'] = 'GET';
    new Settings($settings);
    $this->entity->set('url_without_extension', ['uri' => $uri]);
    $this->entity->save();
    $request = \Drupal\file_link_test\HttpMiddleware::getLastRequest();
    $this->assertNotNull($request);
    $this->assertEquals('GET', $request->getMethod());

    $settings['file_link.http_request_method'] = 'OPTIONS';
    new Settings($settings);
    $this->logger->log(
      \Drupal\Core\Logger\RfcLogLevel::WARNING,
      'Wrong parameter given for "file_link.http_request_method" settings key, @value passed, but HEAD or GET expected.',
      \Prophecy\Argument::withEntry('@value', 'OPTIONS')
    )->shouldBeCalled();
    $this->entity->set('url_without_extension', ['uri' => $uri]);
    $this->entity->save();
    $request = \Drupal\file_link_test\HttpMiddleware::getLastRequest();
    $this->assertNotNull($request);
    $this->assertEquals('HEAD', $request->getMethod());
  }

}
