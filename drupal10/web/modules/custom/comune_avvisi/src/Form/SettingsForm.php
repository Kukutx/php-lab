<?php

declare(strict_types=1);

namespace Drupal\comune_avvisi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings stored as configuration (exportable with `drush cex`).
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'comune_avvisi_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['comune_avvisi.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('comune_avvisi.settings');

    $form['highlighted_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Highlighted notices in the block'),
      '#min' => 1,
      '#max' => 10,
      '#default_value' => $config->get('highlighted_limit'),
      '#required' => TRUE,
    ];
    $form['expiring_days'] = [
      '#type' => 'number',
      '#title' => $this->t('"Expiring soon" window (days)'),
      '#min' => 1,
      '#max' => 365,
      '#default_value' => $config->get('expiring_days'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ((int) $form_state->getValue('expiring_days') > 90 && (int) $form_state->getValue('highlighted_limit') > 5) {
      $form_state->setErrorByName('highlighted_limit', $this->t('With a long window, keep at most 5 highlighted notices.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('comune_avvisi.settings')
      ->set('highlighted_limit', (int) $form_state->getValue('highlighted_limit'))
      ->set('expiring_days', (int) $form_state->getValue('expiring_days'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
