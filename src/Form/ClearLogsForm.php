<?php

namespace Drupal\epsilon_harmony\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class ClearLogsForm extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @var int
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::database()->truncate('epsilon_logs')->execute();
    drupal_set_message(t('Epsilon logs have been cleared.'), 'status', TRUE);
    $form_state->setRedirect('epsilon_harmony.logs');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "epsilon_clear_logs";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('epsilon_harmony.logs');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to clear all the logs?');
  }

}
