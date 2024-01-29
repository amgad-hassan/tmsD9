<?php

namespace Drupal\ldap_auth\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\AuditAndLogs;
use Drupal\ldap_auth\Utilities;
use Drupal\ldap_auth\MiniorangeLDAPConstants;

/**
 *
 */
class AttributeMapping extends LDAPFormBase {

  /**
   *
   */
  public function getFormId() {
    return 'miniorange_ldap_attrmapping';
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['markup_library'] = [
        '#attached' => [
            'library' => [
                "ldap_auth/ldap_auth.admin",
                "ldap_auth/ldap_auth.testconfig",
                "core/drupal.dialog.ajax"
            ],
        ],
    ];
    $this->config_factory->set('tab_name','Attribute & Role Mapping')->save();
    $form['markup_top'] = [
        '#markup' => t('<div class="mo_ldap_table_layout_1"><div class="mo_ldap_table_layout container" >
          <span><h2>Attribute Mapping <a class="button button--primary button--small" style="float:right;margin: 1%;" href ='.MiniorangeLDAPConstants::ATTRIBUTE_MAPPING.' target="_blank">&#128366;  How to Perform Mapping</a></h2></span><hr>'),
    ];

    $form['miniorange_ldap_email_attribute'] = [
        '#type' => 'textfield',
        '#title' => t('Email Attribute'),
        '#required' => TRUE,
        '#attributes' => [
            'style' => 'width:700px;',
            'placeholder' => t('Enter email attribute eg. mail, userprincipalname'),
        ],
        '#default_value' => $this->config->get('miniorange_ldap_email_attribute'),
        '#description' => t("Enter the LDAP attribute in which you get the email address of your users."),
    ];

    $form['miniorange_ldap_mapping_submit'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => t('Save Configuration'),
        '#suffix' => '<br>',
    ];

    $form['markup_idp_user_attr_header'] = [
        '#markup' => '<br><h5>Mapping User Attributes &nbsp;&nbsp;</h5> ',
    ];


    $form['markup_cam'] = [
        '#markup' => '<div class="mo_ldap_highlight_background_note_1"><small>1. Select Drupal user field :  Select the user field name from the dropdown.<br>2. Select LDAP Attribute : Select the LDAP Attribute Name from the dropdown which you want to assign to the corresponding selected Drupal user field. 
            
            <br>    <b>   For example: </b> If you select the <i>mail</i> from Drupal user field and <i>userprincipalname</i> from LDAP attributes then users email in Drupal will be updated as per its userprincipalname value in the LDAP server upon LDAP Authentication. <br>
            This feature is available in the <a href="' . $this->base_url . '/admin/config/people/ldap_auth/Licensing">Premium, All-Inclusive</a> version of the module.
            </small></div><br>',
    ];

    $form['info'] = [
        '#type' => 'markup',
        '#markup' => $this->t('<small><strong>NOTE</strong> : This mapping will be useful for both the cases : <br> <ul><li>Mapping from <b>LDAP</b> <span style="font-size:25px;">&#8594;</span> Drupal</li><li>Mapping from Drupal <span style="font-size:25px;">&#8594;</span> <b>LDAP</b> <a href="'.$this->base_url.'/admin/config/people/ldap_auth/user_sync"><b>[LDAP Provisioning]</b></a></li></ul></small>'),
    ];

    $allDrupalFieldsOption['select'] = '-Select-';
    $allDrupalFields =  \Drupal::service('entity_field.manager')->getFieldStorageDefinitions('user', 'user');

    foreach ($allDrupalFields as $field_name => $field_object){
      $allDrupalFieldsOption[$field_name] = $field_name;
    }

    $allLDAPAttributeOption['select'] = '-select ldap attribute-';
    unset($allDrupalFieldsOption['uid']);
    unset($allDrupalFieldsOption['uuid']);
    unset($allDrupalFieldsOption['roles']);
    unset($allDrupalFieldsOption['init']);
    unset($allDrupalFieldsOption['access']);

    $row = [];

    $row['drupal_attribute'] = [
        '#type' => 'select',
        '#options' => $allDrupalFieldsOption
    ];

    $row['ldap_attribute'] = [
        '#type' => 'select',
        '#options' => $allLDAPAttributeOption,
        '#disabled' => true,
    ];

    $row['button'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
        '#button_type' => 'primary',
        '#disabled' => true,
    ];

    $form['attribute_table'] = [
        '#type' => 'table',
        '#responsive' => TRUE,
        '#sticky' => TRUE,
        '#header' => [
            $this->t("Select Drupal user field"),
            $this->t("Select LDAP Attribute"),
            $this->t("")
        ],
    ];

    $form['attribute_table']['row1'] = $row;

    $form['addRow'] = [
        '#type'        => 'submit',
        '#button_type' => 'primary',
        '#value'       => $this->t('<b>Add more</b>'),
        '#disabled' => true,
    ];

    $form['miniorange_ldap_gateway_config1_submit'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => t('Save Configuration'),
        '#disabled' => true,
        '#suffix' => '<br>',
    ];

    /**
     * User Role Mapping Feature
     */

    $form['role_mapping'] = [
        '#type' => 'details',
        '#title' => $this->t('LDAP Group/OU to Drupal Role Mapping   <a style="float: right" class="js-form-submit form-submit use-ajax" data-dialog-options="{&quot;width&quot;:&quot;40%&quot;}" data-dialog-type="modal"  href="'.$this->base_url.'/admin/config/people/ldap_auth/requestTrial?trial_feature=Role_Mapping">Try this feature!</a>'),
        "#disabled" => true,
    ];

    $form['role_mapping']['role_mapping_intro'] = [
        '#markup'     => $this->t('<div><div class="mo_ldap_highlight_background_note_1"> <small>With this mapping, you can assign Drupal Roles to your users based on the exisitng LDAP groups.<br>You can follow the step-by-step <a href="'.MiniorangeLDAPConstants::ROLE_MAPPING_GUIDE.'" target="_blank">Role Mapping guide</a> on Drupal.org to configure the Role mapping.<br><br>This feature is available in the <a href="' . $this->base_url . '/admin/config/people/ldap_auth/Licensing">Premium,All-Inclusive</a> version of the module.<br></small> </div><br>'),
    ];


    $form['role_mapping']['miniorange_ldap_enable_rolemapping'] = [
        '#type' => 'checkbox',
        '#title' => t('Check this option if you want to <b>Enable Role Mapping</b>'),
        '#description' => t('Enabling Role Mapping will automatically map Users from LDAP Groups to below selected Drupal Role.<br> Role mapping will not be applicable for primary admin of Drupal.'),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
        '#disabled' => TRUE,
    ];

    $form['role_mapping']['miniorange_ldap_disable_role_update'] = [
        '#type' => 'checkbox',
        '#title' => t("Check this option if you don't want to remove existing roles of users (New Roles will be added)"),
        '#disabled' => TRUE,
    ];

    $form['role_mapping']['miniorange_ldap_enable_ntlm_role_mapping'] = [
        '#type' => 'checkbox',
        '#disabled' => TRUE,
        '#title' => t('Enable Role Mapping for NTLM Users'),
        '#description' => t('Likewise Role Mapping, enabling this option automatically map NTLM user roles from LDAP Groups to below selected Drupal Role.'),
    ];

    $mrole = user_role_names($membersonly = TRUE);
    $drole = array_values($mrole);

    $form['role_mapping']['miniorange_ldap_default_mapping'] = [
        '#type' => 'select',
        '#title' => t('Select default role for the new users'),
        '#options' => $mrole,
        '#default_value' => $drole,
        '#attributes' => ['style' => 'width:45%;'],
        '#disabled' => FALSE,
    ];

    $form['role_mapping']['miniorange_ldap_memberOf'] = [
        '#type' => 'textfield',
        '#disabled' => TRUE,
        '#title' => t('LDAP Group Attribute Name'),
        '#attributes' => ['style' => 'width:45%;', 'placeholder' => 'memberOf'],
        '#description' => "LDAP attribute in which you will get your user's LDAP group. Default value is memberof"
    ];

    $row = [];
    $row['drupal_roles'] = [
        '#type' => 'select',
        '#options' => $mrole,
        '#disabled' => false,
    ];
    $row['ldap_group_dn'] = [
        '#type' => 'textfield',
        '#disabled' => true,
    ];

    $row['button'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
        '#button_type' => 'primary',
        '#disabled' => true,
    ];

    $form['role_mapping']['role_maping_table'] = [
        '#type' => 'table',
        '#responsive' => TRUE,
        '#sticky' => TRUE,
        '#header' => [
            $this->t("Select Drupal role"),
            $this->t("Enter LDAP Group DN"),
            $this->t("")
        ],
    ];

    $form['role_mapping']['role_maping_table']['row1'] = $row;

    $form['role_mapping']['role_mapping_addRow'] = [
        '#type'        => 'submit',
        '#button_type' => 'primary',
        '#value'       => $this->t('<b>Add more</b>'),
        '#disabled' => true,
    ];

    $form['role_mapping']['miniorange_ldap_rolemapping_submit'] = [
        '#type' => 'submit',
        '#value' => t('Save Configuration'),
        '#disabled' => TRUE,
    ];

    // Group mapping feature advertise

    $form['group_mapping'] = [
        '#type' => 'details',
        '#title' => $this->t('LDAP Group to Drupal Group Mapping  <a style="float: right" class="js-form-submit form-submit use-ajax"  data-dialog-options="{&quot;width&quot;:&quot;40%&quot;}" data-dialog-type="modal"  href="'.$this->base_url.'/admin/config/people/ldap_auth/requestTrial?trial_feature=Group_Mapping">Try this feature!</a>'),
        "#disabled" => true,
    ];

    $form['group_mapping']['intro'] = [
        '#markup'     => $this->t('<div><div class="mo_ldap_highlight_background_note_1"> <small>This mapping allows you to assign the Drupal groups to your users based on their existing LDAP groups.<br>You can create the drupal groups using the Drupal <a href="https://www.drupal.org/project/group" target="_blank">Group module</a>.<br>You can follow the step-by-step <a href="'.MiniorangeLDAPConstants::GROUP_MAPPING_GUIDE.'" target="_blank">Group mapping guide</a> on Drupal.org to configure the group mapping.<br><br>This feature is available in the <a href="' . $this->base_url . '/admin/config/people/ldap_auth/Licensing">All-Inclusive</a> version of the module.<br></small> </div><br>'),
    ];

    $form['group_mapping']['enable_group_mapping'] = [
        "#type" => 'checkbox',
        "#title" => $this->t("Enable Group mapping."),
        "#description" => $this->t("Enabling Group Mapping will automatically map Users from LDAP Groups to below mapped Drupal Group."),
    ];

    $form['group_mapping']['enable_group_mapping_ntlm'] = [
        "#type" => 'checkbox',
        "#title" => $this->t("Enable Group mapping for NTLM users."),
        "#description" => $this->t("Enabling Group Mapping will automatically map Users from LDAP Groups to below mapped Drupal Group in NTLM flow."),
        "#suffix" => "</div>",
    ];

    $row = [];
    $row['drupal_group'] = [
        '#type' => 'select',
        '#disabled' => true,
    ];
    $row['ldap_group'] = [
        '#type' => 'textfield',
        '#disabled' => true,
    ];
    $row['button'] = [
        '#type' => 'submit',
        '#value' =>'-',
        '#button_type' => 'primary',
        '#disabled' => true,
    ];

    $form['group_mapping']['group_table'] = [
        '#type' => 'table',
        '#responsive' => TRUE,
        '#sticky' => TRUE,
        '#disabled' => TRUE,
        '#header' => [
            $this->t("Drupal Group"),
            $this->t("LDAP Group DN"),
            $this->t("")
        ],
    ];

    $form['group_mapping']['group_table']['row1'] = $row;

    $form['group_mapping']['addRow'] = [
        '#type'        => 'submit',
        '#button_type' => 'primary',
        '#value'       => $this->t('<b>&#43;</b>'),
    ];

    $form['group_mapping']['save_attributes'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Save Group Mapping'),
        '#suffix' => "</div>",
    ];

    Utilities::addSupportButton( $form, $form_state);

    return $form;
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config_factory->set('miniorange_ldap_email_attribute', strtolower(trim($form_state->getValue('miniorange_ldap_email_attribute'))))->save();
    $this->messenger->addStatus($this->t('Attribute Mapping saved successfully.'));
  }

}