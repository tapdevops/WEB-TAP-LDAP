<?php
namespace App\Http\Controllers;
set_time_limit(0);
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Session;
use Config;
use View;
use URL;
use Storage;
use File;

class ExportController extends Controller {
	
	protected $url;
	protected $auth;
	protected $access_token;
	
	public function __construct() {
		$this->env = 'development';
		$this->tap_dw = ( $this->env == 'production' ? DB::connection( 'prod_tap_dw' ) : DB::connection( 'dev_tap_dw' ) );
		$this->tapapps_mobile_estate = ( $this->env == 'production' ? DB::connection( 'prod_tapapps_mobile_estate' ) : DB::connection( 'dev_tapapps_mobile_estate' ) );
		$this->tapapps_mobile_inspection = ( $this->env == 'production' ? DB::connection( 'prod_tapapps_mobile_inspection' ) : DB::connection( 'dev_tapapps_mobile_inspection' ) );
		$this->access_token = Storage::get( 'files/access_token_mobile_inspection.txt' );
		$this->url = array(
			'report' => '',
			'development' => array(
				'auth' => 'http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-auth',
				// 'auth' => 'http://app.tap-agri.com/mobileinspection/ins-msa-auth',
				'hectare_statement' => 'http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-hectarestatement',
				//'inspection' => 'http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-inspection',
				'inspection' => 'http://app.tap-agri.com/mobileinspection/ins-msa-inspection',
				'report' => 'http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-reports',
				'ebcc_validation' => 'http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-ebccval',
				'images' => 'http://149.129.250.199:4012'
			),
			'qa' => array(
				'auth' => 'http://app.tap-agri.com/mobileinspectionqa/ins-msa-qa-auth',
				'hectare_statement' => 'http://app.tap-agri.com/mobileinspectionqa/ins-msa-qa-hectarestatement',
				'inspection' => 'http://app.tap-agri.com/mobileinspectionqa/ins-msa-qa-inspection',
				'report' => 'http://app.tap-agri.com/mobileinspectionqa/ins-msa-qa-reports',
				'ebcc_validation' => 'http://app.tap-agri.com/mobileinspectionqa/ins-msa-qa-ebccval',
				'images' => 'http://149.129.246.66:5012'
			),
			'production' => array(
				'auth' => 'http://app.tap-agri.com/mobileinspection/ins-msa-auth',
				'hectare_statement' => 'http://app.tap-agri.com/mobileinspection/ins-msa-hectarestatement',
				'inspection' => 'http://app.tap-agri.com/mobileinspection/ins-msa-inspection',
				'report' => 'http://app.tap-agri.com/mobileinspection/ins-msa-reports',
				'ebcc_validation' => 'http://app.tap-agri.com/mobileinspection/ins-msa-ebccval',
				'images' => 'http://149.129.245.230:3012'
			),
		);
		$this->auth = array(
			'username' => 'ferdinand',
			'password' => 'bakuljam',
			'imei' => ''
		);
	}

