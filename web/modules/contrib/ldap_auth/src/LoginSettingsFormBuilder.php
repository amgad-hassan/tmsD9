<?php
namespace Drupal\ldap_auth;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\Form\LDAPFormBase;

class LoginSettingsFormBuilder extends LDAPFormBase {

  public static function insertForm(array &$form, FormStateInterface $form_state, $config) {
    global $base_url;
    $form['miniorange_ldap_enable_ldap_markup'] = [
        '#markup' => t("<h3 style='margin-top: 0%'>Login Settings:</h3><hr style='margin-top: -0.5%'>"),
    ];
    $form['miniorange_ldap_enable_ldap'] = [
        '#type' => 'checkbox',
        '#description' => t('Select this checkbox to enable Login using LDAP/Active Directory credentials.'),
        '#title' => t('Enable Login with LDAP'),
        '#default_value' => $config->get('miniorange_ldap_enable_ldap'),
    ];
    $form['miniorange_ldap_enable_auto_reg'] = [
        '#type' => 'checkbox',
        '#title' => t('Automatically Create LDAP Users in Drupal if they DO NOT EXIST in Drupal.<a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing"><strong>[Premium, All-Inclusive]</strong></a>'),
        '#disabled' => 'true',
        '#default_value' => $config->get('miniorange_ldap_enable_auto_reg'),
    ];


    $form['set_of_radiobuttons']['miniorange_ldap_authentication'] = [
        '#type' => 'radios',
        '#disabled' => true,
        '#title' => t('Authentication restrictions: <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing">[Premium, All-Inclusive]</a>'),
        '#default_value' => is_null($config->get('miniorange_ldap_authentication')) ? 0 : $config->get('miniorange_ldap_authentication'),
        '#options' => [
            0 => t('User can login using both their Drupal and LDAP credentials'),
            1 => t('User can login in Drupal using their LDAP credentials and Drupal admins can also login using their local Drupal credentials'),
            2 => t('Users can only login using their LDAP credentials'),
        ],
        '#disabled_values' => array(1, 2),
    ];


    $form['back_step_3'] = [
        '#type' => 'submit',
        '#button_type' => 'danger',
        '#value' => t('&#171; Back'),
        '#submit' => ['::miniorange_ldap_back_5'],
        '#attributes' => ['style' => 'width: fit-content;display:inline-block;'],
    ];
    $form['next_step_1'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => t('Save & Next &#187; '),
        '#attributes' => ['style' => 'float: right;display:block;'],
        '#submit' => ['::miniorange_ldap_next_1'],
    ];
    $form['closing_markup_for_login_settings_form'] = [
        '#markup' => '</div>',
    ];
    return $form;
  }

}
