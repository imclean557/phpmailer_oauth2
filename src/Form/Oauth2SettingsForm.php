<?php

namespace Drupal\phpmailer_oauth2\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form to configure PHPMailer SMTP OAuth2 settings.
 */
class Oauth2SettingsForm extends ConfigFormBase {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(MailManagerInterface $mail_manager, LanguageManagerInterface $language_manager, ModuleHandlerInterface $module_handler) {
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('language_manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'phpmailer_oauth2_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['phpmailer_oauth2.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get immutable config.
    $config = $this->configFactory()->get('phpmailer_oauth2.settings');

    $form['ms_auth'] = [
      '#type' => 'details',
      '#title' => $this->t('Azure AD OAuth2'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['ms_auth']['ms_email_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address'),
      '#default_value' => $config->get('ms_email_address'),
    ];
    $form['ms_auth']['ms_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('ms_client_id'),
    ];
    $form['ms_auth']['ms_client_secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Client secret'),
      '#default_value' => $config->get('ms_client_secret'),
    ];

    $form['ms_auth']['ms_tenant_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tenant ID'),
      '#default_value' => $config->get('ms_tenant_id'),
    ];
    $form['ms_auth']['ms_login'] = [
      '#title' => $this->t('Get auth token'),
      '#type' => 'link',
      '#attributes' => [
        'class' => ['button'],
      ],
      '#url' => Url::fromRoute('phpmailer_oauth2.aad_login'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save the configuration changes.
    $config = $this->configFactory()->getEditable('phpmailer_oauth2.settings');
    $config->set('ms_email_address', $values['ms_email_address'])
      ->set('ms_client_id', $values['ms_client_id'])
      ->set('ms_client_secret', $values['ms_client_secret'])
      ->set('ms_tenant_id', $values['ms_tenant_id']);

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
