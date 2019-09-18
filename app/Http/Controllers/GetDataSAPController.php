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
use Illuminate\Support\Facades\Input;

include 'Soap/nusoap.php';

use nusoap_client;

class GetDataSAPController extends Controller
{

	public function material_group()
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.37:8000/sap/bc/soap/wsdl11?services=ZFMDB_GROUPMATERIAL&sap-client=520',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);

		$client->setCredentials("tap1", "abaptap092013");
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}
		// Doc/lit parameters get wrapped
		$param = array('Symbol' => 'it_group');
		$result = $client->call('ZFMDB_GROUPMATERIAL', array('parameters' => $param), '', '', false, true);
		// Check for a fault
		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result["IT_GROUP"]);
			echo '</pre>';
		} else {
			// Check for errors
			$err = $client->getError();
			if ($err) {
				// Display the error
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				$data = $result["IT_GROUP"];
				$json = '{"data":[';
				$no = 1;
				foreach ($data as $row) {
					if ($no > 1) {
						$json .= ",";
					}

					$json .= json_encode($row);
					$no++;
				}
				$json .= ']}';
				echo $json;
			}
		}
	}

	public function uom()
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.37:8000/sap/bc/soap/wsdl11?services=ZFMDB_UOM&sap-client=520',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);
		$client->setCredentials("tap1", "abaptap092013");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$param = array('Symbol' => 'it_uom');
		$result = $client->call('ZFMDB_UOM', array('parameters' => $param), '', '', false, true);

		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result["IT_UOM"]);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				if (isset($result["IT_UOM"]["item"])) {
					$data = $result["IT_UOM"]["item"];
					$json = '{"data":[';
					$no = 1;
					foreach ($data as $row) {
						if ($row["MANDT"]) {
							if ($no > 1) {
								$json .= ",";
							}
							$arr = array(
								"MANDT" => utf8_decode($row["MANDT"]),
								"SPRAS" => utf8_decode($row["SPRAS"]),
								"MSEHI" => utf8_decode($row["MSEHI"]),
								"MSEH3" => utf8_decode($row["MSEH3"]),
								"MSEH6" => utf8_decode($row["MSEH6"]),
								"MSEHT" => utf8_decode($row["MSEHT"]),
								"MSEHL" => utf8_decode($row["MSEHL"])
							);

							$json .= json_encode($arr);
						}

						$no++;
					}

					$json .= ']}';
					echo $json;
				} else {
					header("HTTP/1.1 200 OK");
				}
			}
		}
	}

	public function store_loc(Request $request)
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.37:8000/sap/bc/soap/wsdl11?services=ZFMDB_STORE_LOC&sap-client=520',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);
		$client->setCredentials("tap1", "abaptap092013");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$werks = '';

		if (strtoupper($request->werks) == 'ALL') {
			$param = array(
				'Symbol' => 'it_store_loc',
				'P_WERKS' => '',
				'P_LGORT' => ''
			);
		} else {
			$param = array(
				'Symbol' => 'it_store_loc',
				'P_WERKS' => $request->werks,
				'P_LGORT' => $request->lgort
			);
		}

		$result = $client->call('ZFMDB_STORE_LOC', array('parameters' => $param), '', '', false, true);

		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result["IT_STORE_LOC"]);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				if (isset($result["IT_STORE_LOC"]["item"])) {
					$data = $result["IT_STORE_LOC"]; //["item"];
					$json = '{"data":[';
					$no = 1;
					foreach ($data as $row) {
						if ($no > 1) {
							$json .= ",";
						}

						$json .= json_encode($row);
						$no++;
					}

					$json .= ']}';
					echo $json;
				} else {
					header("HTTP/1.1 200 OK");
				}
			}
		}
	}


	public function select_po(Request $request)
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.140:8000/sap/bc/soap/wsdl11?services=ZFMAMS_SELECT_PO&sap-client=700',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);
		$client->setCredentials("tap1", "tap12345678");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$param = array(
			'Symbol' => 'it_ekpo',
			'P_EBELN' => $request->no_po
		);


		$result = $client->call('ZFMAMS_SELECT_PO', array('parameters' => $param), '', '', false, true);



		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result["IT_PO"]);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {

				//dd(sizeof($result["IT_EKPO"],1));

				if (isset($result["IT_EKPO"]["item"])) {
					if (sizeof($result["IT_EKPO"], 1) <= 17) {
						$data = $result["IT_EKPO"];
					} else {
						$data = $result["IT_EKPO"]["item"];
					}


					foreach ($data as $row) {
						$json = '{ "EBELN":"' . utf8_decode($row["EBELN"]) . '",';
						$json .= '"AEDAT":"' . utf8_decode($row["AEDAT"]) . '",';
						$json .= '"LIFNR":"' . utf8_decode($row["LIFNR"]) . '",';
						$json .= '"NAME1":"' . utf8_decode($row["NAME1"]) . '",';
					}

					$json .= '"DETAIL_ITEM":[';
					$no = 1;
					foreach ($data as $row) {
						if ($no > 1) {
							$json .= ",";
						}
						$arr = array(
							'EBELP' => utf8_decode($row['EBELP']),
							'MATNR' => utf8_decode($row['MATNR']),
							'MAKTX' => utf8_decode($row['MAKTX']),
							'MENGE' => utf8_decode($row['MENGE']),
							'MEINS' => utf8_decode($row['MEINS']),
							'NETPR' => utf8_decode($row['NETPR']),
							"EBELP" => utf8_decode($row["EBELP"]),
							"MATNR" => utf8_decode($row["MATNR"]),
							"MAKTX" => utf8_decode($row["MAKTX"]),
							"MENGE" => round(utf8_decode($row["MENGE"]), 3),
							"MEINS" => utf8_decode($row["MEINS"]),
							"NETPR" => utf8_decode($row["NETPR"]),
							"WERKS" => utf8_decode($row["WERKS"])
						);
						$json .= json_encode($arr);
						$no++;
					}

					$json .= ']}';
					echo $json;
				} else {
					$result = array(
						"code" => 200,
						"status" => "success",
						"message" => "data 0",
						"data" => []
					);

					//return Response::json($result,200);
					return response()->json($result, 200);
				}
			}
		}
	}


	public function create_material()
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.37:8000/sap/bc/soap/wsdl11?services=ZMM_CREATE_MATERIAL&sap-client=520',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);
		$client->setCredentials("tap1", "abaptap092013");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$param = array(
			'Symbol' => 'it_return',
			'IT_MATNR'		=> Input::get('material_number'),
			'IT_MBRSH' 		=> Input::get('industri_sector'),
			'IT_MTART' 		=> Input::get('material_type'),
			'IT_WERKS' 		=> Input::get('plant'),
			'IT_LGORT' 		=> Input::get('store_loc'),
			'IT_VKORG' 		=> Input::get('sales_org'),
			'IT_VTWEG' 		=> Input::get('dist_channel'),
			'IT_MAKTX' 		=> Input::get('material_name'),
			'IT_MEINS' 		=> Input::get('uom'),
			'IT_MATKL' 		=> Input::get('mat_group'),
			'IT_SPART' 		=> Input::get('division'),
			'IT_ITEM_CAT' => Input::get('item_cat_group'),
			'IT_BRGEW' 		=> Input::get('gross_weight'),
			'IT_NTGEW' 		=> Input::get('net_weight'),
			'IT_VOLUM' 		=> Input::get('volume'),
			'IT_GROES' 		=> Input::get('size_dimension'),
			'IT_GEWEI' 		=> Input::get('weight_unit'),
			'IT_VOLEH' 		=> Input::get('volume_unit'),
			'IT_SKTOF' 		=> Input::get('cash_discount'),
			'IT_TAX_DATA' => Input::get('tax_classification'),
			'IT_KTGRM' 		=> Input::get('account_assign'),
			'IT_MTPOS' 		=> Input::get('general_item'),
			'IT_MTVFP' 		=> Input::get('avail_check'),
			'IT_TRAGR' 		=> Input::get('transportation_group'),
			'IT_LADGR' 		=> Input::get('loading_group'),
			'IT_PRCTR' 		=> Input::get('profit_center'),
			'IT_DISMM' 		=> Input::get('mrp_type'),
			'IT_DISPO' 		=> Input::get('mrp_controller'),
			'IT_IPRKZ' 		=> Input::get('period_sle'),
			'IT_BKLAS' 		=> Input::get('valuation_class'),
			'IT_PEINH' 		=> Input::get('price_unit'),
			'IT_VERPR' 		=> Input::get('price_estimate')
		);


		$result = $client->call('ZMM_CREATE_MATERIAL', array('parameters' => $param), '', '', false, true);
		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result["IT_RETURN"]);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				if (isset($result["IT_RETURN"]["item"])) {
					$data = $result["IT_RETURN"]; //["item"];
					$json = '{"data":[';
					$no = 1;
					foreach ($data as $row) {
						if ($no > 1) {
							$json .= ",";
						}
						$json .= json_encode($row);
						$no++;
					}
					$json .= ']}';
					echo $json;
				} else {
					header("HTTP/1.1 200 OK");
				}
			}
		}
	}
	//ZFMAMS_CHECK_IO

	public function check_io(Request $request)
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.140:8000/sap/bc/soap/wsdl11?services=ZFMAMS_CHECK_IO&sap-client=700',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);
		$client->setCredentials("tap1", "tap12345678");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$param = array(
			'Symbol'		=> 'RET_BAPI',
			'IT_AUFNR'  	=> $request->AUFNR,
			'IT_ASSET' 		=> $request->AUFUSER3
		);


		$result = $client->call('ZFMAMS_CHECK_IO', array('parameters' => $param), '', '', false, true);
		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				if (isset($result["RET_BAPI"])) {
					$data = $result["RET_BAPI"];
					echo json_encode($data);
				} else {
					header("HTTP/1.1 200 OK");
				}
			}
		}
	}
	public function check_gi(Request $request)
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.140:8000/sap/bc/soap/wsdl11?services=ZFMAMS_CHECK_GI&sap-client=700',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);
		$client->setCredentials("tap1", "tap12345678");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$param = array(
			'Symbol'		=> 'RET_BAPI',
			'MBLNR'  		=> $request->MBLNR,
			'MJAHR' 		=> $request->MJAHR,
			'ANLN1' 		=> $request->ANLN1,
			'ANLN2' 		=> $request->ANLN2
		);


		$result = $client->call('ZFMAMS_CHECK_GI', array('parameters' => $param), '', '', false, true);
		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				if (isset($result["RET_BAPI"])) {
					$data = $result["RET_BAPI"];
					echo json_encode($data);
				} else {
					header("HTTP/1.1 200 OK");
				}
			}
		}
	}


	/*

$param = array( 'Symbol'		=> 'RET_BAPI',
						'ANLKL'  		=> $request->ANLA_ANLKL,
						'BUKRS' 		=> $request->ANLA_BUKRS,
						'NASSETS' 		=> $request->RA02S_NASSETS,
						'TXT50' 		=> $request->ANLA_TXT50,
						'TXA50' 		=> $request->ANLA_TXA50,
						'ANLHTXT' 		=> $request->ANLH_ANLHTXT,
						'SERNR' 		=> $request->ANLA_SERNR,
						'INVNR' 		=> $request->ANLA_INVNR,
						'MENGE' 		=> $request->ANLA_MENGE,
						'MEINS' 		=> $request->ANLA_MEINS,
						'AKTIV' 		=> $request->ANLA_AKTIV,
						'DEAKT' 		=> $request->ANLA_DEAKT,
						'GSBER' 		=> $request->ANLZ_GSBER,
						'KOSTL' 		=> $request->ANLZ_KOSTL,
						'WERKS' 		=> $request->ANLZ_WERKS,
						'LIFNR' 		=> $request->ANLA_LIFNR,
						'NDJAR_01' 		=> $request->ANLB_NDJAR_01,
						'NDJAR_02' 		=> $request->ANLB_NDJAR_02,
					);			

*/
	public function edit_asset(Request $request)
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.140:8000/sap/bc/soap/wsdl11?services=ZFMAMS_EDIT_ASSET&sap-client=700',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);
		$client->setCredentials("tap1", "tap12345678");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$param = array(
			'Symbol'		=> 'MESSTAB',
			'ANLN1'  		=> $request->asset_num,
			'ANLN2'  		=> $request->subnumber,
			'BUKRS' 		=> $request->company_code,
			'TXT50' 		=> $request->asset_desc,
			'TXA50' 		=> $request->additional_desc,
			'ANLHTXT' 	=> $request->asset_main_txt,
			'SERNR' 		=> $request->serial_number,
			'INVNR' 		=> $request->inv_number,
			'MENGE' 		=> $request->qty,
			'MEINS' 		=> $request->satuan,
			'AKTIV' 		=> $request->capitalize_on,
			'DEAKT' 		=> $request->deactivation_on,
			'GSBER' 		=> $request->ba_code,
			'KOSTL' 		=> $request->cost_center,
			'WERKS' 		=> $request->plant,
			'LIFNR' 		=> $request->vendor,
			'NDJAR_01' 	=> $request->plan_life,
			'NDJAR_02' 	=> $request->plan_life2,
		);


		$result = $client->call('ZFMAMS_EDIT_ASSET', array('parameters' => $param), '', '', false, true);
		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result["MESSTAB"]);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				if (isset($result["MESSTAB"]["item"])) {
					$data = $result["MESSTAB"]; //["item"];
					$json = '{"data":[';
					$no = 1;
					foreach ($data as $row) {
						if ($no > 1) {
							$json .= ",";
						}
						$json .= json_encode($row);
						$no++;
					}
					$json .= ']}';
					echo $json;
				} else {
					header("HTTP/1.1 200 OK");
				}
			}
		}
	}
	public function create_asset(Request $request)
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client(
			'http://10.20.1.140:8000/sap/bc/soap/wsdl11?services=ZFMAMS_CREATE_ASSET&sap-client=700',
			'wsdl',
			$proxyhost,
			$proxyport,
			$proxyusername,
			$proxypassword
		);
		$client->setCredentials("tap1", "tap12345678");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$param = array(
			'Symbol'		=> 'RET_BAPI',
			'ANLKL'  		=> $request->ANLA_ANLKL,
			'BUKRS' 		=> $request->ANLA_BUKRS,
			'NASSETS' 	=> $request->RA02S_NASSETS,
			'TXT50' 		=> $request->ANLA_TXT50,
			'TXA50' 		=> $request->ANLA_TXA50,
			'ANLHTXT' 	=> $request->ANLH_ANLHTXT,
			'SERNR' 		=> $request->ANLA_SERNR,
			'INVNR' 		=> $request->ANLA_INVNR,
			'MENGE' 		=> $request->ANLA_MENGE,
			'MEINS' 		=> $request->ANLA_MEINS,
			'AKTIV' 		=> $request->ANLA_AKTIV,
			'DEAKT' 		=> $request->ANLA_DEAKT,
			'GSBER' 		=> $request->ANLZ_GSBER,
			'KOSTL' 		=> $request->ANLZ_KOSTL,
			'WERKS' 		=> $request->ANLZ_WERKS,
			'LIFNR' 		=> $request->ANLA_LIFNR,
			'NDJAR_01' 	=> $request->ANLB_NDJAR_01,
			'NDJAR_02' 	=> $request->ANLB_NDJAR_02,
		);


		$result = $client->call('ZFMAMS_CREATE_ASSET', array('parameters' => $param), '', '', false, true);
		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result["RET_BAPI"]);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				if (isset($result["RET_BAPI"]["item"])) {
					$data = $result["RET_BAPI"]; //["item"];
					//			$json = '{"data":[';
					//			$no = 1;
					//			foreach ($data as $row) {
					//					if ($no > 1) {
					//						$json .= ",";
					//					}
					//					$json .= json_encode($row);
					//				$no++;
					//			}
					//			$json .= ']}';
					//			echo $json;
					echo json_encode($data);
				} else {
					header("HTTP/1.1 200 OK");
				}
			}
		}
	}

	/*
$client = new nusoap_client(
    'http://10.20.1.140:8000/sap/bc/soap/wsdl11?services=ZFMDB_UOM&sap-client=700<http://10.20.1.140:8000/sap/bc/soap/wsdl11?services=ZFMDB_GROUPMATERIAL&sap-client=700', 'wsdl',
						$proxyhost, $proxyport, $proxyusername, $proxypassword);
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}

$param = array('Symbol' => 'it_uom');
$result = $client->call('ZFMDB_UOM', array('parameters' => $param), '', '', false, true);

if ($client->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result["IT_UOM"]);
	echo '</pre>';
} else {
	$err = $client->getError();
	if ($err) {
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
        $data = $result["IT_UOM"]["item"];
		$json = '{"data":[';
		$no = 1;
		foreach ($data as $row) {
            if($row["MANDT"]) {
                if ($no > 1) {
                    $json .= ",";
                }
                $arr = array(
                    "MANDT" => $row["MANDT"],
                    "SPRAS" => $row["SPRAS"],
                    "MSEHI" => utf8_decode($row["MSEHI"]) ,
                    "MSEH3" => $row["MSEH3"],
                    "MSEH6" => $row["MSEH6"],
                    "MSEHT" => $row["MSEHT"],
                    "MSEHL" => $row["MSEHL"]
                );

                $json .= json_encode($arr);
            }

			$no++;
        }
        
		$json .= ']}';
		echo $json;
	}
}
*/

	public function send_email_fams(Request $request)
	{
		$data = $request->all();

		/*
		stdClass Object
		(
			[content] => a:3:{i:0;s:20:"19.07/AMS/PDFA/00132";i:1;i:1;i:2;i:2;}
		)
	*/

		//$dt = unserialize($data->data_asset);

		//$document_code = str_replace("-", "/", $data['noreg']); 
		//echo "7<pre>"; print_r($data); die();

		/*
	// 1. DATA ASSET 
	$sql = " SELECT * FROM v_email_approval WHERE document_code = '{$document_code}' ";
	//$dt = DB::SELECT($sql);
	$dt = DB::connection('mysql_EC2')->select($sql);
	*/

		//$dt = 2;

		//$req = base64_decode($dt);
		//$data = unserialize($req);

		echo "90=<pre>";
		db($data);
		die();

		//$data = $req;
		echo json_encode($data);
	}
}
