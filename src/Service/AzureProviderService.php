<?php

namespace Drupal\phpmailer_oauth2\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use TheNetworg\OAuth2\Client\Provider\Azure;

/**
 * Helper to generate a new Azure provider.
 */
class AzureProviderService {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger interface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_channel
   *   The logger.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_channel, RequestStack $request_stack) {
    $this->configFactory = $config_factory;
    $this->logger = $logger_channel;
    $this->requestStack = $request_stack;
  }

  /**
   * Create a new Azure provider for logging in.
   *
   * @return object
   *   The Azure provider.
   */
  public function getLoginProvider() {
    $config = $this->configFactory->get('phpmailer_oauth2.settings');

    $options = [
      'clientId' => $config->get('ms_client_id'),
      'clientSecret' => $config->get('ms_client_secret'),
      'redirectUri' => $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost() . '/phpmailer_oauth2/aad-callback',
    ];

    $provider = new Azure($options);
    $provider->tenant = $config->get('ms_tenant_id');
    $provider->urlAPI = 'https://graph.microsoft.com/';
    $provider->API_VERSION = '1.0';
    $provider->defaultEndPointVersion = $provider::ENDPOINT_VERSION_2_0;
    $provider->scope = ['offline_access', 'https://outlook.office.com/SMTP.Send'];

    return $provider;
  }

  /**
   * Create a new provider for SMTP Auth.
   *
   * @return object
   *   The Azure provider.
   */
  public function getProvider() {
    $config = $this->configFactory->get('phpmailer_oauth2.settings');

    $options = [
      'clientId' => $config->get('ms_client_id'),
      'clientSecret' => $config->get('ms_client_secret'),
    ];

    $provider = new Azure($options);
    $provider->tenant = $config->get('ms_tenant_id');
    $provider->urlAPI = 'https://graph.microsoft.com/';
    $provider->API_VERSION = '1.0';
    $provider->defaultEndPointVersion = $provider::ENDPOINT_VERSION_2_0;
    $provider->scope = ['offline_access', 'https://outlook.office.com/SMTP.Send'];

    return $provider;
  }

  /**
   * Get OAuth options for PHPMailer OAuth.
   *
   * @return array
   *   PHPMailer auth options
   */
  public function getAuthOptions() {
    $config = $this->configFactory->get('phpmailer_oauth2.settings');
    return [
      'provider' => $this->getProvider(),
      'userName' => $config->get('ms_email_address'),
      'clientSecret' => $config->get('ms_client_secret'),
      'clientId' => $config->get('ms_client_id'),
      'refreshToken' => $config->get('ms_refresh_access_token'),
    ];
  }

}
