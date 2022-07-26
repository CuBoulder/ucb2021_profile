<?php

/**
 * @file
 * Enables modules and site configuration for a test profile.
 */

use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function ucb2021_profile_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  $form['#submit'][] = 'ucb2021_profile_form_install_configure_submit';
}

/**
 * Submission handler to sync the contact.form.feedback recipient.
 */
function ucb2021_profile_form_install_configure_submit($form, FormStateInterface $form_state) {
  $site_mail = $form_state->getValue('site_mail');
  ContactForm::load('feedback')->setRecipients([$site_mail])->trustData()->save();
}

/**
 * Implements hook_modules_installed().
 *
 * @param $modules
 * Install syslog and uninstall dblog if site is not local
 * Install default content for the site
 */
function ucb2021_profile_modules_installed( $modules ){
  if( in_array('ucb2021_profile', $modules )){
    $installer = \Drupal::service('module_installer');
    // install or uninstall modules once the profile has installed
    $installer->install([
		'ucb_site_configuration',
      'ucb_site_contact_info',
      'ucb_custom_paragraphs',
      'ucb_custom_page_types',
      'ucb_default_content',
      'ucb_focal_image_enable'
    ]);
	\Drupal::logger('ucb2021_profile')->notice('Installed CU Site Configuration');
    \Drupal::logger('ucb2021_profile')->notice('Installed CU Site Contact Info');
    \Drupal::logger('ucb2021_profile')->notice('Installed CU Custom Paragraph Types');
    \Drupal::logger('ucb2021_profile')->notice('Installed CU Custom Page Types');
    \Drupal::logger('ucb2021_profile')->notice('Installed CU Default Content');
    \Drupal::logger('ucb2021_profile')->notice('Installed CU Focal Image Enable');
  }
}
