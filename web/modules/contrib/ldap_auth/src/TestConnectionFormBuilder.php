<?php

namespace Drupal\ldap_auth;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\Form\LDAPFormBase;

class TestConnectionFormBuilder extends LDAPFormBase{
  public static function insertForm(array &$form, FormStateInterface $form_state, $config){
    $form['ldap_server_test_bind_connection'] = [
        '#markup' => t('
        <table class="table-header-properties">
            <tr class="custom-table-properties">
                <td class="shift-text-left custom-table-properties"><h4>Service Account / Bind Details</h4></td>
                <td class="custom-table-properties"><a class="button button--small btn-right" href ="https://www.youtube.com/watch?v=wBe8T6FLKx4" target="_blank">Setup Video</a><a class="button button--small btn-right" href="https://plugins.miniorange.com/guide-to-configure-ldap-ad-integration-module-for-drupal" target="_blank">Setup Guide</a></td>
            </tr>
        </table>
      '),
    ];


    // description when anonymous bind support
    if($config->get('supports_anonymous_bind')){
      $form['miniorange_ldap_anonymous_bind_markup'] = [
          '#markup' => t('<div class="mo_ldap_highlight_background_note_1" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">If you want to bind anonymously to your LDAP server click on the <strong>Test Connection & Proceed</strong> without entering any credentials.</div>'),
      ];
    }


    $form['miniorange_ldap_server_account_username'] = [
        '#type' => 'textfield',
        '#title' => t('Bind Account DN:'),
        '#default_value' => $config->get('miniorange_ldap_server_account_username'),
        '#description' => t("Enter the <i>Service Account username</i> or the <i>Distinguished Name (DN)</i> for the account you wish to bind connection to your LDAP Server"),
        '#attributes' => [
            'placeholder' => 'CN=service,DC=domain,DC=com',
        ],
        '#required' => $config->get('supports_anonymous_bind') == 0,

        '#size' => 60,
    ];
    $form['miniorange_ldap_server_account_password'] = [
        '#type' => 'password',
        '#title' => t('Bind Account Password:'),
        '#description' => t('Enter the password for your Service Account'),
        '#default_value' => $config->get('miniorange_ldap_server_account_password'),
        '#attributes' => [
            'placeholder' => 'Enter password here',
        ],
        '#required' => $config->get('supports_anonymous_bind') == 0 ,
        '#size' => 60,
    ];

    $form['miniorange_ldap_test_connection_button'] = [
        '#type' => 'submit',
        '#value' => t('&#171; Back'),
        '#button_type' => 'danger',
        '#limit_validation_errors' => [],
        '#submit' => ['::back_to_contact_server'],
    ];


    $form['next_step_x'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => t('Test Connection & Proceed &#187;'),
        '#attributes' => ['style' => 'float: right;display:block;'],
        '#submit' => ['::test_connection_ldap'],
    ];

    return $form;
  }
}
