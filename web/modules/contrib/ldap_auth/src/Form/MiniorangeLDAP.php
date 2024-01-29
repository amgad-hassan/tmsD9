<?php


namespace Drupal\ldap_auth\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\LDAPLOGGERS;
use Drupal\ldap_auth\LDAPFlow;
use Drupal\ldap_auth\MiniorangeLDAPConstants;
use Drupal\ldap_auth\Utilities;
use Drupal\Component\Utility\Html;
use Drupal\ldap_auth\SetupNavbarHeader;
use Drupal\ldap_auth\TestConnectionFormBuilder;
use Drupal\ldap_auth\ContactLDAPServerFormBuilder;
use Drupal\ldap_auth\SearchBaseAndFilterFormBuilder;
use Drupal\ldap_auth\ReviewConfigFormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Drupal\ldap_auth\Form\LDAPFormBase;
use Drupal\ldap_auth\LoginSettingsFormBuilder;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 *
 */
class MiniorangeLDAP extends LDAPFormBase {

  /**
   *
   */
  public function getFormId() {
    return 'miniorange_ldap_config_client';
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    global $base_url;
    $ldap_connect = new LDAPFlow();
    $form['markup_library'] = [
        '#attached' => [
            'library' => [
                "ldap_auth/ldap_auth.admin",
                "ldap_auth/ldap_auth.test",
                "core/drupal.dialog.ajax"
            ],
        ],
    ];

    $this->config_factory->set('tab_name','LDAP Configuration')->save();

    if (!Utilities::isLDAPInstalled()) {
      $this->config_factory->set('miniorange_ldap_extension_enabled', FALSE)
          ->save();
      $form['markup_reg_msg'] = [
          '#markup' => $this->t('<div class="mo_ldap_enable_extension_message"><b>The PHP LDAP extension is not enabled.</b><br> Please Enable the PHP LDAP Extension for you server to continue. If you want, you refer to the steps given on the link  <a target="_blank" href="https://faq.miniorange.com/knowledgebase/how-to-enable-php-ldap-extension/" >here</a> to enable the extension for your server.</div><br>'),
      ];
    }
    else {
      $this->config_factory->set('miniorange_ldap_extension_enabled', TRUE)->save();
    }

    $is_configured = $this->config->get('miniorange_ldap_is_configured');

    $query_parameter = \Drupal::request()->query->get('action');

    global $base_url;
    if($query_parameter == 'disable' || $query_parameter == 'enable'){
      $this->config_factory->set('miniorange_ldap_enable_ldap',$query_parameter == 'enable')->save();
      $response = new RedirectResponse($base_url . "/admin/config/people/ldap_auth/ldap_config");
      $response->send();
      return new Response();
    }
    elseif ($query_parameter == 'delete'){
      self::miniorange_ldap_back_2($form, $form_state);
      $response = new RedirectResponse($base_url . "/admin/config/people/ldap_auth/ldap_config");
      $response->send();
      return new Response();
    }

    $form['ldap_css_classes'] = [
        '#markup' => '<div class="mo_ldap_table_layout_1">
                        <div class="mo_ldap_table_layout">',
    ];

    $status = $this->config->get('miniorange_ldap_config_status');

    if(!$is_configured && $status!='review_config' ){
      //show the normal steps to configure the module
      if ($status == '') {
        $status = 'two';
      }

      $config_step = $this->config->get('miniorange_ldap_steps');

      switch ($config_step) {
        case 0:
          $navbar_val = 3;
          break;

        case 1:
          $navbar_val = 25;
          break;

        case 2:
          $navbar_val = 51;
          break;

        case 3:
          $navbar_val = 78;
          break;

        case 4:
          $navbar_val = 100;
          break;

        default:
          $navbar_val = 1;
      }

      /**
       * builds and inserts the Navbar Headers
       */
      SetupNavbarHeader::insertForm($form, $form_state, $navbar_val);

      if ($status == 'one') {
        /**
         * Builds and inserts the Login Settings form
         */
        LoginSettingsFormBuilder::insertForm($form, $form_state, $this->config);
      }
      elseif ($status == 'two') {
        $form['mo_ldap_local_configuration_form_action'] = [
            '#markup' => "<input type='hidden' name='option' id='mo_ldap_local_configuration_form_action' value='mo_ldap_local_save_config'></input>",
        ];
        if ($this->config->get('miniorange_ldap_steps') != 1) {
          /**
           * builds and inserts the Contact LDAP Server Form
           */
          ContactLDAPServerFormBuilder::insertForm($form, $form_state, $this->config);
        }
        if ($this->config->get('miniorange_ldap_steps') == 1) {
          /**
           * builds and inserts the Test Connection Form
           */
          TestConnectionFormBuilder::insertForm($form, $form_state, $this->config);

        }
      }
      elseif ($status == 'three') {
        // Get all Search bases from AD.
        $possible_search_bases = $ldap_connect->getSearchBases();

        $possible_search_bases_in_key_val = [];
        foreach ($possible_search_bases as $search_base) {
          $possible_search_bases_in_key_val[$search_base] = $search_base;
        }
        $possible_search_bases_in_key_val['custom_base'] = 'Provide Custom LDAP Search Base';
        /**
         * Builds and inserts the Select Search Base and Filter Form
         */
        SearchBaseAndFilterFormBuilder::insertForm($form, $form_state, $this->config, $possible_search_bases_in_key_val);
      }
      elseif ($status == 'four') {
        /**
         * Builds and Inserts Test Authentication Form
         */
        TestConnectionFormBuilder::insertForm($form, $form_state, $this->config);
      }

    }
    else{
      if($query_parameter == null){
        //show the table list of the ldap servers
        self::showLDAPServersTable($form, $form_state,$this->config);
      }
      else if($query_parameter == 'edit'){
        $next_disabled = TRUE;
        if ($this->config->get('miniorange_ldap_test_conn_enabled') == 1) {
          $next_disabled = FALSE;
        }
          ReviewConfigFormBuilder::insertForm($form, $form_state, $this->config, $ldap_connect, $next_disabled);
      }
      else if($query_parameter == 'testing'){
        self::showLDAPTestAuthentication($form,$form_state,$this->config);
      }
    }

    $form['mo_markup_div_imp'] = ['#markup' => '</div>'];

    Utilities::addSupportButton( $form, $form_state);

    return $form;

  }

