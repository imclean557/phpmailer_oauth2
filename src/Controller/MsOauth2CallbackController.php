<?php

namespace Drupal\phpmailer_oauth2\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\phpmailer_oauth2\Service\AzureProviderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class LoginCallbackController.
 */
class MsOauth2CallbackController extends ControllerBase {

  /**
   * The request stack used to access request globals.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Azure provider service.
   *
   * @var \Drupal\phpmailer_oauth2\Service\AzureProviderService
   */
  protected $azureProvider;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    RequestStack $request_stack,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory,
    AzureProviderService $azure_provider
  ) {
    $this->requestStack = $request_stack;
    $this->loggerFactory = $logger_factory;
    $this->configFactory = $config_factory;
    $this->azureProvider = $azure_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('phpmailer_oauth2.azure_provider'),
    );
  }

  /**
   * Callback for the login.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return mixed
   *   A redirect to the set URL.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  public function callback(Request $request) {

    if (!$request->get('error') && !$request->get('code')) {
      throw new NotFoundHttpException();
    }

    if ($request->get('error')) {
      $variables = [
        '@error' => $request->get('error'),
        '@details' => $request->get('error_description') ? $request->get('error_description') : $this->t('Unknown error.'),
      ];
      $message = 'Authorization failed: @error. Details: @details';
      $this->loggerFactory->get('phpmailer_oauth2')->error($message, $variables);
      $this->messenger()->addError($this->t('Could not authenticate with Azure.'));
    }

    if ($authCode = $request->get('code')) {
      $config = $this->configFactory->getEditable('phpmailer_oauth2.settings');
      $config->set('ms_refresh_token', $authCode)->save();
      $this->messenger()->addMessage('Auth token retrieved.');

      $provider = $this->azureProvider->getProvider();
      $accessToken = $provider->getAccessToken('authorization_code', ['code' => $authCode]);
      $config->set('ms_access_token', $accessToken->getToken())->save();
      $this->messenger()->addMessage('Access token retrieved.');

      $config->set('ms_refresh_access_token', $accessToken->getRefreshToken())->save();
      $this->messenger()->addMessage('Refresh token retrieved.');

      return $this->redirect('phpmailer_oauth2.settings');
    }

    $this->messenger()->addError('Could not retrieve auth token.');
    return $this->redirect('phpmailer_oauth2.settings');
  }

}
