<?php

namespace Drupal\ldap_auth;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\Form\LDAPFormBase;
use Drupal\Core\Url;

class ReviewConfigFormBuilder extends LDAPFormBase{
  public static function insertForm(array &$form, FormStateInterface $form_state, $config, $ldap_connect, $next_disabled){
    global $base_url;

    $form['review_config'] = array(
        '#type' => 'details',
        '#title' => t('Contact LDAP Server [ '.$config->get('miniorange_ldap_server').' ]'),
    );

    $form['review_config']['miniorange_ldap_enable_tls'] = [
        '#type' => 'checkbox',
        '#disabled' => TRUE,
        '#id' => 'check',
        '#title' => t('Enable TLS (Check this only if your server use TLS Connection)  <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing"><strong>[Premium, All-Inclusive]</strong></a>'),
    ];
    $form['review_config']['miniorange_ldap_server_review'] = [
        '#type' => 'textfield',
        '#default_value' => $config->get('miniorange_ldap_server'),
        '#title' => t('LDAP Server'),
        '#attributes' => [
            'placeholder' => '<ldap/ldaps>://<your_server_addres>:<port number>'
        ],
    ];

    $form['review_config']['miniorange_ldap_contact_server_button'] = [
        '#type' => 'submit',
        '#value' => t('Contact LDAP Server'),
        '#submit' => ['::test_ldap_connection_review'],
        '#suffix' => '<br><br>',
    ];

    global $base_url;
    $form['review_config_test_connection'] = array(
        '#type' => 'details',
        '#title' => t('LDAP Binding'),
        '#open'=> FALSE,
    );

    // description when anonymous bind support
    $form['review_config_test_connection']['miniorange_ldap_anonymous_bind_markup'] = [
        '#markup' => t('<div class="mo_ldap_highlight_background_note_1">If you want to bind anonymously to your LDAP server click on the <strong>Test Connection</strong> without entering any credentials.</div><hr>'),
    ];


    $form['review_config_test_connection']['miniorange_ldap_server_account_username'] = [
        '#type' => 'textfield',
        '#title' => t('Bind Account DN:'),
        '#default_value' => $config->get('miniorange_ldap_server_account_username'),
        '#description' => t("Enter the <i>Service Account username</i> or the <i>Distinguished Name (DN)</i> for the account you wish to bind connection to your LDAP Server"),
        '#attributes' => [
            'placeholder' => 'CN=service,DC=domain,DC=com',
        ],
        '#size' => 60,
    ];
    $form['review_config_test_connection']['miniorange_ldap_server_account_password'] = [
        '#type' => 'password',
        '#title' => t('Bind Account Password:'),
        '#description' => t('Enter the password for your Service Account'),
        '#default_value' => $config->get('miniorange_ldap_server_account_password'),
        '#attributes' => [
            'placeholder' => 'Enter password here',
        ],
        '#size' => 60,
    ];

    $form['review_config_test_connection']['miniorange_ldap_test_connection_button'] = [
        '#type' => 'submit',
        '#disabled' => $next_disabled,
        '#prefix' => '<br>',
        '#suffix' => '<br><br>',
        '#value' => t('Test Connection'),
        '#submit' => ['::test_connection_ldap'],
    ];

    $form['review_config_set_filter_base'] = array(
        '#type' => 'details',
        '#title' => t('Set Search Base & Filter'),
        '#open' => FALSE,
    );

    $possible_search_bases = $ldap_connect->getSearchBases();
    $possible_search_bases_in_key_val = [];
    foreach ($possible_search_bases as $search_base) {
      $possible_search_bases_in_key_val[$search_base] = $search_base;
    }
    $possible_search_bases_in_key_val['custom_base'] = 'Provide Custom LDAP Search Base';

    $form['review_config_set_filter_base']['miniorange_search_base_options'] = [
        '#type' => 'value',
        '#value' => $possible_search_bases_in_key_val,
    ];

    $form['review_config_set_filter_base']['miniorange_ldap_custom_sb_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Other Search Base(s):'),
        '#default_value' => empty($config->get('miniorange_ldap_custom_sb_attribute')) ? reset($possible_search_bases_in_key_val) : $config->get('miniorange_ldap_custom_sb_attribute'),
        '#states' => ['visible' => [':input[name = "search_base_attribute"]' => ['value' => 'custom_base']]],
        '#attributes' => ['style' => 'width:65%;'],
        '#maxlength' => 1024,
    ];

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

    $form['review_config_set_filter_base']['miniorange_search_base_options']['search_base_attribute'] = [
        '#id' => 'miniorange_ldap_search_base_attribute',
        '#title' => t('Search Base(s):'),
        '#type' => 'select',
        '#default_value' => $config->get('miniorange_ldap_search_base'),
        '#options' => $form['review_config_set_filter_base']['miniorange_search_base_options']['#value'],
        '#attributes' => ['style' => 'width:65%;height:30px'],
        '#description' => t('Search Base indicates the location in your LDAP server where your users reside. Select the Distinguished Name(DN) of the Search Base object from the above dropdown.<br>Multiple Search Bases are supported in the <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing"><strong>[Premium, All-Inclusive]</strong></a> version of the module.'),
        ];

