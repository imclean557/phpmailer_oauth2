<?php

namespace Drupal\phpmailer_oauth2\Plugin\PhpmailerOauth2;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\phpmailer_oauth2\Service\AzureProviderService;
use Drupal\phpmailer_smtp\Plugin\PhpmailerOauth2\PhpmailerOauth2PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Azure OAuth2 plugin.
 *
 * @PhpmailerOauth2(
 *   id = "azure",
 *   name = @Translation("Azure OAuth2"),
 * )
 */
class AzureOauth2 extends PhpmailerOauth2PluginBase implements ContainerFactoryPluginInterface {

  /**
   * The Azure provider service.
   *
   * @var \Drupal\phpmailer_oauth2\Service\AzureProviderService
   */
  protected $azureProvider;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AzureProviderService $azure_provider) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->azureProvider = $azure_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('phpmailer_oauth2.azure_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthOptions() {
    return $this->azureProvider->getAuthOptions();
  }

}
