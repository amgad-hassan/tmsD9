<?php
/**
 * @file
 * Contains constants class.
 */

/**
 * @file
 * This class represents constants used throughout project.
 */
namespace Drupal\ldap_auth;

class MiniorangeLDAPConstants {
  const BASE_URL = 'https://login.xecurify.com';
  const SUPPORT_EMAIL = 'drupalsupport@xecurify.com';
  const SUPPORT_NAME = 'drupalsupport';

  //guide list
  const LDAP_PROVISIONING = 'https://www.drupal.org/docs/contributed-modules/ldap-integration/ldap-password-sync';
  const ATTRIBUTE_MAPPING = 'https://plugins.miniorange.com/drupal-ldap/map-ldap-attributes-to-drupal-fields';
  const GROUP_MAPPING_GUIDE = 'https://www.drupal.org/docs/contributed-modules/ldap-integration/ldap-groups-to-drupal-groups-mapping';
  const ROLE_MAPPING_GUIDE = "https://www.drupal.org/docs/contributed-modules/ldap-integration/ldap-user-role-mapping";
  const USER_SYNC_GUIDE = 'https://www.drupal.org/docs/contributed-modules/ldap-integration/ldap-sync-and-provisioning';
  const IMPORT_USERS = 'https://www.drupal.org/docs/contributed-modules/ldap-integration/import-users-from-ldap';
  const NTLM_KERBEROS_GUIDE = 'https://www.drupal.org/docs/contributed-modules/ldap-integration/ntlm-kerberos-authentication';

  const NTLM_KERBEROS_CASE_STUDY = 'https://www.drupal.org/case-study/integrated-windows-authentication-iwa';

  const REDIRECT_USER = 'https://plugins.miniorange.com/drupal-ldap/user-redirection-after-login-and-logout';

  const DRUPAL_TRIAL = 'https://drupaltrial.xecurify.com/drupal';

  //Feature list
  const LDAP_AUTHENTICATION = "Unlimited Authentication via LDAP";
  const LDAP_DIRECTORY = "LDAP Directory Configuration";
  const LDAP_SEARCH_FILTER = "Search user in LDAP (search filter)";
  const LDAP_SEARCH_BASE = "Search users under Search Base (like DC,OU)";
  const LDAP_ATTRIBUTE_MAPPING = "Attribute Mapping";
  const LDAP_CUSTOM_INTEGRATION = "Support for Custom Integration";
  const LDAP_AUTOCREATE_USER= "Auto Create Users in Drupal";
  const LDAP_ROLE_MAPPING= "Role Mapping";
  const LDAP_GROUP_RESTRICTION = "LDAP Group Restriction";
  const LDAP_TLS_CONNECTION = "TLS Connection";
  const LDAP_KERBEROS = "NTLM & Kerberos Authentication";
  const LDAP_GROUP_MAPPING = "Group mapping";
  const LDAP_REDIRECT = "Redirect user after login and logout";
  const LDAP_PAGE_RESTRICTION = "Page Restriction";
  const LDAP_IMPORT_USER= "Import users from LDAP server";
  const LDAP_SYNC= "Password and Directory Sync";

  //videos list
  const LDAP_IMPORT_VIDEO = 'https://youtu.be/T7yDZsY-HrM?si=FQbr6E5CvtZklprq';


}