	public function test_kafka() {
		$this->tapapps_mobile_inspection->statement( "
			INSERT INTO TESTING VALUES ( 12, 'F0108190426095604' )
		" );
		$this->tapapps_mobile_inspection->commit();
		print "OK";
	}

	/**
	 * TAP_DW.TR_EBCC
	 *
	 * Untuk mengisi data Mobile Inspection di Database Oracle.
	 * --------------------------------------------------------------------------
	 */
		# Jangan Dihapus!
		# Untuk menjalankan sync tapdw.tr_inspection harian
		// public function sync_tapdw_tr_inspection_harian() {
		// 	$start_date = '2019-08-12'; # Tanggal Start
		// 	$end_date = '2019-08-12'; # Tanggal End
		// 	while (strtotime($start_date) <= strtotime($end_date)) {
		// 	    echo date( 'Ymd', strtotime( $start_date ) ).'<br />';
		// 	    self::sync_tapdw_tr_inspection( date( 'Ymd', strtotime( $start_date ) ) );
		// 	    $start_date = date ("Y-m-d", strtotime("+1 days", strtotime($start_date)));
		// 	}
		// }

		public function sync_tapdw_tr_inspection( ) {
			$client = new \GuzzleHttp\Client();

			# Generate Harian - 1 day
			$result = $client->request( 'GET', $this->url[$this->env]['inspection'].'/api/v1.0/export/tap-dw/tr-inspection/'.date( 'Ymd', strtotime( '-1 day' ) ).'000000/'.date( 'Ymd', strtotime( '-1 day' ) ).'235959', [
				'headers' => [
					'Authorization' => 'Bearer '.$this->access_token
				]
			] );

			# Generate dari fungsi sync_tapdw_tr_inspection_harian
			// $result = $client->request( 'GET', $this->url[$this->env]['inspection'].'/api/v1.0/export/tap-dw/tr-inspection/'.$date.'000000/'.$date.'235959', [
			// 	'headers' => [
			// 		'Authorization' => 'Bearer '.$this->access_token
			// 	]
			// ] );
			
			$result = json_decode( $result->getBody(), true );
			$result_content = $client->request( 'GET', $this->url[$this->env]['auth'].'/api/v1.0/content?GROUP_CATEGORY=INSPEKSI&CATEGORY=PERAWATAN&TM=YES', [
				'headers' => [
					'Authorization' => 'Bearer '.$this->access_token
				]
			] );
			$result_content = json_decode( $result_content->getBody(), true );
			$content = array();
			$content_code = array();
			foreach ( $result_content['data'] as $ct ) {
				array_push( $content_code, $ct['CONTENT_CODE'] );
				$content[$ct['CONTENT_CODE']] = array(
					"CONTENT_CODE" => $ct['CONTENT_CODE'],
					"CONTENT_CODE_REPLACE" => ( strlen( str_replace( 'CC000', '', $ct['CONTENT_CODE'] ) ) > 1 ? str_replace( 'CC00', '', $ct['CONTENT_CODE'] ) : str_replace( 'CC000', '', $ct['CONTENT_CODE'] ) ) ,
					"CONTENT_NAME" => strtoupper( $ct['CONTENT_NAME'] )
				);
			}

			// print '<pre>';
			// print_r( $result_content );
			// print '</pre>';
			// dd();
			
			$response = array();
			$response['message'] = 'TAP_DW.TR_INSPECTION';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['status'] = array(
				'TR_INSPECTION' => 'FAILED',
				'TR_INSPECTION_IMG' => 'FAILED',
				'TR_INSPECTION_PATH' => 'FAILED'
			);

			$csv_data['TR_INSPECTION'] = array();
			$csv_data['TR_INSPECTION_IMG'] = array();
			$csv_data['TR_INSPECTION_PATH'] = array();

			if ( $result['status'] == true && !empty( $result['data'] ) ) {
				foreach ( $result['data'] as $data ) {
						$result_user = $client->request( 'GET', $this->url[$this->env]['auth'].'/api/user/'.$data['INSERT_USER'], [
							'headers' => [
								'Authorization' => 'Bearer '.$this->access_token
							]
						] );
						$result_user = json_decode( $result_user->getBody(), true );

						if ( !empty( $result_user['data'] ) ) {
							$result_user = $result_user['data'];
						}

						$sql_hectare_statement = "SELECT 
								BLOCK.AFD_NAME, 
								BLOCK.BLOCK_NAME 
							FROM 
								TAP_DW.TM_BLOCK BLOCK
							WHERE 
								BLOCK.WERKS = '{$data['WERKS']}' AND 
								BLOCK.AFD_CODE = '{$data['AFD_CODE']}' AND 
								BLOCK.BLOCK_CODE = '{$data['BLOCK_CODE']}' AND 
								TO_CHAR( SYSDATE, 'DD-MM-RRRR' ) >= TO_CHAR( BLOCK.START_VALID, 'DD-MM-RRRR' ) AND 
								TO_CHAR( SYSDATE, 'DD-MM-RRRR' ) <= TO_CHAR( BLOCK.END_VALID, 'DD-MM-RRRR' ) AND 
								ROWNUM = 1";
						$hs = collect( $this->tap_dw->select( $sql_hectare_statement ) )->first();


						$result_img = $client->request( 'GET', $this->url[$this->env]['images'].'/images/'.$data['BLOCK_INSPECTION_CODE'], [
							'headers' => [
								'Authorization' => 'Bearer '.$this->access_token
							]
						] );
						$result_img = json_decode( $result_img->getBody(), true );

						# Jika detail ada, maka akan di set data untuk insert
						if ( !empty( $data['DETAIL'] ) ) {

							$insert[0]['NATIONAL'] = 'NATIONAL';
							$insert[0]['REGION_CODE'] = '0'.substr( $data['WERKS'], 0, 1 );
							$insert[0]['COMP_CODE'] = substr( $data['WERKS'], 0, 2 );
							$insert[0]['EST_CODE'] = substr( $data['WERKS'], 2, 2 );
							$insert[0]['WERKS'] = $data['WERKS'];
							$insert[0]['SUB_BA_CODE'] = NULL;
							$insert[0]['KEBUN_CODE'] = NULL;
							$insert[0]['AFD_CODE'] = $data['AFD_CODE'];
							$insert[0]['AFD_NAME'] = ( count( $hs ) > 0 ? $hs->afd_name : '' );
							$insert[0]['BLOCK_CODE'] = $data['BLOCK_CODE'];
							$insert[0]['BLOCK_NAME'] = ( count( $hs ) > 0 ? $hs->block_name : '' );
							$insert[0]['BLOCK_CODE_GIS'] = $data['WERKS'].$data['BLOCK_CODE'];
							$insert[0]['SUB_BLOCK_CODE'] = $data['BLOCK_CODE'];
							$insert[0]['SUB_BLOCK_NAME'] = ( count( $hs ) > 0 ? $hs->block_name : '' );
							$insert[0]['BLOCK_INSPECT_CODE'] = $data['BLOCK_INSPECTION_CODE'];
							$insert[0]['TANGGAL'] = date( 'd-m-Y H:i:s', strtotime( $data['INSPECTION_DATE'] ) );
							$insert[0]['CONTENT_INSPECT_CODE'] = '0';
							$insert[0]['CONTENT_NAME'] = NULL;
							$insert[0]['NILAI'] = $data['INSPECTION_RESULT'];
							$insert[0]['INSERT_USER'] = ( $result_user['EMPLOYEE_NIK'] ? $result_user['EMPLOYEE_NIK'] : '' );
							$insert[0]['EMP_NAME'] = ( $result_user['FULLNAME'] ? $result_user['FULLNAME'] : '' );
							$insert[0]['EMP_POSITION'] = ( $result_user['JOB'] ? $result_user['JOB'] : '' );
							$insert[0]['INSPECTION_TYPE'] = 'INSPECTION_RESULT';
							$insert[0]['INSERT_TIME_DW'] = '-';
							$insert[0]['UPDATE_TIME_DW'] = NULL;

							$i = 1;
							foreach ( $data['DETAIL'] as $detail ) {
								if ( in_array( $detail['CONTENT_INSPECTION_CODE'], $content_code ) ) {

									$value = '-';
									switch ( $detail['VALUE'] ) {
										case 'REHAB': $value = 'F'; break;
										case 'KURANG': $value = 'C'; break;
										case 'SEDANG': $value = 'B'; break;
										case 'BAIK': $value = 'A'; break;
									}

									$insert[$i]['NATIONAL'] = 'NATIONAL';
									$insert[$i]['REGION_CODE'] = '0'.substr( $data['WERKS'], 0, 1 );
									$insert[$i]['COMP_CODE'] = substr( $data['WERKS'], 0, 2 );
									$insert[$i]['EST_CODE'] = substr( $data['WERKS'], 2, 2 );
									$insert[$i]['WERKS'] = $data['WERKS'];
									$insert[$i]['SUB_BA_CODE'] = NULL;
									$insert[$i]['KEBUN_CODE'] = NULL;
									$insert[$i]['AFD_CODE'] = $data['AFD_CODE'];
									$insert[$i]['AFD_NAME'] = ( count( $hs ) > 0 ? $hs->afd_name : '' );
									$insert[$i]['BLOCK_CODE'] = $data['BLOCK_CODE'];
									$insert[$i]['BLOCK_NAME'] = ( count( $hs ) > 0 ? $hs->block_name : '' );
									$insert[$i]['BLOCK_CODE_GIS'] = $data['WERKS'].$data['BLOCK_CODE'];
									$insert[$i]['SUB_BLOCK_CODE'] = $data['BLOCK_CODE'];
									$insert[$i]['SUB_BLOCK_NAME'] = ( count( $hs ) > 0 ? $hs->block_name : '' );
									$insert[$i]['BLOCK_INSPECT_CODE'] = $data['BLOCK_INSPECTION_CODE'];
									$insert[$i]['TANGGAL'] = date( 'd-m-Y H:i:s', strtotime( $data['INSPECTION_DATE'] ) );
									$insert[$i]['CONTENT_INSPECT_CODE'] = $content[$detail['CONTENT_INSPECTION_CODE']]['CONTENT_CODE_REPLACE'];
									$insert[$i]['CONTENT_NAME'] = $content[$detail['CONTENT_INSPECTION_CODE']]['CONTENT_NAME'];
									$insert[$i]['NILAI'] = $value;
									$insert[$i]['INSERT_USER'] = ( $result_user['EMPLOYEE_NIK'] ? $result_user['EMPLOYEE_NIK'] : '' );
									$insert[$i]['EMP_NAME'] = ( $result_user['FULLNAME'] ? $result_user['FULLNAME'] : '' );
									$insert[$i]['EMP_POSITION'] = ( $result_user['JOB'] ? $result_user['JOB'] : '' );
									$insert[$i]['INSPECTION_TYPE'] = 'CONTENT_RESULT';
									$insert[$i]['INSERT_TIME_DW'] = '-';
									$insert[$i]['UPDATE_TIME_DW'] = NULL;
									$i++;
								}
							}

							if ( !empty( $result_img['data'] ) ) {
								$loop_img = count( $csv_data['TR_INSPECTION_IMG'] ) + 1;
								foreach ( $result_img['data'] as $img ) {
									$raw_image = array(
										'\'NATIONAL\'',
										'\'0'.substr( $data['WERKS'], 0, 1 ).'\'',
										'\''.substr( $data['WERKS'], 0, 2 ).'\'',
										'\''.substr( $data['WERKS'], 2, 2 ).'\'',
										'\''.$data['WERKS'].'\'',
										'NULL',
										'NULL',
										'\''.$data['AFD_CODE'].'\'',
										'\''.( count( $hs ) > 0 ? $hs->afd_name : '' ).'\'',
										'\''.$data['BLOCK_CODE'].'\'',
										'\''.( count( $hs ) > 0 ? $hs->block_name : '' ).'\'',
										'\''.$data['WERKS'].$data['BLOCK_CODE'].'\'',
										'\''.$data['BLOCK_CODE'].'\'',
										'\''.( count( $hs ) > 0 ? $hs->block_name : '' ).'\'',
										'\''.$data['BLOCK_INSPECTION_CODE'].'\'',
										'TO_DATE(\''.date( 'd-m-Y H:i:s', strtotime( $insert[0]['TANGGAL'] ) ).'\', \'DD-MM-RRRR HH24:MI:SS\')',
										'\''.$img['IMAGE_CODE'].'\'',
										'\''.$data['LONG_START_INSPECTION'].'\'',
										'\''.$data['LAT_START_INSPECTION'].'\'',
										'NULL',
										'\''.$insert[0]['INSERT_USER'].'\'',
										'\''.$insert[0]['EMP_NAME'].'\'',
										'\''.$insert[0]['EMP_POSITION'].'\'',
										'\''.$insert[0]['INSPECTION_TYPE'].'\'',
										'SYSDATE',
										'NULL',
										'NULL'
									);

									$csv_data['TR_INSPECTION_IMG'][$loop_img] = join( ',', $raw_image );
									$loop_img++;

								}
							}

							$loop_inspection = count( $csv_data['TR_INSPECTION'] ) + 1;
							foreach ( $insert as $ins ) {
								$raw_inspection = array(
									'\'NATIONAL\'',
									'\'0'.substr( $data['WERKS'], 0, 1 ).'\'',
									'\''.substr( $data['WERKS'], 0, 2 ).'\'',
									'\''.substr( $data['WERKS'], 2, 2 ).'\'',
									'\''.$data['WERKS'].'\'',
									'NULL',
									'NULL',
									'\''.$data['AFD_CODE'].'\'',
									'\''.( count( $hs ) > 0 ? $hs->afd_name : '' ).'\'',
									'\''.$data['BLOCK_CODE'].'\'',
									'\''.( count( $hs ) > 0 ? $hs->block_name : '' ).'\'',
									'\''.$data['WERKS'].$data['BLOCK_CODE'].'\'',
									'\''.$data['BLOCK_CODE'].'\'',
									'\''.( count( $hs ) > 0 ? $hs->block_name : '' ).'\'',
									'\''.$data['BLOCK_INSPECTION_CODE'].'\'',
									'TO_DATE(\''.date( 'd-m-Y H:i:s', strtotime( $ins['TANGGAL'] ) ).'\', \'DD-MM-RRRR HH24:MI:SS\')',
									'\''.$ins['CONTENT_INSPECT_CODE'].'\'',
									( $ins['CONTENT_NAME'] == '' ? 'NULL' : '\''.$ins['CONTENT_NAME'].'\'' ),
									'\''.$ins['NILAI'].'\'',
									'\''.$ins['INSERT_USER'].'\'',
									'\''.$ins['EMP_NAME'].'\'',
									'\''.$ins['EMP_POSITION'].'\'',
									'\''.$ins['INSPECTION_TYPE'].'\'',
									'SYSDATE',
									'NULL'
								);

								$csv_data['TR_INSPECTION'][$loop_inspection] = join( ',', $raw_inspection );
								$loop_inspection++;
							}

							$i = 1;
							$loop_inspection_track = count( $csv_data['TR_INSPECTION_PATH'] ) + 1;
							if ( !empty($data['TRACK'] ) ) {
								foreach ( $data['TRACK'] as $track ) {

									if ( intval( $data['AREAL'] ) == 0 ) {
										$data['AREAL'] = 0;
									}

									$track['LAT_TRACK'] = floatval( $track['LAT_TRACK'] );
									$track['LONG_TRACK'] = floatval( $track['LONG_TRACK'] );

									$raw_inspection_track = array(
										'\'NATIONAL\'',
										'\'0'.substr( $data['WERKS'], 0, 1 ).'\'',
										'\''.substr( $data['WERKS'], 0, 2 ).'\'',
										'\''.substr( $data['WERKS'], 2, 2 ).'\'',
										'\''.$data['WERKS'].'\'',
										'NULL',
										'NULL',
										'\''.$data['AFD_CODE'].'\'',
										'\''.( count( $hs ) > 0 ? $hs->afd_name : '' ).'\'',
										'\''.$data['BLOCK_CODE'].'\'',
										'\''.( count( $hs ) > 0 ? $hs->block_name : '' ).'\'',
										'\''.$data['WERKS'].$data['BLOCK_CODE'].'\'',
										'\''.$data['BLOCK_CODE'].'\'',
										'\''.( count( $hs ) > 0 ? $hs->block_name : '' ).'\'',
										'\''.$data['BLOCK_INSPECTION_CODE'].'\'',
										'TO_DATE(\''.date( 'd-m-Y H:i:s', strtotime( $track['DATE_TRACK'] ) ).'\', \'DD-MM-RRRR HH24:MI:SS\')',
										'\''.$data['AREAL'].'\'',
										'\'0\'',
										'\''.$track['LAT_TRACK'].'\'',
										'\''.$track['LONG_TRACK'].'\'',
										'\''.$ins['INSERT_USER'].'\'',
										'\''.$ins['EMP_NAME'].'\'',
										'\''.$ins['EMP_POSITION'].'\'',
										'SYSDATE',
										'NULL'
									);

									$csv_data['TR_INSPECTION_PATH'][$loop_inspection_track] = join( ',', $raw_inspection_track );
									$loop_inspection_track++;
									$i++;
								}
							}
						}
				}
			}
			$response['end_time'] = date( 'YmdHis' );

			if ( Storage::disk('export_mobile_inspection')->put( 'TR_INSPECTION.csv', join( PHP_EOL, $csv_data['TR_INSPECTION'] ) ) ) {
				$response['status']['TR_INSPECTION'] = 'OK';
			}
			if ( Storage::disk('export_mobile_inspection')->put( 'TR_INSPECTION_IMG.csv', join( PHP_EOL, $csv_data['TR_INSPECTION_IMG'] ) ) ) {
				$response['status']['TR_INSPECTION_IMG'] = 'OK';
			}
			if ( Storage::disk('export_mobile_inspection')->put( 'TR_INSPECTION_PATH.csv', join( PHP_EOL, $csv_data['TR_INSPECTION_PATH'] ) ) ) {
				$response['status']['TR_INSPECTION_PATH'] = 'OK';
			}
			
			// File::append( public_path( '/export/tap_dw/TR_INSPECTION.csv' ), join( PHP_EOL, $csv_data['TR_INSPECTION'] ).PHP_EOL );
			// File::append( public_path( '/export/tap_dw/TR_INSPECTION_IMG.csv' ), join( PHP_EOL, $csv_data['TR_INSPECTION_IMG'] ).PHP_EOL );
			// File::append( public_path( '/export/tap_dw/TR_INSPECTION_PATH.csv' ), join( PHP_EOL, $csv_data['TR_INSPECTION_PATH'] ).PHP_EOL );
			
			return response()->json( $response );
		}

	/**
	 * MOBILE_ESTATE.TR_EBCC
	 *
	 * Untuk mengisi data Mobile Inspection di Database Oracle.
	 * --------------------------------------------------------------------------
	 */
		public function sync_mobile_estate_tr_ebcc() {
			$client = new \GuzzleHttp\Client();
			$result = $client->request( 'GET', $this->url[$this->env]['ebcc_validation'].'/api/v1.1/export/tr-ebcc/'.date( 'Ym1' ).'000000/'.date( 'Ymt' ).'235959/estate', [
				'headers' => [
					'Authorization' => 'Bearer '.$this->access_token
				]
			] );

			$result = json_decode( $result->getBody(), true );
			$response = array();
			$response['message'] = 'MOBILE_ESTATE.TR_EBCC';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;
			$data_from = ( $this->env == 'development' ? 'MI_TEST_DEV': ( $this->env == 'production' ? 'MOBILE_INS' : 'MI_TEST_QA' ) );

			// print '<pre>';
			// print_r( $result );
			// print '</pre>';
			// dd();

			if ( $result['status'] == true && !empty( $result['data'] ) ) {
				foreach ( $result['data'] as $data ) {
					$insert_time = date( 'd-m-Y', strtotime( (String) $data['INSERT_TIME'] ) );
					$check = $this->tapapps_mobile_estate->select( "SELECT COUNT( * ) AS COUNT FROM MOBILE_ESTATE.TR_EBCC WHERE EBCC_CODE = '{$data['EBCC_VALIDATION_CODE']}'" );
					$user_lat = floatval( $data['LAT_TPH'] );
					$user_long = floatval( $data['LON_TPH'] );
					$result_user = $client->request( 'GET', $this->url[$this->env]['auth'].'/api/user/'.$data['INSERT_USER'], [
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					] );
					$result_user = json_decode( $result_user->getBody(), true );
					$insert_user = ( isset($result_user['data']) ? $result_user['data']['EMPLOYEE_NIK'] : '' );
 
					if ( $check[0]->count == 0 ) {
						$delivery_ticket = ( $data['DELIVERY_CODE'] != null ? $data['DELIVERY_CODE'] : "-" );
						$alasan_manual = ( $data['ALASAN_MANUAL'] == null ? 0 : $data['ALASAN_MANUAL'] );
						$query = $this->tapapps_mobile_estate->statement( "
							INSERT INTO 
								MOBILE_ESTATE.TR_EBCC (
									EBCC_CODE,
									WERKS,
									AFD_CODE,
									BLOCK_CODE,
									TPH_CODE,
									USER_LAT,
									USER_LONG,
									SYNC_FLAG,
									DATE_TIME,
									INSERT_USER,
									INSERT_TIME,
									UPDATE_USER,
									UPDATE_TIME,
									DELIVERY_TICKET,
									NIK_KRANI_BUAH,
									IMAGE_NAME,
									DATA_FROM,
									UPLOAD_TIME,
									STATUS_TPH_SCAN,
									ALASAN_MANUAL
								)
							VALUES (
								'{$data['EBCC_VALIDATION_CODE']}',
								'{$data['WERKS']}',
								'{$data['AFD_CODE']}',
								'{$data['BLOCK_CODE']}',
								'{$data['NO_TPH']}',
								{$user_lat},
								{$user_long},
								NULL,
								to_date('{$insert_time}','DD-MM-RRRR'),
								'{$insert_user}',
								to_date('{$insert_time}','DD-MM-RRRR'),
								NULL,
								NULL,
								'{$delivery_ticket}',
								NULL,
								NULL,
								'{$data_from}',
								NULL,
								'{$data['STATUS_TPH_SCAN']}',
								{$alasan_manual}
							)
						" );
						$response['num_rows']++;
					}
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * MOBILE_ESTATE.TR_EBCC_KUALITAS
	 *
	 * Untuk mengisi data Mobile Inspection di Database Oracle.
	 * --------------------------------------------------------------------------
	 */
		public function sync_mobile_estate_tr_ebcc_kualitas() {
			$client = new \GuzzleHttp\Client();
			$result = $client->request( 'GET', $this->url[$this->env]['ebcc_validation'].'/api/v1.1/export/tr-ebcc-kualitas/'.date( 'Ym1' ).'000000/'.date( 'Ymt' ).'235959/estate', [
				'headers' => [
					'Authorization' => 'Bearer '.$this->access_token
				]
			]);
			$response = array();
			$response['message'] = 'MOBILE_ESTATE.TR_EBCC_KUALITAS';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;
			$result = json_decode( $result->getBody(), true );
			$data_from = ( $this->env == 'development' ? 'MI_TEST_DEV': ( $this->env == 'production' ? 'MOBILE_INS' : 'MI_TEST_QA' ) );

			if ( $result['status'] == true && !empty( $result['data'] ) ) {
				foreach ( $result['data'] as $data ) {

					$insert_time = date( 'd-m-Y', strtotime( (String) $data['INSERT_TIME'] ) );
					$ebcc_kualitas_code = $data['EBCC_VALIDATION_CODE'].$data['ID_KUALITAS'];
					$id_kualitas = (int) $data['ID_KUALITAS'];
					$jumlah = (int) $data['JUMLAH'];
					$check = $this->tapapps_mobile_estate->select( "SELECT COUNT( * ) AS jumlah FROM MOBILE_ESTATE.TR_EBCC_KUALITAS WHERE EBCC_KUALITAS_CODE = '{$ebcc_kualitas_code}'" );
					$result_user = $client->request( 'GET', $this->url[$this->env]['auth'].'/api/user/'.$data['INSERT_USER'], [
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					] );

					// print "SELECT EBCC_KUALITAS_CODE FROM MOBILE_ESTATE.TR_EBCC_KUALITAS WHERE EBCC_KUALITAS_CODE = '{$ebcc_kualitas_code}'";
					// print '<pre>';
					// print_r($check);
					// print '<pre><hr />';

					$result_user = json_decode( $result_user->getBody(), true );
					$insert_user = ( isset( $result_user['data'] ) ? $result_user['data']['EMPLOYEE_NIK'] : '' );
					$werks = ( isset( $data['WERKS'] ) ? $data['WERKS'] : '' );
					$block_code = ( isset( $data['BLOCK_CODE'] ) ? $data['BLOCK_CODE']: '' );
					$afd_code = ( isset( $data['AFD_CODE'] ) ? $data['AFD_CODE']: '' );
					$tph_code = ( isset( $data['TPH_CODE'] ) ? $data['TPH_CODE']: '' );

					// print '<pre>';
					// print_r( $check );
					// print '<pre>';

					// if ( isset( $data['WERKS'] ) && isset( $data['AFD_CODE'] ) && isset( $data['TPH_CODE'] ) ):
					if ( $check[0]->jumlah == 0 ) {
						// print 'Ay<br />';
						$this->tapapps_mobile_estate->statement( "
							INSERT INTO 
								MOBILE_ESTATE.TR_EBCC_KUALITAS (
									EBCC_KUALITAS_CODE,
									EBCC_CODE,
									ID_KUALITAS,
									QTY,
									STATUS,
									DATE_TIME,
									INSERT_USER,
									INSERT_TIME,
									UPDATE_USER,
									UPDATE_TIME,
									WERKS,
									AFD_CODE,
									BLOCK_CODE,
									TPH_CODE,
									DELIVERY_TICKET,
									DATA_FROM,
									UPLOAD_TIME
								)
							VALUES (
								'{$ebcc_kualitas_code}',
								'{$data['EBCC_VALIDATION_CODE']}',
								{$id_kualitas},
								{$jumlah},
								'NO',
								TO_DATE( '{$insert_time}', 'DD-MM-RRRR' ),
								'{$insert_user}',
								TO_DATE( '{$insert_time}', 'DD-MM-RRRR' ),
								NULL,
								NULL,
								'{$werks}',
								'{$afd_code}',
								'{$block_code}',
								'{$tph_code}',
								'-',
								'{$data_from}',
								NULL
							)
						" );
						$this->tapapps_mobile_estate->commit();
						$response['num_rows']++;
					}
					// endif;
					
				}
				$response['end_time'] = date( 'YmdHis' );
			}
			
			// return response()->json( $response );
		}

	/**
	 * MOBILE_ESTATE.TR_IMAGES
	 *
	 * Untuk mengisi data Mobile Inspection di Database Oracle.
	 * --------------------------------------------------------------------------
	 */
		public function sync_mobile_estate_tr_image() {
			$client = new \GuzzleHttp\Client();
			$result = $client->request( 'GET', $this->url[$this->env]['ebcc_validation'].'/api/v1.1/export/tr-ebcc/'.date( 'Ym1' ).'000000/'.date( 'Ymt' ).'235959/estate', [
				'headers' => [
					'Authorization' => 'Bearer '.$this->access_token
				]
			] );
			$result = json_decode( $result->getBody(), true );
			$response = array();
			$response['message'] = 'MOBILE_ESTATE.TR_IMAGE';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			// print '<pre>';
			// print_r($result);
			// print '</pre>';
			// dd();

			if ( $result['status'] == true && !empty( $result['data'] ) ) {
				foreach ( $result['data'] as $data ) {
					$result_img = $client->request( 'GET', $this->url[$this->env]['images'].'/images/'.$data['EBCC_VALIDATION_CODE'], [
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					] );
					$result_img = json_decode( $result_img->getBody(), true );

					// print '<pre>';
					// print_r($result_img);
					// print '</pre>';
					
					if ( !empty( $result_img['data'] ) ) {
						// print 'OK<br />';
						foreach( $result_img['data'] as $img_data ) {
							$result_user = $client->request( 'GET', $this->url[$this->env]['auth'].'/api/user/'.$data['INSERT_USER'], [
								'headers' => [
									'Authorization' => 'Bearer '.$this->access_token
								]
							] );
							$result_user = json_decode( $result_user->getBody(), true );
							$check = $this->tapapps_mobile_estate->select( "SELECT COUNT( * ) AS JUMLAH FROM MOBILE_ESTATE.TR_IMAGE WHERE TR_CODE = '{$img_data['TR_CODE']}' AND ROWNUM = 1" );

							if ( $check[0]->jumlah == 0 ):
								$raw['TR_CODE'] = $img_data['TR_CODE'];
								$raw['TR_TYPE'] = 'MOBILE_INS';
								$raw['IMAGE_NAME'] = $img_data['IMAGE_NAME'];
								$raw['INSERT_USER'] = ( isset( $result_user['data'] ) ? $result_user['data']['EMPLOYEE_NIK'] : '' );
								$raw['INSERT_TIME'] = date( 'd-m-Y', strtotime( $data['INSERT_TIME'] ) );
								$raw['USER_LONG'] = floatval( $data['LON_TPH'] );
								$raw['USER_LAT'] = floatval( $data['LAT_TPH'] );
								$query_insert = "
									INSERT INTO 
										MOBILE_ESTATE.TR_IMAGE (
											TR_CODE,
											TR_TYPE,
											IMAGE_NAME,
											SYNC_FLAG,
											USER_LONG,
											USER_LAT,
											INSERT_USER,
											INSERT_TIME,
											UPDATE_USER,
											UPDATE_TIME,
											IMAGE_FILE,
											UPLOAD_TIME
										) 
									VALUES (
										'{$raw['TR_CODE']}',
										'{$raw['TR_TYPE']}',
										'{$raw['IMAGE_NAME']}',
										'SYNC',
										'{$raw['USER_LONG']}',
										'{$raw['USER_LAT']}',
										'{$raw['INSERT_USER']}',
										TO_DATE('{$raw['INSERT_TIME']}','DD-MM-RRRR' ),
										NULL,
										NULL,
										NULL,
										NULL
									)
								";
								$this->tapapps_mobile_estate->statement( $query_insert );
								$this->tapapps_mobile_estate->commit();
								
								$response['num_rows']++;
							endif;
						}
					}
				}
			}

			$response['end_time'] = date( 'YmdHis' );
			return response()->json( $response );
		}

	/**
	 * MOBILE_INSPECTION.TR_PREMI_INSPECTION
	 *
	 * Untuk mengisi data Mobile Inspection di Database Oracle.
	 * --------------------------------------------------------------------------
	 */
		public function sync_tr_premi_inspection() {
			$client = new \GuzzleHttp\Client();
			$result = $client->request( 'GET', $this->url[$this->env]['inspection'].'/export/premi/'.date( 'Ymd' ).'000000/'.date( 'Ymd' ).'235959', [
				'headers' => [
					'Authorization' => 'Bearer '.$this->access_token
				]
			]);

			// $result = $client->request( 'GET', $this->url[$this->env]['inspection'].'/export/premi/20190813000000/20190813235959', [
			// 	'headers' => [
			// 		'Authorization' => 'Bearer '.$this->access_token
			// 	]
			// ]);

			$response = array();
			$response['message'] = 'TR_PREMI_INSPECTION';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;
			$result = json_decode( $result->getBody(), true );

			#print '<pre>';
			#print_r( $result );
			#print '</pre>';
			#dd();

			if ( $result['status'] == true ) {

				// Menghapus dulu transaksi bulan sekarang
				// $spmon_bulan_ini = date( 'Ym' );
				// $this->tapapps_mobile_inspection->statement( "
				// 	DELETE FROM TR_PREMI_INSPECTION WHERE SPMON = '{$spmon_bulan_ini}'
				// " );

				foreach ( $result['data'] as $inspeksi ) {

					$ins_spmon = date( 'Ym', strtotime( $inspeksi['INSPECTION_DATE'] ) );
					$ins_werks = $inspeksi['WERKS'];
					$ins_code = $inspeksi['BLOCK_INSPECTION_CODE'];
					$ins_afd_code = ( $inspeksi['AFD_CODE'] ? $inspeksi['AFD_CODE'] : "" );
					$ins_block_code = ( $inspeksi['BLOCK_CODE'] ? $inspeksi['BLOCK_CODE'] : "" );
					$ins_CC0002_pokok_panen = 0; // CONTENT_CODE : CC0002 ( POKOK PANEN )
					$ins_CC0004_piringan = 0; // CONTENT_CODE: CC0004 (Brondolan di Piringan )
					$ins_CC0005_tph = 0; // CONTENT_CODE: CC0005 (Brondolan di TPH )
					$ins_date = date( 'Y-m-d H:i:s', strtotime( $inspeksi['INSPECTION_DATE'] ) );

					$check = $this->tapapps_mobile_inspection->select( "SELECT COUNT( * ) AS COUNT FROM MOBILE_INSPECTION.TR_PREMI_INSPECTION WHERE BLOCK_INSPECTION_CODE = '{$ins_code}'" );
					
					if ( $check[0]->count == 0 ) {
						foreach ( $inspeksi['DETAIL'] as $detail ) {
							if ( $detail['CONTENT_INSPECTION_CODE'] == 'CC0002' ) { $ins_CC0002_pokok_panen = intval( $detail['VALUE'] ); }
							if ( $detail['CONTENT_INSPECTION_CODE'] == 'CC0004' ) { $ins_CC0004_piringan = intval( $detail['VALUE'] ); }
							if ( $detail['CONTENT_INSPECTION_CODE'] == 'CC0005' ) { $ins_CC0005_tph = intval( $detail['VALUE'] ); }
						}

						$query = $this->tapapps_mobile_inspection->statement( "
							INSERT INTO 
								MOBILE_INSPECTION.TR_PREMI_INSPECTION (
									WERKS,
									BLOCK_INSPECTION_CODE,
									AFD_CODE,
									BLOCK_CODE,
									SPMON,
									POKOK_PANEN,
									BRD_PIRINGAN,
									BRD_TPH,
									LOSSES_BRDT,
									INSERT_DATE
								) 
							VALUES (
								'{$ins_werks}', 
								'{$ins_code}',
								'{$ins_afd_code}',  
								'{$ins_block_code}',  
								'{$ins_spmon}',  
								'{$ins_CC0002_pokok_panen}',
								'{$ins_CC0004_piringan}',  
								'{$ins_CC0005_tph}',  
								'0',
								SYSDATE
							)
						" );
						
						$response['num_rows']++;
					}
				}

				$response['end_time'] = date( 'YmdHis' );
			}

			$this->tapapps_mobile_inspection->commit();

			// Insert Log TR_PREMI_INSPECTION
			$start_time = date( 'Y-m-d H:i:s', strtotime( $response['start_time'] ) );
			$end_time = date( 'Y-m-d H:i:s', strtotime( $response['end_time'] ) );
			$this->tapapps_mobile_inspection->statement( "
				INSERT INTO 
					TR_PREMI_INSPECTION_LOG 
				VALUES (
					TO_DATE( '{$start_time}', 'yyyy/mm/dd hh24:mi:ss' ),
					TO_DATE( '{$end_time}', 'yyyy/mm/dd hh24:mi:ss' ),
					{$response['num_rows']}
				)
			" );

			return response()->json( $response );
		}

	/**
	 * GENERATE TOKEN
	 *
	 * Untuk generate token setiap 6 hari.
	 * --------------------------------------------------------------------------
	 */
		public function generate_token() {
			for ( $i = 1; $i <= 1000; $i++ ) {
				$login = self::login();
				if ( $login['status'] == true ) {
					Storage::disk( 'local' )->put( 'files/access_token_mobile_inspection.txt', $login['data']['ACCESS_TOKEN'] );
					break;
				}
			}
		}

	/**
	 * LOGIN
	 *
	 * Untuk login ke Service Mobile Inspection
	 * --------------------------------------------------------------------------
	 */
		public function login() {
			$client = new \GuzzleHttp\Client();
			$login = $client->request( 'POST', $this->url[$this->env]['auth'].'/api/login', [
				'json' => [
					'username' => $this->auth['username'],
					'password' => $this->auth['password'],
					'imei' => $this->auth['imei'],
				]
			]);
			$login = json_decode( $login->getBody(), true );

			return $login;
		}

	/**
	 * Sync TM_REGION
	 *
	 * Untuk update ke database MongoDB (Insert time, update time, delete time)
	 * --------------------------------------------------------------------------
	 */
		public function sync_tm_region() {
			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT 
					NATIONAL, 
					REGION_CODE, 
					REGION_NAME 
				FROM 
					TAP_DW.TM_REGION 
				WHERE 
					REGION_CODE NOT IN ( '03' ) 
			");
			$response = array();
			$response['message'] = 'TM_REGION';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$result = $client->request( 'POST', $this->url[$this->env]['hectare_statement'].'/sync-tap/region', [
						'json' => [
							'NATIONAL' => $data->national,
							'REGION_CODE' => $data->region_code,
							'REGION_NAME' => $data->region_name
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * Sync TM_COMP
	 *
	 * Untuk update ke database MongoDB (Insert time, update time, delete time)
	 * --------------------------------------------------------------------------
	 */
		public function sync_tm_comp() {
			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT 
					NATIONAL, 
					REGION_CODE, 
					COMP_CODE, 
					COMP_NAME, 
					ADDRESS 
				FROM 
					TAP_DW.TM_COMP 
				WHERE 
					REGION_CODE NOT IN ( '03' )
			");
			$response = array();
			$response['message'] = 'TM_COMP';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$result = $client->request( 'POST', $this->url[$this->env]['hectare_statement'].'/sync-tap/comp', [
						'json' => [
							'NATIONAL' => $data->national,
							'REGION_CODE' => $data->region_code,
							'COMP_CODE' => $data->comp_code,
							'COMP_NAME' => $data->comp_name,
							'ADDRESS' => $data->address
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * TM_EST
	 *
	 * Untuk update ke database MongoDB (Insert time, update time, delete time)
	 * --------------------------------------------------------------------------
	 */
		public function sync_tm_est() {
			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT 
					NATIONAL, 
					REGION_CODE, 
					COMP_CODE, 
					EST_CODE, 
					WERKS, 
					EST_NAME, 
					CITY,
					START_VALID,
					END_VALID
				FROM 
					TAP_DW.TM_EST
				WHERE 
					REGION_CODE NOT IN ( '03' )
			");
			$response = array();
			$response['message'] = 'TM_EST';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$result = $client->request( 'POST', $this->url[$this->env]['hectare_statement'].'/sync-tap/est', [
						'json' => [
							'NATIONAL' => $data->national,
							'REGION_CODE' => $data->region_code,
							'COMP_CODE' => $data->comp_code,
							'EST_CODE' => $data->est_code,
							'EST_NAME' => $data->est_code,
							'WERKS' => $data->werks,
							'EST_NAME' => $data->est_name,
							'CITY' => $data->city,
							'START_VALID' => date( 'Ymd', strtotime( $data->start_valid ) ),
							'END_VALID' => date( 'Ymd', strtotime( $data->end_valid ) ),
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * Sync TM_AFD
	 *
	 * Untuk update ke database MongoDB (Insert time, update time, delete time)
	 * --------------------------------------------------------------------------
	 */
		public function sync_tm_afd() {
			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT
					NATIONAL,
					REGION_CODE,
					COMP_CODE,
					EST_CODE,
					WERKS,
					AFD_CODE,
					AFD_NAME,
					WERKS || AFD_CODE WERKS_AFD_CODE,
					START_VALID,
					END_VALID
				FROM 
					TAP_DW.TM_AFD 
				WHERE 
					REGION_CODE NOT IN ( '03' )
			");
			$response = array();
			$response['message'] = 'TM_AFD';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$result = $client->request( 'POST', $this->url[$this->env]['hectare_statement'].'/sync-tap/afdeling', [
						'json' => [
							'NATIONAL' => $data->national,
							'REGION_CODE' => $data->region_code,
							'COMP_CODE' => $data->comp_code,
							'EST_CODE' => $data->est_code,
							'WERKS' => $data->werks,
							'AFD_CODE' => $data->afd_code,
							'AFD_NAME' => ( $data->afd_name != '' ) ? $data->afd_name : '',
							'WERKS_AFD_CODE' => $data->werks_afd_code,
							'START_VALID' => date( 'Ymd', strtotime( $data->start_valid ) ),
							'END_VALID' => date( 'Ymd', strtotime( $data->end_valid ) ),
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * TM_BLOCK
	 *
	 * Untuk update ke database MongoDB (Insert time, update time, delete time)
	 * --------------------------------------------------------------------------
	 */
		public function sync_tm_block() {
			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT 
					BLOK.NATIONAL,
					BLOK.REGION_CODE,
					BLOK.COMP_CODE,
					BLOK.EST_CODE,
					BLOK.WERKS,
					BLOK.AFD_CODE,
					BLOK.BLOCK_CODE,
					BLOK.BLOCK_NAME,
					BLOK.WERKS_AFD_CODE,
					BLOK.WERKS_AFD_BLOCK_CODE,
					BLOK.START_VALID,
					BLOK.END_VALID,
					TPH.JUMLAH AS JUMLAH_TPH
				FROM (
						SELECT
							BLOCK.NATIONAL,
							BLOCK.REGION_CODE,
							BLOCK.COMP_CODE,
							BLOCK.EST_CODE,
							BLOCK.WERKS,
							BLOCK.AFD_CODE,
							BLOCK.BLOCK_CODE,
							BLOCK.BLOCK_NAME,
							BLOCK.WERKS || BLOCK.AFD_CODE AS WERKS_AFD_CODE,
							BLOCK.WERKS || BLOCK.AFD_CODE || BLOCK.BLOCK_CODE AS WERKS_AFD_BLOCK_CODE,
							MAX( BLOCK.START_VALID ) AS START_VALID,
							MAX( BLOCK.END_VALID ) AS END_VALID
						FROM
							TAP_DW.TM_BLOCK BLOCK
						WHERE
							TO_CHAR( SYSDATE, 'RRRR-MM-DD' ) >= TO_CHAR( BLOCK.START_VALID, 'RRRR-MM-DD' )
							AND TO_CHAR( SYSDATE, 'RRRR-MM-DD' ) <= TO_CHAR( BLOCK.END_VALID, 'RRRR-MM-DD' )
							AND BLOCK.REGION_CODE NOT IN ( '03' )
						GROUP BY
							BLOCK.NATIONAL,
							BLOCK.REGION_CODE,
							BLOCK.COMP_CODE,
							BLOCK.EST_CODE,
							BLOCK.WERKS,
							BLOCK.AFD_CODE,
							BLOCK.BLOCK_CODE,
							BLOCK.BLOCK_NAME
					) BLOK
					INNER JOIN (
						SELECT
							A.WERKS,
							A.AFD,
							A.BLOCK_CODE,
							A.TPH AS JUMLAH,
							A.CREATED_AT
						FROM
							EBCC.T_BLOK_TPH@PRODDB_LINK A
							INNER JOIN (
								SELECT * FROM(
									SELECT 
										EBCC.WERKS, 
										EBCC.AFD, 
										EBCC.BLOCK_CODE,
										MAX( CREATED_AT ) AS CREATED_AT
									FROM
										EBCC.T_BLOK_TPH@PRODDB_LINK EBCC
									WHERE
										SUBSTR( EBCC.WERKS, 0, 1 ) NOT IN ( '3' )
									GROUP BY
										EBCC.WERKS, 
										EBCC.AFD, 
										EBCC.BLOCK_CODE
								)
							) B ON A.WERKS = B.WERKS 
								AND A.AFD = B.AFD 
								AND A.BLOCK_CODE = B.BLOCK_CODE
								AND TO_CHAR( A.CREATED_AT, 'RRRR-MM-DD' ) >= TO_CHAR( B.CREATED_AT, 'RRRR-MM-DD' )
						WHERE
							SUBSTR( A.WERKS, 0, 1 ) NOT IN ( '3' )
					) TPH ON BLOK.WERKS = TPH.WERKS 
						AND BLOK.AFD_CODE = TPH.AFD 
						AND BLOK.BLOCK_CODE = TPH.BLOCK_CODE
			");
			$response = array();
			$response['message'] = 'TM_BLOCK';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$result = $client->request( 'POST', $this->url[$this->env]['hectare_statement'].'/sync-tap/block', [
						'json' => [
							'NATIONAL' => $data->national,
							'REGION_CODE' => $data->region_code,
							'COMP_CODE' => $data->comp_code,
							'EST_CODE' => $data->est_code,
							'WERKS' => $data->werks, 
							'JUMLAH_TPH' => $data->jumlah_tph,
							'AFD_CODE' => $data->afd_code,
							'BLOCK_CODE' => $data->block_code,
							'BLOCK_NAME' => $data->block_name,
							'WERKS_AFD_CODE' => $data->werks_afd_code,
							'WERKS_AFD_BLOCK_CODE' => $data->werks_afd_block_code,
							'LATITUDE_BLOCK' => '',
							'LONGITUDE_BLOCK' => '',
							'START_VALID' => date( 'Ymd', strtotime( $data->start_valid ) ),
							'END_VALID' => date( 'Ymd', strtotime( $data->end_valid ) ),
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * TR_LAND_USE
	 *
	 * Untuk update ke database MongoDB (Insert time, update time, delete time)
	 * --------------------------------------------------------------------------
	 */
		public function sync_tr_land_use() {
			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT 
					* 
				FROM (
					SELECT
						A.NATIONAL,
						A.REGION_CODE,
						A.COMP_CODE,
						A.EST_CODE,
						A.WERKS,
						A.SUB_BA_CODE,
						A.KEBUN_CODE,
						A.AFD_CODE,
						A.AFD_NAME,
						A.WERKS || A.AFD_CODE AS WERKS_AFD_CODE,
						A.BLOCK_CODE,
						A.BLOCK_NAME,
						A.WERKS || A.AFD_CODE || A.BLOCK_CODE AS WERKS_AFD_BLOCK_CODE,
						A.LAND_USE_CODE,
						A.LAND_USE_NAME,
						A.LAND_USE_CODE_GIS,
						A.SPMON,
						A.LAND_CAT,
						A.LAND_CAT_L1_CODE,
						A.LAND_CAT_L1,
						A.LAND_CAT_L2_CODE,
						A.LAND_CAT_L2,
						A.MATURITY_STATUS,
						A.SCOUT_STATUS,
						A.AGES,
						A.HA_SAP,
						A.PALM_SAP,
						A.SPH_SAP,
						A.HA_GIS,
						A.PALM_GIS,
						A.SPH_GIS,
						A.SCOUT_STATUS_HS,
						A.AGES_HS
					FROM
						TAP_DW.TR_HS_LAND_USE A
						INNER JOIN (
							SELECT 
								NATIONAL,
								REGION_CODE,
								COMP_CODE,
								EST_CODE,
								AFD_CODE,
								BLOCK_CODE,
								MAX(SPMON) AS SPMON 
							FROM
							    TAP_DW.TR_HS_LAND_USE
							WHERE 
								REGION_CODE NOT IN ( '03' )
							GROUP BY
								NATIONAL,
								REGION_CODE,
								COMP_CODE,
								EST_CODE,
								AFD_CODE,
								BLOCK_CODE
						) B ON 
							A.REGION_CODE = B.REGION_CODE 
							AND A.COMP_CODE = B.COMP_CODE 
							AND A.EST_CODE = B.EST_CODE
							AND A.AFD_CODE = B.AFD_CODE
							AND A.BLOCK_CODE = B.BLOCK_CODE
							AND A.SPMON = B.SPMON
					WHERE
						A.LAND_CAT = 'PLANTED'
				)
				WHERE
					BLOCK_CODE = LAND_USE_CODE
					AND REGION_CODE NOT IN ( '03' )
					AND ROWNUM <= 25
			");
			$response = array();
			$response['message'] = 'TR_HS_LAND_USE';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$result = $client->request( 'POST', $this->url[$this->env]['hectare_statement'].'/sync-tap/land-use', [
						'json' => [
							"NATIONAL" 				=> $data->national,
							"REGION_CODE" 			=> $data->region_code,
							"COMP_CODE" 			=> $data->comp_code,
							"EST_CODE" 				=> $data->est_code,
							"WERKS" 				=> $data->werks,
							"SUB_BA_CODE" 			=> $data->sub_ba_code,
							"KEBUN_CODE" 			=> $data->kebun_code,
							"AFD_CODE" 				=> $data->afd_code,
							"AFD_NAME" 				=> $data->afd_name,
							"WERKS_AFD_CODE" 		=> $data->werks_afd_code,
							"BLOCK_CODE" 			=> $data->block_code,
							"BLOCK_NAME"			=> $data->block_name,
							"WERKS_AFD_BLOCK_CODE"	=> $data->werks_afd_block_code,
							"LAND_USE_CODE" 		=> $data->land_use_code,
							"LAND_USE_NAME" 		=> $data->land_use_name,
							"LAND_USE_CODE_GIS" 	=> $data->land_use_code_gis,
							"SPMON" 				=> date( 'Y-m-d', strtotime( $data->spmon ) ),
							"LAND_CAT" 				=> $data->land_cat,
							"LAND_CAT_L1_CODE" 		=> $data->land_cat_l1_code,
							"LAND_CAT_L1" 			=> $data->land_cat_l1,
							"LAND_CAT_L2_CODE" 		=> $data->land_cat_l2_code,
							"MATURITY_STATUS" 		=> $data->maturity_status,
							"SCOUT_STATUS" 			=> $data->scout_status,
							"AGES" 					=> $data->ages,
							"HA_SAP" 				=> $data->ha_sap,
							"PALM_SAP" 				=> $data->palm_sap,
							"SPH_SAP" 				=> $data->sph_sap,
							"HA_GIS" 				=> $data->ha_gis,
							"PALM_GIS" 				=> $data->palm_gis,
							"SPH_GIS" 				=> $data->sph_gis
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * Sync TM_EMPLOYEE_SAP
	 *
	 * Untuk sync data TM_EMPLOYEE_SAP MongoDB dan Oracle
	 * --------------------------------------------------------------------------
	 */
		public function sync_tm_employee_sap() {
			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT
					SAP.EMPLOYEE_NAME,
					SAP.NIK,
					SAP.JOB_CODE,
					SAP.INSERT_TIME_DW,
					SAP.UPDATE_TIME_DW,
					SAP.START_VALID,
					SAP.END_VALID
				FROM
					TAP_DW.TM_EMPLOYEE_SAP SAP
				WHERE
					SAP.EMPLOYEE_NAME IS NOT NULL
					AND SAP.EMPLOYEE_NAME!= 'N/A'
					AND SAP.NIK IS NOT NULL
					AND SAP.JOB_CODE IS NOT NULL
					AND TO_CHAR( SYSDATE, 'RRRR-MM-DD' ) >= TO_CHAR( SAP.START_VALID, 'RRRR-MM-DD' )
					AND TO_CHAR( SYSDATE, 'RRRR-MM-DD' ) <= TO_CHAR( SAP.END_VALID, 'RRRR-MM-DD' )
					AND SAP.RES_DATE IS NULL
					--AND ROWNUM <= 10
				ORDER BY 
					SAP.NIK DESC
			");
			$response = array();
			$response['message'] = 'TM_EMPLOYEE_SAP';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$res = $client->request('POST', $this->url[$this->env]['auth'].'/api/v1.0/sync/tap/employee-sap', [
						'json' => [
							'NIK' => $data->nik,
							'EMPLOYEE_NAME' => $data->employee_name,
							'JOB_CODE' => $data->job_code,
							'INSERT_TIME_DW' => $data->insert_time_dw,
							'UPDATE_TIME_DW' => ( $data->insert_time_dw == null ? 0 : $data->insert_time_dw ),
							'START_VALID' => ( $data->start_valid == null ? 0 : $data->start_valid ),
							'END_VALID' => ( $data->end_valid == null ? 0 : $data->end_valid ),
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * TR_CLASS_BLOCK
	 *
	 * Untuk insert data Class Block
	 * --------------------------------------------------------------------------
	 */
		public function sync_tr_class_block() {
			$client = new \GuzzleHttp\Client();
			$query = $this->tapapps_mobile_estate->select("
				SELECT 
					CB.WERKS,
					CB.AFD_CODE,
					CB.BLOCK_CODE,
					CB.DATE_TIME,
					CB.CLASS_BLOCK
				FROM 
					MOBILE_ESTATE.TR_CLASS_BLOCK CB
				--WHERE
				--	CB.WERKS = '2121'
				--	AND CB.AFD_CODE = 'B'
				--	AND CB.BLOCK_CODE = '016'
			");
			$response = array();
			$response['message'] = 'TR_CLASS_BLOCK';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$result = $client->request('POST', $this->url['report'].'/api/report/class-block', [
						'json' => [
							'WERKS' => $data->werks,
							'AFD_CODE' => $data->afd_code,
							'BLOCK_CODE' => $data->block_code,
							'CLASS_BLOCK' => $data->class_block,
							'DATE_TIME' => date( 'Ym', strtotime( $data->date_time ) ),
							'INSERT_TIME' => date( 'YmdHis' )
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * Sync TM_EMPLOYEE_HRIS
	 * --------------------------------------------------------------------------
	 */
		public function sync_tm_employee_hris() {

			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT
					HRIS.EMPLOYEE_NIK,
					HRIS.EMPLOYEE_USERNAME,
					HRIS.EMPLOYEE_FULLNAME,
					HRIS.EMPLOYEE_POSITIONCODE,
					HRIS.EMPLOYEE_POSITION,
					HRIS.EMPLOYEE_EMAIL,
					HRIS.INSERT_TIME_DW,
					HRIS.UPDATE_TIME_DW,
					HRIS.DELETE_TIME_DW
				FROM
					TAP_DW.TM_EMPLOYEE_HRIS HRIS
				WHERE
					HRIS.EMPLOYEE_FULLNAME IS NOT NULL
					AND HRIS.EMPLOYEE_USERNAME IS NOT NULL
					AND HRIS.EMPLOYEE_RESIGNDATE IS NULL
					AND HRIS.DELETE_TIME_DW IS NULL
			");
			$response = array();
			$response['message'] = 'TM_EMPLOYEE_HRIS';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$res = $client->request( 'POST', $this->url[$this->env]['auth'].'/api/v1.0/sync/tap/employee-hris', [
						'json' => [
							'EMPLOYEE_NIK' => $data->employee_nik,
							'EMPLOYEE_USERNAME' => $data->employee_username,
							'EMPLOYEE_FULLNAME' => $data->employee_fullname,
							'EMPLOYEE_POSITIONCODE' => $data->employee_positioncode,
							'EMPLOYEE_POSITION' => $data->employee_position,
							'EMPLOYEE_EMAIL' => $data->employee_email,
							'INSERT_TIME_DW' => $data->insert_time_dw,
							'UPDATE_TIME_DW' => ( $data->update_time_dw == null ? 0 : $data->update_time_dw ),
							'DELETE_TIME_DW' => ( $data->delete_time_dw == null ? 0 : $data->delete_time_dw )
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);

					$response['num_rows']++;
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}

	/**
	 * Sync EBCC.T_KUALITAS_PANEN (TM_KUALITAS@MongoDB)
	 * --------------------------------------------------------------------------
	 *
	 * ..
	 *
	 */
		public function sync_tm_kualitas() {

			$client = new \GuzzleHttp\Client();
			$query = $this->tap_dw->select("
				SELECT
					KLT.ID_KUALITAS,
					KLT.NAMA_KUALITAS,
					KLT.UOM,
					KLT.GROUP_KUALITAS,
					KLT.ACTIVE_STATUS,
					KLT.PENALTY_STATUS,
					KLT.SHORT_NAME
				FROM
					EBCC.T_KUALITAS_PANEN@PRODDB_LINK KLT
				ORDER BY
					KLT.NAMA_KUALITAS ASC
			");

			$response = array();
			$response['message'] = 'TM_KUALITAS';
			$response['start_time'] = date( 'YmdHis' );
			$response['end_time'] = 0;
			$response['num_rows'] = 0;

			if ( count( $query ) > 0 ) {
				foreach ( $query as $data ) {
					$res = $client->request( 'POST', $this->url[$this->env]['ebcc_validation'].'/api/v1.0/sync-tap/kualitas', [
						'json' => [
							"ID_KUALITAS" => intval( $data->id_kualitas ),
							"NAMA_KUALITAS" => $data->nama_kualitas,
							"UOM" => $data->uom,
							"GROUP_KUALITAS" => $data->group_kualitas,
							"ACTIVE_STATUS" => $data->active_status,
							"PENALTY_STATUS" => $data->penalty_status,
							"SHORT_NAME" => $data->short_name
						],
						'headers' => [
							'Authorization' => 'Bearer '.$this->access_token
						]
					]);
					$res = json_decode( $res->getBody(), true );
					if ( $res['status'] == true ) {
						$response['num_rows']++;
					}
					
				}
				$response['end_time'] = date( 'YmdHis' );
			}

			return response()->json( $response );
		}




	/**
	 * ------------------------------------------------------------------------
	 * ZONA BONGKAR PASANG
	 * ------------------------------------------------------------------------
	 */

}
