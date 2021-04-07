<?php

namespace Drupal\file_link_test;

use Drupal\Core\Site\Settings;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * A middleware for guzzle to test requests.
 */
class HttpMiddleware {

  /**
   * List of counters of requested resources.
   *
   * @var array
   */
  public static $recorder = [];

  /**
   * The last request.
   *
   * @var RequestInterface|null
   */
  public static $lastRequest;

  /**
   * Gets number of requests made to particular url.
   *
   * @param string $url
   *   The searched URL.
   *
   * @return int
   *   Number of requests made to that url.
   */
  public static function getRequestCount(string $url) {
    if (!isset(static::$recorder[$url])) {
      static::$recorder[$url] = 0;
    }

    return static::$recorder[$url];
  }

  /**
   * Gets the last made request.
   *
   * @return \Psr\Http\Message\RequestInterface|null
   */
  public static function getLastRequest() {
    return static::$lastRequest;
  }

  /**
   * Invoked method that returns a promise.
   */
  public function __invoke() {
    return function ($handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        static::$lastRequest = $request;
        $uri = $request->getUri();
        $settings = Settings::get('file_link_test_middleware', []);
        // Check if the request is made to one of our fixtures.
        $key = $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath();

        if (array_key_exists($key, $settings)) {
          if (!isset(static::$recorder[$key])) {
            static::$recorder[$key] = 0;
          }
          static::$recorder[$key]++;

          return $this->createPromise($request, $settings[$key]);
        }

        // Otherwise, no intervention. We defer to the handler stack.
        return $handler($request, $options);
      };
    };
  }

  /**
   * Creates a promise for the file_link fixture request.
   *
   * @param RequestInterface $request
   *
   * @return \GuzzleHttp\Promise\PromiseInterface
   */
  protected function createPromise(RequestInterface $request, $fixture) {
    // Create a response from the fixture.
    $response = new Response($fixture['status'] ?? 200, $fixture['headers'] ?? [], $fixture['body'] ?? NULL);
    return new FulfilledPromise($response);
  }

}
