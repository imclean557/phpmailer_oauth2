<?php
namespace Drupal\phpmailer_oauth2\OptionProvider;

use InvalidArgumentException;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;

/**
 * Add http basic auth into access token request options
 * @link https://tools.ietf.org/html/rfc6749#section-2.3.1
 */
class HttpBasicAuthWithScope extends HttpBasicAuthOptionProvider {

  /**
   * @inheritdoc
   */
  public function getAccessTokenOptions($method, array $params) {
    if (empty($params['client_id']) || empty($params['client_secret'])) {
      throw new InvalidArgumentException('clientId and clientSecret are required for http basic auth');
    }

    $encodedCredentials = base64_encode(sprintf('%s:%s', $params['client_id'], $params['client_secret']));
    unset($params['client_id'], $params['client_secret']);

    $options = parent::getAccessTokenOptions($method, $params);
    $options['headers']['Authorization'] = 'Basic ' . $encodedCredentials;
    $options['headers']['Scope'] = $params['scope'];

    return $options;
  }

}
