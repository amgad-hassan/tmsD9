<?php

namespace Drupal\ldap_auth;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\Form\LDAPFormBase;

class ContactLDAPServerFormBuilder extends LDAPFormBase{
  public static function insertForm(array &$form, FormStateInterface $form_state, $config){
    global $base_url;
    $form['ldap_server'] = [
        '#markup' => t('
        <table class="table-header-properties">
            <tr class="custom-table-properties">
                <td class="shift-text-left custom-table-properties"><h4>Enter Your LDAP Server URL:</h4></td>
                <td class="custom-table-properties"><a class="button button--small btn-right" href ="https://www.youtube.com/watch?v=wBe8T6FLKx4" target="_blank">Setup Video</a><a class="button button--small btn-right" href="https://plugins.miniorange.com/guide-to-configure-ldap-ad-integration-module-for-drupal" target="_blank">Setup Guide</a></td>
            </tr>
        </table>

      '),
    ];
    $form['ldap_server_url_markup_start'] = [
        '#markup' =>'<div class="ldap_Server_row">',
    ];

    $form['miniorange_ldap_options'] = [
        '#type' => 'value',
        '#id' => 'miniorange_ldap_options',
        '#value' => [
            'ldap://' => t('ldap://'),
            'ldaps://' => t('ldaps://'),
        ],
    ];
    $form['miniorange_ldap_protocol'] = [
        '#id' => 'miniorange_ldap_protocol',
        '#type' => 'select',
        '#prefix' => '<div class="ldap-column left">',
        '#suffix' => '</div>',
        '#default_value' => $config->get('miniorange_ldap_protocol'),
        '#options' => $form['miniorange_ldap_options']['#value'],
        '#attributes' => ['style' => 'width:100%'],
    ];
    $form['miniorange_ldap_server_address'] = [
        '#type' => 'textfield',
        '#id' => 'miniorange_ldap_server_address',
        '#prefix' => '<div class="ldap-column middle">',
        '#suffix' => '</div>',
        '#default_value' => $config->get('miniorange_ldap_server_address'),
        '#attributes' => [
            'style' => 'width:100%;',
            'placeholder' => 'Enter your server-address or IP',
        ],
    ];
    $form['miniorange_ldap_server_port_number'] = [
        '#type' => 'textfield',
        '#prefix' => '<div class="ldap-column right">',
        '#suffix' => '</div>',
        '#default_value' => $config->get('miniorange_ldap_server_port_number'),
        '#attributes' => [
            'style' => 'width:100%;',
            'placeholder' => '<port>'
        ],
    ];

    $form['ldap_server_url_markup_end'] = [
        '#markup' => t('</div>'),
    ];

    $form['ldap_server_url_description'] = [
        '#markup' =>t('<small>Specify the host name for the LDAP server eg: ldap://myldapserver.domain:389 , ldap://89.38.192.1:389. When using SSL, the host may have to take the form ldaps://host:636</small>'),
    ];

    $form['miniorange_ldap_enable_tls'] = [
        '#type' => 'checkbox',
        '#id' => 'check',
        '#disabled' => 'true',
        '#title' => t('Enable TLS (Check this only if your server use TLS Connection) <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing"><strong>[Premium, All-Inclusive]</strong></a>'),
    ];
    $form['miniorange_ldap_contact_server_button'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => t('Contact LDAP Server'),
        '#submit' => ['::test_ldap_connection'],
    ];
    return $form;
  }
}
