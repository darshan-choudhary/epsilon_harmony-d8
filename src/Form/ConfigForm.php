<?php

namespace Drupal\epsilon_harmony\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the ConfigForm form controller.
 *
 * This example demonstrates a simple form with a singe text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class ConfigForm extends ConfigFormBase {

  const SETTINGS = 'epsilon_harmony.settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['epsilon_harmony_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#description' => $this->t('Client ID for your Epsilon Harmony account.'),
      '#required' => TRUE,
      '#default_value' => $config->get('epsilon_harmony_client_id'),
    ];

    $form['epsilon_harmony_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret Key'),
      '#description' => $this->t('Secret key for your Epsilon Harmony account.'),
      '#required' => TRUE,
      '#default_value' => $config->get('epsilon_harmony_secret_key'),
    ];

    $form['epsilon_harmony_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#description' => $this->t('Username for your Epsilon Harmony account.'),
      '#required' => TRUE,
      '#default_value' => $config->get('epsilon_harmony_username'),
    ];

    $form['epsilon_harmony_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#description' => $this->t('Password for your Epsilon Harmony account.'),
      '#required' => TRUE,
      '#default_value' => $config->get('epsilon_harmony_password'),
    ];

    $form['epsilon_harmony_xouid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('X-OUID'),
      '#description' => $this->t('Enter the X-OUID key received from the Epsilon Harmony representative.'),
      '#required' => TRUE,
      '#default_value' => $config->get('epsilon_harmony_xouid'),
    ];

    $form['epsilon_harmony_region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#description' => $this->t('Enter the default region for the API.'),
      '#options' => [
        'eu' => $this->t('Canada'),
        'us' => $this->t('US'),
      ],
      '#empty_option' => $this->t('-select-'),
      '#required' => TRUE,
      '#default_value' => $config->get('epsilon_harmony_region'),
    ];

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'epsilon_harmony_config_form';
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration
    $this->configFactory->getEditable(static::SETTINGS)
    // Set the submitted configuration setting
    ->set('epsilon_harmony_client_id', $form_state->getValue('epsilon_harmony_client_id'))
    ->set('epsilon_harmony_secret_key', $form_state->getValue('epsilon_harmony_secret_key'))
    ->set('epsilon_harmony_username', $form_state->getValue('epsilon_harmony_username'))
    ->set('epsilon_harmony_password', $form_state->getValue('epsilon_harmony_password'))
    ->set('epsilon_harmony_xouid', $form_state->getValue('epsilon_harmony_xouid'))
    ->set('epsilon_harmony_region', $form_state->getValue('epsilon_harmony_region'))
    ->save();

    $this->messenger()->addMessage($this->t('Your configurations have been saved.'));
  }

}
