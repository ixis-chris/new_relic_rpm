<?php
/**
 * @file
 * New Relic RPM Drupal setting form and callbacks.
 */

/**
 * New Relic RPM settings form.
 */
function new_relic_rpm_settings($form, &$form_state) {
  $form = array();

  $form['track_fieldset'] = array(
    '#type'   => 'fieldset',
    '#title'  => t('Tracking'),
  );

  $form['track_fieldset']['new_relic_rpm_track_drush'] = array(
    '#type' => 'select',
    '#title' => t('Drush transactions'),
    '#description' => t('How do you wish RPM to track drush commands?'),
    '#options' => array(
      'ignore' => t('Ignore completely'),
      'bg' => t('Track as background tasks'),
      'norm' => t('Track normally'),
    ),
    '#default_value' => variable_get('new_relic_rpm_track_drush', 'norm'),
  );

  $form['track_fieldset']['new_relic_rpm_track_cron'] = array(
    '#type' => 'select',
    '#title' => t('Cron transactions'),
    '#description' => t('How do you wish RPM to track cron tasks?'),
    '#options' => array(
      'ignore' => t('Ignore completely'),
      'bg' => t('Track as background tasks'),
      'norm' => t('Track normally'),
    ),
    '#default_value' => variable_get('new_relic_rpm_track_cron', 'norm'),
  );

  $form['track_fieldset']['new_relic_rpm_track_cache_clears'] = array(
    '#type'           => 'checkbox',
    '#title'          => t('Track cache clears'),
    '#description'    => t('If enabled, cache clears will show up under the deployments system in New Relic.'),
    '#default_value'  => variable_get('new_relic_rpm_track_cache_clears', 0),
  );

  $form['track_fieldset']['new_relic_rpm_track_login_user_1'] = array(
    '#type'           => 'checkbox',
    '#title'          => t('Track user 1 logins'),
    '#description'    => t('If enabled, logins by user 1 will show up under the deployments system in New Relic'),
    '#default_value'  => variable_get('new_relic_rpm_track_login_user_1', 0),
  );

  $form['track_fieldset']['new_relic_rpm_track_login_roles'] = array(
    '#type'           => 'checkboxes',
    '#title'          => t('Track logins for these roles'),
    '#description'    => t('Check none to disable. A deployment will show up in New Relic for any users in roles that are checked below.'),
    '#options'        => user_roles(TRUE),
    '#default_value'  => variable_get('new_relic_rpm_track_login_roles', array()),
  );

  $form['track_fieldset']['new_relic_rpm_module_deployment'] = array(
    '#type' => 'select',
    '#title' => t('Track module activation as deployment'),
    '#description' => t('Turning this on will create a "deployment" on the New Relic RPM dashboard each time a module is enabled or disabled. This will help you track before and after statistics.'),
    '#options' => array(
      '1' => t('Enable'),
      '0' => t('Disable'),
    ),
    '#default_value' => variable_get('new_relic_rpm_module_deployment', 0),
  );

  $form['urls_fieldset'] = array(
    '#type'   => 'fieldset',
    '#title'  => t('URLs'),
  );

  $form['urls_fieldset']['new_relic_rpm_ignore_urls'] = array(
    '#type' => 'textarea',
    '#wysiwyg' => FALSE,
    '#title' => t('Ignore URLs'),
    '#description' => t('Enter URLs you wish New Relic RPM to ignore. Enter one URL per line.'),
    '#default_value' => variable_get('new_relic_rpm_ignore_urls', ''),
  );

  $form['urls_fieldset']['new_relic_rpm_bg_urls'] = array(
    '#type' => 'textarea',
    '#wysiwyg' => FALSE,
    '#title' => t('Background URLs'),
    '#description' => t('Enter URLs you wish to have tracked as background tasks. Enter one URL per line.'),
    '#default_value' => variable_get('new_relic_rpm_bg_urls', ''),
  );

  $form['urls_fieldset']['new_relic_rpm_exclusive_urls'] = array(
    '#type' => 'textarea',
    '#wysiwyg' => FALSE,
    '#title' => t('Exclusive URLs'),
    '#description' => t('Enter URLs you wish exclusively track. This is usefull for debugging specific issues. **NOTE** Entering URLs here effectively marks all other URLs as ignored. Leave blank to disable.'),
    '#default_value' => variable_get('new_relic_rpm_exclusive_urls', ''),
  );

  $form['plugin_fieldset'] = array(
    '#type'   => 'fieldset',
    '#title'  => t('API plugin'),
  );

  $form['plugin_fieldset']['new_relic_rpm_license_key'] = array(
    '#type' => 'textfield',
    '#title' => t('API Key'),
    '#description' => t('Enter your New Relic license key to allow the plugin functionality to work with this site. License key is not necessarily the same as API key. License key can be found by logging into New Relic and choosing Account Settings from the drop-down menu at the top right.'),
    '#default_value' => variable_get('new_relic_rpm_license_key', ''),
  );

  $form['plugin_fieldset']['new_relic_rpm_plugin_count_users'] = array(
    '#type' => 'checkbox',
    '#title' => t('Monitor the number of users'),
    '#default_value' => variable_get('new_relic_rpm_plugin_count_users', 0),
  );

  $form['plugin_fieldset']['new_relic_rpm_plugin_count_nodes'] = array(
    '#type' => 'checkbox',
    '#title' => t('Monitor the number of nodes'),
    '#default_value' => variable_get('new_relic_rpm_plugin_count_nodes', 0),
  );

  $form['plugin_fieldset']['new_relic_rpm_plugin_count_comments'] = array(
    '#type' => 'checkbox',
    '#title' => t('Monitor the number of comments'),
    '#default_value' => variable_get('new_relic_rpm_plugin_count_comments', 0),
  );

  $form['misc_fieldset'] = array(
    '#type'   => 'fieldset',
    '#title'  => t('Miscellaneous'),
  );

  $form['misc_fieldset']['new_relic_rpm_api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('API Key'),
    '#description' => t('Enter your New Relic API key if you wish to view reports and analysis within Drupal'),
    '#default_value' => variable_get('new_relic_rpm_api_key', ''),
  );

  $form['misc_fieldset']['new_relic_rpm_watchdog_severities'] = array(
    '#type' => 'select',
    '#multiple' => TRUE,
    '#title' => t('Forward watchdog messages'),
    '#description' => t('Select which watchdog severities should be forwarded to New Relic API as errors.'),
    '#options' => watchdog_severity_levels(),
    '#default_value' => variable_get('new_relic_rpm_watchdog_severities', array()),
  );

  $form['misc_fieldset']['new_relic_rpm_override_exception_handler'] = array(
    '#type' => 'checkbox',
    '#title' => t('Override exception handler'),
    '#description' => t('Check to override default Drupal exception handler and to have exceptions passed to New Relic'),
    '#default_value' => variable_get('new_relic_rpm_override_exception_handler', FALSE),
  );

  return system_settings_form($form);
}