  public function miniorange_ldap_back_1($form, $form_state) {
    $this->config_factory->set('miniorange_ldap_config_status', 'one')->save();
  }

  /**
   *
   */
  public function miniorange_ldap_back_2($form, $form_state) {

    $this->config_factory->clear('miniorange_ldap_enable_ldap')
        ->clear('miniorange_ldap_authenticate_admin')
        ->clear('miniorange_ldap_authenticate_drupal_users')
        ->clear('miniorange_ldap_enable_auto_reg')
        ->clear('miniorange_ldap_server')
        ->clear('miniorange_ldap_server_account_username')
        ->clear('miniorange_ldap_server_account_password')
        ->clear('miniorange_ldap_search_base')
        ->clear('miniorange_ldap_username_attribute')
        ->clear('miniorange_ldap_test_username')
        ->clear('miniorange_ldap_test_password')
        ->clear('miniorange_ldap_server_address')
        ->clear('miniorange_ldap_enable_anony_bind')
        ->clear('miniorange_ldap_protocol')
        ->clear('miniorange_ldap_username_attribute_option')
        ->clear('ldap_binding_options')
        ->clear('miniorange_ldap_is_configured')
        ->clear('miniorange_ldap_user_attributes')->save();

    $this->config_factory->set('miniorange_ldap_server_port_number', '389')
        ->save();
    $this->config_factory->set('miniorange_ldap_custom_username_attribute', 'samaccountName')
        ->save();
    $this->config_factory->set('miniorange_ldap_config_status', 'two')->save();
    $this->config_factory->set('miniorange_ldap_steps', "0")->save();

    Utilities::add_message($this->t('Configurations removed successfully.'), 'status');
  }

  /**
   *
   */
  public function miniorange_ldap_back_3($form, $form_state) {
    $this->config_factory->set('miniorange_ldap_config_status', 'two')->save();
    $this->config_factory->set('miniorange_ldap_steps', "1")->save();
  }

  /**
   *
   */
  public function miniorange_ldap_back_5($form, $form_state) {
    $this->config_factory->set('miniorange_ldap_steps', "2")->save();
    $this->config_factory->set('miniorange_ldap_config_status', 'three')
        ->save();
  }

  /**
   *
   */
  public function miniorange_ldap_back_4($form, $form_state) {
    $this->config_factory->set('miniorange_ldap_config_status', 'four')->save();
  }

