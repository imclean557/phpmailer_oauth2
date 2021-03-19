<?php

namespace Drupal\phpmailer_oauth2\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\phpmailer_oauth2\Service\AzureProviderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoginController.
 */
class MsLoginController extends ControllerBase {

  /**
   * The Azure provider service.
   *
   * @var \Drupal\phpmailer_oauth2\Service\AzureProviderService
   */
  protected $azureProvider;

  /**
   * {@inheritdoc}
   */
  public function __construct(AzureProviderService $azure_provider) {
    $this->azureProvider = $azure_provider->getProvider();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('phpmailer_oauth2.azure_provider'),
    );
  }

  /**
   * Login.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   Redirect to provider login.
   */
  public function login() {
    $authorizationUrl = $this->azureProvider->getAuthorizationUrl(['scope' => $provider->scope]);

    return new TrustedRedirectResponse($authorizationUrl);
  }

}