/**
 * Form callback for manually creating a deployment.
 */
function new_relic_rpm_deploy($form, &$form_state) {
  $form = array();

  $form['deploy_user'] = array(
    '#type' => 'textfield',
    '#title' => t('Deployer/Deployment Name'),
    '#required' => TRUE,
    '#description' => t('Enter the name for this deployment of your application. This will be the name shown in your list of deployments on the New Relic RPM website.'),
  );

  $form['deploy_description'] = array(
    '#type' => 'textarea',
    '#title' => t('Deployment Description'),
    '#description' => t('Provide some notes and description regarding this deployment.'),
  );

  $form['deploy_changelog'] = array(
    '#type' => 'textarea',
    '#title' => t('Deployment Changelog'),
    '#description' => t('Provide a specific changelog for this deployment.'),
  );

  $form['deploy_revision'] = array(
    '#type' => 'textfield',
    '#title' => t('Deployment Revision'),
    '#description' => t('Add a revision number to this deployment.'),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create Deployment'),
  );

  return $form;
}

/**
 * FormAPI sumbit callback for the manual deployment creation.
 */
function new_relic_rpm_deploy_submit($form, &$form_state) {

  $deployments = _new_relic_rpm_deploy($form_state['values']['deploy_user'],
    $form_state['values']['deploy_description'],
    $form_state['values']['deploy_changelog'],
    $form_state['values']['deploy_revision']);

  if (strlen($deployments) > 20) {
    drupal_set_message(t('New Relic RPM deployment created successfully'), 'status');
  }
  else {
    drupal_set_message(t('New Relic RPM deployment failed to be created. Please ensure you have your account configured on the <a href="@settings">New Relic RPM Drupal admin page</a>.', array('@settings' => url('admin/config/development/new-relic-rpm'))), 'error');
  }
}