  /**
   *
   */
  public function miniornage_ldap_back_6($form, $form_state) {
    $this->config_factory->set('miniorange_ldap_config_status', 'three')
        ->save();
    $this->config_factory->set('miniorange_ldap_steps', "3")->save();
  }

  /**
   * Test Connection.
   */
  public function test_connection_ldap($form, $form_state) {

    $ldap_connect = new LDAPFlow();

    $form_values = $form_state->getValues();
    $ldapconn = $ldap_connect->getConnection();

    if($ldapconn){

      $server_account_username = trim($form_values['miniorange_ldap_server_account_username']);
      $server_account_password = $form_values['miniorange_ldap_server_account_password'];

      $this->config_factory->set("miniorange_ldap_server_account_username",$server_account_username)->save();
      $this->config_factory->set("miniorange_ldap_server_account_password",$server_account_password)->save();

      $bind = @ldap_bind($ldapconn,$server_account_username,$server_account_password);

      if($bind){
        if ($this->config->get('miniorange_ldap_steps') != '4') {
          $this->config_factory->set('miniorange_ldap_steps', "2")->save();
          $this->config_factory->set('miniorange_ldap_config_status', 'three')->save();
        }
        $this->config_factory->set('miniorange_ldap_test_connection','Successfull')->save();
        $this->messenger->addMessage(t("Test Connection is successful."));
      }
      else{
        $msg = 'Unable to make authenticated bind to LDAP server.[ '.ldap_error($ldapconn).' ( '.ldap_errno($ldapconn).' ) ]';
        if(ldap_errno($ldapconn) == -1){
          $msg = $msg.'<br> Make sure you have entered correct LDAP server hostname or IP address.If you need further assistance, donot hesitate to contact us at <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a>.';
        }

        $this->config_factory->set('miniorange_ldap_test_connection',ldap_error($ldapconn).' ['.ldap_errno($ldapconn)."]")->save();
        $this->messenger->addMessage(t($msg),'error');
      }

    }
    else{
      $msg = $this->t("Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address. <br>If there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your Drupal site IP address and below specified port number. <br>If you still face the same issue then contact us drupalsupport@xecurify.com");

      $this->config_factory->set('miniorange_ldap_test_connection',"Cannot contact to LDAP Server")->save();
      $this->messenger->addMessage($msg,'error');
    }

  }
  /**
   *
   */


  public function miniorange_ldap_next_1($form, $form_state) {

    $form_values = $form_state->getValues();
    $this->config_factory->set('miniorange_ldap_config_status', 'review_config')->save();
    $this->config_factory->set('miniorange_ldap_steps', "4")->save();
    $this->config_factory->set('miniorange_ldap_is_configured', 1)->save();
    $enable_ldap = $form_values['miniorange_ldap_enable_ldap'];

    $this->config_factory->set('miniorange_ldap_enable_ldap', $enable_ldap)->save();
    $message = 'Congratulations! You have successfully configured the module.<br>Now you can login to your Drupal site using the LDAP Credentials.<br>If you encounter any problems or need assistance, please do not hesitate to contact us at <a href="'.MiniorangeLDAPConstants::SUPPORT_EMAIL.'">'.MiniorangeLDAPConstants::SUPPORT_EMAIL.'</a>. ';
    Utilities::add_message(t($message),'status');
    $form_state->setRedirect('ldap_auth.ldap_config');
  }


