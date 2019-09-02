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

class TargetController extends Controller {

	public function __construct() {
		# Set TAP_DW Connection
		$this->env = 'prod';
		$this->tap_dw = ( $this->env == 'prod' ? DB::connection( 'prod_tap_dw' ) : DB::connection( 'dev_tap_dw' ) );
	}

	public function time_daily_get_active_date_min_7_days( Request $req ) {
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

	public function employee_sap_get_krani_buah( Request $req ) {
		$response = array(
			"http_status_code" => 404,
			"message" => "Not found",
			"data" => array(
				"results" => array(),
				"error_message" => array()
			)
		);
		if ( $req->werks_afd_code ) {
			$in_array = '\''.str_replace( ',', '\',\'', $req->werks_afd_code ).'\'';
			$query = collect( $this->tap_dw->select( "
				SELECT
					COUNT( 1 ) AS JUMLAH
				FROM
					TAP_DW.TM_EMPLOYEE_SAP
				WHERE
					SYSDATE BETWEEN START_VALID AND END_VALID
					AND JOB_CODE = 'KRANI BUAH'
					AND WERKS || AFD_CODE IN ( {$in_array} )
			" ) )->first();

			if ( !empty( $query ) ) {
				$response['http_status_code'] = 200;
				$response['message'] = "OK";
				$response['data']['results']['jumlah_hari'] = intval( $query->jumlah );
			}
		}

		return response()->json( $response );
	}

}