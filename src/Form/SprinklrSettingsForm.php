<?php

namespace Drupal\sprinklr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure sprinklr settings.
 */
class SprinklrSettingsForm extends ConfigFormBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new SprinklrSettingsForm instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sprinklr_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sprinklr.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('sprinklr.settings');
    $allowed_urls = $config->get('allowed_urls');
    $form['sprinklr_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['sprinklr_configuration']['enable_disable_sprinklr'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('sprinklr_enabled'),
      '#title' => $this->t('Enable sprinklr chatbot.'),
    ];
    $form['sprinklr_configuration']['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App Id'),
      '#required' => TRUE,
      '#description' => $this->t('Provide app id to connect with sprinklr chatbot server.'),
      '#default_value' => $config->get('app_id'),
    ];
    $form['sprinklr_configuration']['urls'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Allow sprinklr for specific pages'),
      '#open' => TRUE,
    ];
    $form['sprinklr_configuration']['urls']['urls_negate'] = [
      '#type' => 'radios',
      '#options' => [
        $this->t('Skip sprinklr for listed pages'),
        $this->t('Allow sprinklr for listed pages'),
      ],
      '#default_value' => $config->get('urls_negate'),
    ];
    $form['sprinklr_configuration']['urls']['allowed_urls'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URLs for sprinklr chatbot integration.'),
      '#description' => $this->t("Specify pages by using their url aliases/paths. Enter one path per line. The '*' character is a wildcard. An example path is %article-wildcard for every article page. Use %front for the front page. Use %all to target all pages. If no urls are provided all URLs are skipped.", [
        '%article-wildcard' => '/article/*',
        '%front' => '<front>',
        '%all' => '/*',
      ]),
      '#default_value' => $config->get('allowed_urls'),
    ];
    $form['sprinklr_configuration']['content_types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Allow sprinklr for specific content types'),
      '#description' => $this->t('Choose content types to enable sprinklr for all the nodes of the selected content types. To skip particular node pages add paths in the urls section and choose skip option.'),
      '#open' => TRUE,
    ];

    $options = [];
    $types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    // Prepare array of content types.
    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }
    $form['sprinklr_configuration']['content_types']['allowed_content_types'] = [
      '#type' => 'checkboxes',
      '#open' => TRUE,
      '#title' => $this->t('Content Types'),
      '#type' => 'checkboxes',
      '#title' => $this->t('Choose content types to allow sprinklr'),
      '#options' => $options,
      '#default_value' => $config->get('allowed_content_types'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $allowed_urls = $form_state->getValue('allowed_urls');
    $this->config('sprinklr.settings')
      ->set('sprinklr_enabled', $form_state->getValue('enable_disable_sprinklr'))
      ->set('app_id', $form_state->getValue('app_id'))
      ->set('urls_negate', $form_state->getValue('urls_negate'))
      ->set('allowed_urls', $form_state->getValue('allowed_urls'))
      ->set('allowed_content_types', $form_state->getValue('allowed_content_types'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