  /**
   *
   */
  public function miniorange_ldap_next3($form, $form_state) {
    $this->config_factory->set('miniorange_ldap_config_status', 'one')->save();
    $form_values = $form_state->getValues();

    if (!empty($form['search_base_attribute']['#value'])) {
      $searchBase = $form['search_base_attribute']['#value'];
      $searchBaseCustomAttribute = NULL;
      if ($searchBase == 'custom_base') {
        $this->config_factory->set('miniorange_ldap_username_attribute_option', 'custom')
            ->save();
        $searchBaseCustomAttribute = trim($form['miniorange_ldap_custom_sb_attribute']['#value']);
      }
      $ldap_connect = new LDAPFlow();
      $ldap_connect->setSearchBase($searchBase, $searchBaseCustomAttribute);
      $this->config_factory->set('miniorange_ldap_steps', "3")->save();
    }

    $email_attribute = $form_values['miniorange_ldap_email_attribute'] == 'custom' ? trim($form_values['miniorange_ldap_custom_email_attribute']) : $form_values['miniorange_ldap_email_attribute'];
    $email_attribute = empty($email_attribute) ? 'mail' : $email_attribute;
    $this->config_factory->set('miniorange_ldap_email_attribute', $email_attribute)->save();

    if (!empty($form['ldap_auth']['settings']['username_attribute']['#value'])) {
      $usernameAttribute = $form['ldap_auth']['settings']['username_attribute']['#value'];
      $usernameCustomAttribute = NULL;
      if ($usernameAttribute == 'custom') {
        $this->config_factory->set('miniorange_ldap_username_attribute_option', 'custom')
            ->save();
        $usernameCustomAttribute = trim($form['miniorange_ldap_custom_username_attribute']['#value']);
        if (trim($usernameCustomAttribute) == '') {
          $usernameCustomAttribute = 'samaccountName';
        }
        $this->config_factory->set('miniorange_ldap_custom_username_attribute', $usernameCustomAttribute)
            ->save();
        $ldap_connect->setSearchFilter($usernameCustomAttribute);
      }
      else {
        $this->config_factory->set('miniorange_ldap_username_attribute_option', $usernameAttribute)
            ->save();
        $ldap_connect->setSearchFilter($usernameAttribute);
      }
    }

    if (!empty($form['miniorange_ldap_test_username']['#value'])) {
      $testUsername = $form['miniorange_ldap_test_username']['#value'];
      $this->config_factory->set('miniorange_ldap_test_username', $testUsername)
          ->save();
    }

    if (!empty($form['miniorange_ldap_test_password']['#value'])) {
      $testPassword = $form['miniorange_ldap_test_password']['#value'];
      $this->config_factory->set('miniorange_ldap_test_password', $testPassword)
          ->save();
    }
  }

  /**
   *
   */
  public function miniorange_ldap_next_4($form, $form_state) {
    $this->config_factory->set('miniorange_ldap_config_status', 'review_config')
        ->save();
    $this->config_factory->set('miniorange_ldap_steps', "4")->save();

    Utilities::add_message(t('Configuration updated successfully. <br><br>Now please open a private/incognito window and try to login to your Drupal site using your LDAP credentials. In case you face any issues or if you need any sort of assistance, please feel free to reach out to us at <u><a href="mailto:drupalsupport@xecurify.com"><i>drupalsupport@xecurify.com</i></a></u>'), 'status');
  }

  /**
   *
   */
  public function miniorange_ldap_next_x(&$form, &$form_state) {
    $this->config_factory->set('miniorange_ldap_config_status', 'three')
        ->save();
    $this->config_factory->set('miniorange_ldap_steps', "2")->save();
  }

  public function back_to_contact_server(&$form, &$form_state) {
    $this->config_factory->set('miniorange_ldap_config_status', 'two')
        ->save();
    $this->config_factory->set('miniorange_ldap_steps', "0")->save();
  }

