<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class LDAP extends Model {
	public static function auth( $username,$password ){
		$SearchFor = $username;
		$SearchField = "samaccountname";
		$ldapport = 389;
		
		$LDAPHost = "ldap-url.com";
		$dn = "OU=XXXXXXXX, DC=tap, DC=corp";
		$LDAPUserDomain = "@tap";

		$response['status'] = false;
		$response['message'] = 'Data tidak diproses';
		
		$LDAPUser = $username;
		$LDAPUserPassword = $password;
		$LDAPFieldsToFind = array("cn", "givenname","company", "samaccountname", "homedirectory", "telephonenumber", "mail");
		
		$cnx = ldap_connect($LDAPHost, $ldapport) or  $info = "Koneksi LDAP Gagal";
		if ( $cnx ) {
			ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);
			$bind = @ldap_bind( $cnx,$LDAPUser.$LDAPUserDomain,$LDAPUserPassword );
			if ( !$bind ) {
				$response['message'] = 'Username/Password salah';
				return $response;
				exit();
			}
		}

		$filter = "($SearchField=$SearchFor*)";
		$sr = ldap_search($cnx, $dn, $filter, $LDAPFieldsToFind);
		$info = ldap_get_entries($cnx, $sr);

		if ( $info['count'] > 0 ) {
			$response['status'] = true;
			$response['message'] = 'Login berhasil';
		}

		return $response;
		

	}
}
