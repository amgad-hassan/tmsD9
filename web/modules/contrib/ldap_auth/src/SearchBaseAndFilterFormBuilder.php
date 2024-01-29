<?php

namespace Drupal\ldap_auth;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\Form\LDAPFormBase;

class SearchBaseAndFilterFormBuilder extends LDAPFormBase{

  public static function insertForm(array &$form, FormStateInterface $form_state, $config,$possible_search_bases_in_key_val){
    global $base_url;

    $form['search_base_attribute'] = [
        '#id' => 'miniorange_ldap_search_base_attribute',
        '#title' => t('Search Base(s):'),
        '#type' => 'select',
        '#description' => t('Search Base indicates the location in your LDAP server where your users reside. Select the Distinguished Name(DN) of the Search Base object from the above dropdown.<br>Multiple Search Bases are supported in the <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing"><strong>[Premium, All-Inclusive]</strong></a> version of the module.'),
        '#default_value' => $config->get('miniorange_ldap_search_base'),
        '#options' => $possible_search_bases_in_key_val,//$form['miniorange_search_base_options']['#value'],
        '#attributes' => ['style' => 'width:65%;height:30px'],
    ];
    $form['miniorange_ldap_custom_sb_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Other Search Base(s):'),
        '#default_value' => empty($config->get('miniorange_ldap_custom_sb_attribute')) ? reset($possible_search_bases_in_key_val) : $config->get('miniorange_ldap_custom_sb_attribute'),
        '#states' => ['visible' => [':input[name = "search_base_attribute"]' => ['value' => 'custom_base']]],
        '#attributes' => ['style' => 'width:65%;'],
        '#maxlength' => 1024,
    ];

    // Username Attribute
    $ldap_attribute_option = [
        'samaccountname' => t('samaccountname'),
        'mail' => t('mail'),
        'userprincipalname' => t('userprincipalname'),
        'cn' => t('cn'),
        'sn' => t('sn'),
        'givenname' => t('givenname'),
        'uid' => t('uid'),
        'displayname' => t('displayname'),
        'custom' => t('other'),
    ];
    $form['ldap_auth']['settings']['username_attribute'] = [
        '#id' => 'miniorange_ldap_username_attribute',
        '#title' => t('LDAP Username Attribute / Search Filter:'),
        '#type' => 'select',
        '#description' => t('Select the LDAP attribute by which the user will be searched in the LDAP server. Using this LDAP attribute value your user can login to Drupal.<br> <b>For example:</b> If you want the user to login to Drupal using their samaccountName ( the one present in the LDAP server), you can select <b>samaccountName</b> in the dropdown.<br>You can even search for your user using a Custom Search Filter in the <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing"><strong>[Premium, All-Inclusive]</strong></a> version of the module<div><br>'
        ),
        '#default_value' => $config->get('miniorange_ldap_username_attribute_option'),
        '#options' => $ldap_attribute_option,
        '#attributes' => ['style' => 'width:65%;'],
    ];

    $form['miniorange_ldap_custom_username_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Other Username Attribute'),
        '#default_value' => $config->get('miniorange_ldap_custom_username_attribute'),
        '#states' => [
            'visible' => [
                ':input[name = "username_attribute"]' => ['value' => 'custom']
            ],
            'required' => [
                ':input[name = "username_attribute"]' => ['value' => 'custom']
            ]
        ],
        '#attributes' => ['style' => 'width:65%;'],
    ];

    // Email Attribute
     $saved_email_attribute = $config->get('miniorange_ldap_email_attribute');
    $form['miniorange_ldap_email_attribute'] = [
        '#type' => 'select',
        '#title' => t('LDAP Email Attribute'),
        '#options' => $ldap_attribute_option,
        '#required' => false,
        '#attributes' => ['style' => 'width:65%;'],
        '#default_value' => $saved_email_attribute != NULL && in_array($saved_email_attribute,$ldap_attribute_option)? $saved_email_attribute : 'custom',
        '#description' => t("Select the LDAP attribute in which you get the email address of your LDAP users."),
    ];

    $form['miniorange_ldap_custom_email_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Other LDAP Email Attribute'),
        '#default_value' => $saved_email_attribute,
        '#states' => [
            'visible' => [
                ':input[name = "miniorange_ldap_email_attribute"]' => ['value' => 'custom']
            ],
            'required' => [
                ':input[name = "miniorange_ldap_email_attribute"]' => ['value' => 'custom']
            ]
        ],
        '#attributes' => ['style' => 'width:65%;'],
    ];

    //image attribute
    $form['miniorange_ldap_photo_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Image/Profile Attribute'),
        '#disabled' => TRUE,
        '#attributes' => [
            'style' => 'width:65%;',
            'placeholder' => t('Enter image attribute eg. jpegphoto, thumbnailphoto'),
        ],
        '#description' => t("Enter the LDAP attribute in which you get the profile photo/image of your users. <a href='#'><b>[All-Inclusive]</b></a>"),
    ];

    $form['back_step_3'] = [
        '#type' => 'submit',
        '#button_type' => 'danger',
        '#prefix' => "<div class='pito_enable_alignment'>",
        '#value' => t('&#171; Back'),
        '#submit' => ['::miniorange_ldap_back_3'],
        '#attributes' => ['style' => 'display: inline-block;'],
    ];

    $form['next_step_3'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => t('Next &#187; '),
        '#suffix' => "</div></div>",
        '#attributes' => ['style' => 'float: right;display:block;'],
        '#submit' => ['::miniorange_ldap_next3'],
    ];

    return $form;
  }
}