  /**
   * Contact LDAP server.
   */
  public function test_ldap_connection($form, $form_state) {

    global $base_url;
    LDAPLOGGERS::addLogger('L101: Entered Contact LDAP Server ', '', __LINE__, __FUNCTION__, __FILE__);

    if (!Utilities::isLDAPInstalled()) {
      LDAPLOGGERS::addLogger('L102: PHP_LDAP Extension is not enabled', '', __LINE__, __FUNCTION__, __FILE__);
      Utilities::add_message(t('You have not enabled the PHP LDAP extension'), 'error');
      return;
    }

    $form_values = $form_state->getValues();

    $server_address = "";

    if (!empty(trim($form_values['miniorange_ldap_server_address']))) {
      $server_address = Html::escape(trim($form_values['miniorange_ldap_server_address']));
    }
    else{
      Utilities::add_message(t('LDAP Server Address can not be empty.'), 'error');
      return;
    }

    if (isset($form_values['miniorange_ldap_protocol']) && !empty($form_values['miniorange_ldap_protocol'])) {
      $protocol = Html::escape($form_values['miniorange_ldap_protocol']);
    }

    $server_name = $protocol . $server_address;

    if (!empty(trim($form_values['miniorange_ldap_server_port_number']))) {
      $port_number = Html::escape(trim($_POST['miniorange_ldap_server_port_number']));
      $server_name = $server_name . ':' . $port_number;
    }
    else{
      Utilities::add_message(t('LDAP Server Address Port can not be empty.'), 'error');
      return;
    }


    $this->config_factory->set('miniorange_ldap_server', $server_name)->save();
    $this->config_factory->set('miniorange_ldap_server_address', $server_address)->save();
    $this->config_factory->set('miniorange_ldap_protocol', $protocol)->save();
    $this->config_factory->set('miniorange_ldap_server_port_number', $port_number)->save();

    $ldap_connect = new LDAPFlow();
    $ldap_connect->setServerName($server_name);

    $ldapconn = $ldap_connect->getConnection();
    LDAPLOGGERS::addLogger('DL1: ldapconn getConnection: ', $ldapconn, __LINE__, __FUNCTION__, __FILE__);

    if ($ldapconn) {

      //checking anonymous bind
      $anonymous_bind = @ldap_bind($ldapconn);

      if ($anonymous_bind) {
        $this->config_factory->set("supports_anonymous_bind",1)->save();
      }
      else{
        $this->config_factory->set("supports_anonymous_bind",0)->save();
      }

      if ($this->config->get('miniorange_ldap_steps') != '4') {
        $this->config_factory->set('miniorange_ldap_steps', "1")->save();
      }


      $this->config_factory->set('miniorange_ldap_contacted_server', "Successful")->save();
      $this->config_factory->set('miniorange_ldap_test_conn_enabled', "1")->save();
      $this->messenger->addMessage("Congratulations! You are successfully able to connect to your LDAP Server.",'status');
    }
    else {

      $this->config_factory->set('miniorange_ldap_contacted_server', "Failed")->save();
      $this->config_factory->set('miniorange_ldap_test_conn_enabled', "0")->save();

      $msg = $this->t("Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address. <br>If there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your Drupal site IP address and below specified port number. <br>If you still face the same issue then contact us <a href='mailto::drupalsupport@xecurify.com'>drupalsupport@xecurify.com</a>.");
      $this->messenger->addMessage($msg,'error');
    }

  }

