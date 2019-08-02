<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Session;
use Config;
use URL;
use Validator;
use App\LDAP;

class AuthController extends Controller {
	
	public function __construct() {
		// ..
	}

	public function login( Request $request ) {

		$response['status'] = false;
		$response['message'] = 'Validasi Gagal';

		$validator = Validator::make( $request->all(), [
			'username' => 'required|regex:/(^([a-zA-Z.]+)(\d+)?$)/u|max:64',
			'password' => 'required|max:64|'
		] );

		if ( $validator->fails() ) {
			$response['message'] = 'Validasi Gagal';
		}
		else {
			$LDAP = new LDAP();
			$LDAP_check = LDAP::auth( $request->username, $request->password );
			$response = $LDAP_check;
		}

		return response()->json( $response );

	}
	
	public function send_email_fams(Request $request)
	{
		$dt = $request->all();
		//$dt = 2;

		//$req = base64_decode($dt);
		//$data = unserialize($req);

		echo "2=<pre>"; db($dt); die();
		
		//$data = $req;
		echo json_encode($dt);
	}
	
	public function showToken() 
	{
		echo csrf_token(); 
    }

}