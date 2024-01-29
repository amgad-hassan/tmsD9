<?php
namespace Drupal\ldap_auth;

class Mo_Ldap_Auth_Response{

  public $status;

  public $statusMessage;

  public $userDn;

  public $attributeList;

  public $profileAttributesList;

  public $test_configuration_error;

  public function __construct(){
    //Empty constructor
  }

}

?>