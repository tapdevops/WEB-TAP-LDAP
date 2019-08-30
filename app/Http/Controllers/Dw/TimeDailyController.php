<?php
namespace App\Http\Controllers\Dw;
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

class TimeDailyController extends Controller {

	public function __construct() {
		# Set TAP_DW Connection
		$this->env = 'prod';
		$this->tap_dw = ( $this->env == 'prod' ? DB::connection( 'prod_tap_dw' ) : DB::connection( 'dev_tap_dw' ) );
	}

	public function get_active_date_min_7_days( Request $req ) {
		$response = array(
			"http_status_code" => 404,
			"message" => "Not found",
			"data" => array(
				"results" => array(),
				"error_message" => array()
			)
		);
		if ( $req->werks ) {
			$query = collect( $this->tap_dw->select( "
				SELECT
					COUNT( 1 ) AS JUMLAH_HARI
				FROM
					TAP_DW.TM_TIME_DAILY TD
				WHERE
					TD.TANGGAL BETWEEN SYSDATE-7 AND SYSDATE-1
					AND TD.WERKS = '{$req->werks}'
					AND TD.FLAG_HK = 'Y'
			" ) )->first();

			if ( !empty( $query ) ) {
				$response['http_status_code'] = 200;
				$response['message'] = "OK";
				$response['data']['results']['jumlah_hari'] = $query->jumlah_hari;
			}
		}

		return response()->json( $response );
	}

}