  /**
   *
   */
  public function miniorange_ldap_review_changes($form, $form_state) {
    $ldap_connect = new LDAPFlow();

    $form_values = $form_state->getValues();
    $this->config_factory->set('miniorange_ldap_enable_ldap', $form_values['miniorange_ldap_enable_ldap'])->save();

    if(!empty(trim($form_values['miniorange_ldap_server_review']))){
      $this->config_factory->set("miniorange_ldap_server",trim($form_values['miniorange_ldap_server_review']))->save();
    }

    if(!empty($form_values['miniorange_ldap_server_account_username'])){
      $this->config_factory->set('miniorange_ldap_server_account_username', $form_values['miniorange_ldap_server_account_username'])
          ->save();
    }
    if(!empty($form_values['miniorange_ldap_server_account_password'])){
      $this->config_factory->set('miniorange_ldap_server_account_password', $form_values['miniorange_ldap_server_account_password'])
          ->save();
    }

    if (!empty($form_values['search_base_attribute'])) {
      $searchBase = $form_values['search_base_attribute'];
      if ($searchBase == 'custom_base') {
        $this->config_factory->set('miniorange_ldap_username_attribute_option', 'custom')
            ->save();
        $this->config_factory->set('miniorange_ldap_custom_sb_attribute', trim($form_values['miniorange_ldap_custom_sb_attribute']))
            ->save();
        $ldap_connect->setSearchBase($searchBase, trim($form_values['miniorange_ldap_custom_sb_attribute']));
      }
      else {
        $this->config_factory->set('miniorange_ldap_search_base', $searchBase)
            ->save();
        $ldap_connect->setSearchBase($searchBase);
      }
    }

    if (!empty($form_values['username_attribute'])) {
      $usernameAttribute = $form_values['username_attribute'];
      if ($usernameAttribute == 'custom') {
        $this->config_factory->set('miniorange_ldap_username_attribute_option', 'custom')->save();
        $usernameCustomAttribute = trim($form_values['miniorange_ldap_custom_username_attribute']);
        if (trim($usernameCustomAttribute) == '') {
          $usernameCustomAttribute = 'samaccountName';
        }
        $this->config_factory->set('miniorange_ldap_custom_username_attribute', $usernameCustomAttribute)->save();
        $this->config_factory->set('miniorange_ldap_username_attribute', $usernameCustomAttribute)->save();
        $ldap_connect->setSearchFilter($usernameCustomAttribute);
      }
      else {
        $this->config_factory->set('miniorange_ldap_username_attribute_option', $usernameAttribute)
            ->save();
        $this->config_factory->set('miniorange_ldap_username_attribute', $usernameAttribute)
            ->save();
        $ldap_connect->setSearchFilter($usernameAttribute);
      }
    }

    //email attribute saving
    $email_attribute = $form_values['miniorange_ldap_email_attribute'] == 'custom' ? trim($form_values['miniorange_ldap_custom_email_attribute']) : $form_values['miniorange_ldap_email_attribute'];
    $email_attribute = empty($email_attribute) ? 'mail' : trim($email_attribute);
    $this->config_factory->set('miniorange_ldap_email_attribute', $email_attribute)->save();

    $this->config_factory->set('miniorange_ldap_steps', "4")->save();
    Utilities::add_message(t('Configuration updated successfully.'), 'status');
    $form_state->setRedirect('ldap_auth.ldap_config');
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Contact LDAP server.
   */
  public function test_ldap_connection_review($form, $form_state) {
    global $base_url;
    LDAPLOGGERS::addLogger('LR101: Entered Review Contact LDAP Server ', '', __LINE__, __FUNCTION__, __FILE__);

    if (!Utilities::isLDAPInstalled()) {
      LDAPLOGGERS::addLogger('LR102: PHP_LDAP Extension is not enabled', '', __LINE__, __FUNCTION__, __FILE__);
      Utilities::add_message(t('You have not enabled the PHP LDAP extension'), 'error');
      return;
    }

    $server_name = "";
    $anony_bind = "";
    if (isset($_POST['miniorange_ldap_server_review']) && !empty($_POST['miniorange_ldap_server_review'])) {
      $server_name = Html::escape(trim($_POST['miniorange_ldap_server_review']));
    }
    if (trim($server_name) == '') {
      Utilities::add_message(t('LDAP Server Address can not be empty.'), 'error');
      return;
    }


    $this->config_factory->set('miniorange_ldap_server', $server_name)->save();
    $this->config_factory->set('miniorange_ldap_enable_anony_bind', $anony_bind)->save();

    $ldap_connect = new LDAPFlow();
    $ldap_connect->setServerName($server_name);
    $ldapconn = $ldap_connect->getConnection();
    LDAPLOGGERS::addLogger('DLR1: ldapconn getConnection: ', $ldapconn, __LINE__, __FUNCTION__, __FILE__);
    if ($ldapconn) {
      if ($this->config->get('miniorange_ldap_steps') != '4') {
        $this->config_factory->set('miniorange_ldap_steps', "1")->save();
      }
      $this->config_factory->set('miniorange_ldap_contacted_server', "Successful")
          ->save();
      $this->config_factory->set('miniorange_ldap_test_conn_enabled', "1")
          ->save();
      Utilities::add_message(t('Congratulations, you were able to successfully connect to your LDAP Server'), 'status');
      return;
    }
    else {
      $this->config_factory->set('miniorange_ldap_contacted_server', "Failed")
          ->save();
      $this->config_factory->set('miniorange_ldap_test_conn_enabled', "0")
          ->save();
      Utilities::add_message(t('There seems to be an error trying to contact your LDAP server. Please check your configurations or contact the administrator for the same.'), 'error');
      return;
    }
  }

  /**
   * Show the ldap server table
   */

  public static function showLDAPServersTable(array &$form, FormStateInterface $form_state,$config = null){

    $caption = Markup::create('<div style="display: flex;justify-content: space-between;"><h3>Configured LDAP server</h3><span><a class="button button--primary use-ajax" data-dialog-options="{&quot;width&quot;:&quot;55%&quot;}"
data-dialog-type="modal" href="requestSupport/addLdapServer">+ Add LDAP Server</a></span></div><br>');
    $header = [
            'ldap_server'=> [
                'data' => t('LDAP Server')
              ],
            'service_account' => [
                'data' => t('Service Account')
              ],
            'status' => [
                'data' => t('LDAP Login')
              ],
            'test' => [
                'data' => t('Test')
              ],
            'action' => [
                'data' => t('Action')
              ],
    ];
    global $base_url;
    $server_url = $config->get('miniorange_ldap_server') ?? 'Not configured';
    $service_account = $config->get('miniorange_ldap_server_account_username') ?? 'No Account Found';
    $service_account = empty($service_account) && $config->get('supports_anonymous_bind') ? 'Anonymous Bind' : $service_account;

    $ldap_enabled = $config->get('miniorange_ldap_enable_ldap') ? 'Enabled' : 'Disabled';
    $test_button = [
        '#type' => 'link',
        '#title' => t('Test Authentication'),
        '#attributes' => [
            'class' => [
                'button',
                'button--primary',
                'button--small',
            ],
        ],
        '#url' => Url::fromUri($base_url.'/admin/config/people/ldap_auth/ldap_config?action=testing'),
    ];

    $status_title = $config->get('miniorange_ldap_enable_ldap') ? 'Disable' : 'Enable';
    //todo add the kerberos attirbute mapping role mapping section.
    $drop_button = [
        '#type' => 'dropbutton',
        '#dropbutton_type' => 'small',
        '#links' => [
            'edit' => [
                'title' => t('Edit'),
                'url' => Url::fromUri($base_url.'/admin/config/people/ldap_auth/ldap_config?action=edit'),
            ],
            'delete' => [
                'title' => t('Delete'),
                'url' => Url::fromUri($base_url.'/admin/config/people/ldap_auth/ldap_config?action=delete'),
            ],
            'status' => [
                'title' => t($status_title),
                'url' => Url::fromUri($base_url.'/admin/config/people/ldap_auth/ldap_config?action='.strtolower($status_title)),
            ],
           'ldap_sso' => [
               'title' => t('SSO/Windows Auto Login'),
               'url' => Url::fromUri($base_url.'/admin/config/people/ldap_auth/signin_settings#windows_auto_login'),
           ],
           'ldap_import' => [
                'title' => t('Import LDAP Users'),
                'url' => Url::fromUri($base_url.'/admin/config/people/ldap_auth//user_sync#import_ldap_users'),
            ],
        ],
    ];

    $rows= [
        [
           'ldap_server' => $server_url,
           'service_account' => $service_account,
           'status' => $ldap_enabled,
            'test' => [
                'data' => $test_button
              ],
            'action' => [
               'data' => $drop_button
           ],
        ],
    ];

    $form['ldap_server_list_table'] = [
        '#type' => 'table',
        '#caption' => $caption,
        '#header' => $header,
        '#rows'  => $rows,
    ];

    return $form;
  }

  public static function showLDAPTestAuthentication(array &$form, FormStateInterface $form_state,$config = null){

    $ldap_conn = new LDAPFlow();
    $search_base = $ldap_conn->getSearchBase();
    $filter = $ldap_conn->getSearchFilter();
    $ldapServer = $ldap_conn->getServerName();
    global $base_url;

    $form['review_test_authentication_config'] = array(
        '#type' => 'fieldset',
    );
    $form['review_test_authentication_config']['miniorange_ldap_testuser'] = [
        '#markup' => t("<div id='test_authentication'><h4>Test Authentication</h4></div><hr>
            <div class='mo_ldap_highlight_background_note_1'>Please enter user's LDAP username and password to test your configurations. The user will be searched based on your search filter i.e <b>$filter</b> of the user present under the search base <b>$search_base</b></div>
            "),
    ];

    $form['review_test_authentication_config']['miniorange_ldap_test_account_username'] = [
        '#type' => 'textfield',
        '#title' => t('Username:'),
        '#id' => 'miniorange_ldap_test_account_username',
        '#default_value' => $config->get('mo_last_authenticated_user'),
    ];

    $form['review_test_authentication_config']['miniorange_ldap_test_account_password'] = [
        '#type' => 'password',
        '#title' => t('Password:'),
        '#id' => 'miniorange_ldap_test_account_password',
    ];

    $form['review_test_authentication_config']['miniorange_test_configuration'] = [
        '#type' => 'submit',
        '#prefix' => "<br>",
        '#value' => t('Test Authentication'),
        '#attributes' => [
            'onclick' => 'ldap_testConfig()',
            'class' => ['use-ajax'],
        ],
        '#ajax' => ['event' => 'click'],
    ];

    $form['review_test_authentication_config']['miniorange_test_back_button'] = [
        '#type' => 'link',
        '#title' => t('&#171; Back'),
        '#url' => Url::fromRoute('ldap_auth.ldap_config'),
    ];

    return $form;

  }
}