    $form['review_config_set_filter_base']['ldap_auth']['settings']['username_attribute'] = [
        '#id' => 'miniorange_ldap_username_attribute',
        '#title' => t('LDAP Username Attribute / Search Filter:'),
        '#type' => 'select',
        '#default_value' => $config->get('miniorange_ldap_username_attribute_option'),
        '#options' => $ldap_attribute_option,
        '#attributes' => ['style' => 'width:65%;height:30px'],
        '#description' => t('Select the LDAP attribute by which the user will be searched in the LDAP server. Using this LDAP attribute value your user can login to Drupal.<br> <b>For example:</b> If you want the user to login to Drupal using their email address( the one present in the LDAP server), you can select <b>mail</b> in the dropdown.<br>You can even search for your user using a Custom Search Filter in the <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing"><strong>[Premium, All-Inclusive]</strong></a> version of the module<div><br>'
        ),
    ];

    $form['review_config_set_filter_base']['miniorange_ldap_custom_username_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Other LDAP Username Attribute:'),
        '#default_value' => empty($config->get('miniorange_ldap_custom_username_attribute')) ? 'samaccountname' : $config->get('miniorange_ldap_custom_username_attribute'),
        '#states' => ['visible' => [':input[name = "username_attribute"]' => ['value' => 'custom']]],
        '#attributes' => ['style' => 'width:65%;height:30px'],
    ];

    // Email attribute
    $saved_email_attribute = $config->get('miniorange_ldap_email_attribute');

    $form['review_config_set_filter_base']['miniorange_ldap_email_attribute'] = [
        '#type' => 'select',
        '#title' => t('LDAP Email Attribute:'),
        '#options' => $ldap_attribute_option,
        '#required' => false,
        '#attributes' => [
            'style' => 'width:65%;',
        ],
        '#default_value' => $saved_email_attribute != NULL && in_array($saved_email_attribute,$ldap_attribute_option)? $saved_email_attribute : 'custom',
        '#description' => t("Select the LDAP attribute in which you get the email address of your LDAP users."),
    ];

    $form['review_config_set_filter_base']['miniorange_ldap_custom_email_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Other LDAP Email Attribute:'),
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
    $form['review_config_set_filter_base']['miniorange_ldap_photo_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Image/Profile Attribute'),
        '#disabled' => TRUE,
        '#attributes' => [
            'style' => 'width:65%;',
            'placeholder' => t('Enter image attribute eg. jpegphoto, thumbnailphoto'),
        ],
        '#description' => t("Enter the LDAP attribute in which you get the profile photo/image of your users. <a href='#'><b>[All-Inclusive]</b></a>"),
    ];

    $form['review_login_settings_config'] = array(
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => t('User Login Settings'),
    );
    $form['review_login_settings_config']['miniorange_ldap_enable_ldap'] = [
        '#type' => 'checkbox',
        '#title' => t('Enable Login with LDAP '),
        '#default_value' => $config->get('miniorange_ldap_enable_ldap'),
    ];
    $form['review_login_settings_config']['miniorange_ldap_enable_auto_reg'] = [
        '#type' => 'checkbox',
        '#disabled' => 'true',
        '#title' => t('Enable Auto Creation of users if they do not exist in Drupal <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing"><strong>[Premium, All-Inclusive]</strong></a>'),
        '#default_value' => $config->get('miniorange_ldap_enable_auto_reg'),
    ];
    $form['review_login_settings_config']['set_of_radiobuttons']['miniorange_ldap_authentication'] = [
        '#type' => 'radios',
        '#disabled' => TRUE,
        '#title' => t('Authentication restrictions: <a href="' . $base_url . '/admin/config/people/ldap_auth/Licensing">[Premium, All-Inclusive]</a>'),
        //'#description' => t('Only particular users will be able to login by selecting the above option.'),
        '#tree' => TRUE,
        '#default_value' => is_null($config->get('miniorange_ldap_authentication')) ? 0 : $config->get('miniorange_ldap_authentication'),
        '#options' => [
            0 => t('User can login using both their Drupal and LDAP credentials'),
            1 => t('User can login in Drupal using their LDAP credentials and Drupal admins can also login using their local Drupal credentials'),
            2 => t('Users can only login using their LDAP credentials'),
        ],
    ];

    $form['save_config_edit'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => t('Save Changes '),
        '#submit' => ['::miniorange_ldap_review_changes'],
        '#prefix' => '<br><div class="container-inline">'
    ];
    $form['reset_configuration'] = [
        '#type' => 'submit',
        '#value' => t('Reset Configurations'),
        '#submit' => ['::miniorange_ldap_back_2'],
    ];

    $form['miniorange_back_button'] = [
        '#type' => 'link',
        '#title' => t('&#171; Back'),
        '#attributes' => [
            'class' => [
                'button',
                'button--danger',
            ],
        ],
        '#url' => Url::fromRoute('ldap_auth.ldap_config'),
        '#suffix' => '</div><br><br>',
    ];

    return $form;
  }
}
