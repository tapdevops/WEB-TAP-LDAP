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

class GetDataSAPController extends Controller {
	
	public function material_group() {
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$client = new nusoap_client('http://10.20.1.37:8000/sap/bc/soap/wsdl11?services=ZFMDB_GROUPMATERIAL&sap-client=520', 'wsdl',
						$proxyhost, $proxyport, $proxyusername, $proxypassword);
		
		$client->setCredentials("tap1","abaptap092013");
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

	public function uom() {
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
		$client->setCredentials("tap1","abaptap092013");
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
				if (isset($result["IT_UOM"]["item"])){
				$data = $result["IT_UOM"]["item"];
				$json = '{"data":[';
				$no = 1;
				foreach ($data as $row) {
					if($row["MANDT"]) {
						if ($no > 1) {
							$json .= ",";
						}
						$arr = array(
							"MANDT" => utf8_decode($row["MANDT"]),
							"SPRAS" => utf8_decode($row["SPRAS"]),
							"MSEHI" => utf8_decode($row["MSEHI"]) ,
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
			}else{
				header("HTTP/1.1 200 OK");
			}	
			}
		}
	}

public function store_loc(Request $request) {
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
		$client->setCredentials("tap1","abaptap092013");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}

		$werks = '';

		if (strtoupper($request->werks) == 'ALL') {
			$param = array('Symbol' => 'it_store_loc',
						'P_WERKS' => '',
						'P_LGORT' => '');
		}else{
			$param = array('Symbol' => 'it_store_loc',
						'P_WERKS' => $request->werks,
						'P_LGORT' => $request->lgort);
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
				if  (isset($result["IT_STORE_LOC"]["item"])) {
				$data = $result["IT_STORE_LOC"];//["item"];
				$json = '{"data":[';
				$no = 1;
				foreach ($data as $row) {
	//				if($row["WERKS"]) {
						if ($no > 1) {
							$json .= ",";
						}
	/*					$arr = array(
							"WERKS" => utf8_decode($row["WERKS"]),
							"LGOR" => utf8_decode($row["LGORT"]),
							"LGOBE" => utf8_decode($row["LGOBE"])
						);
	*/
						//$json .= json_encode($arr);
						$json .= json_encode($row);
	//				}

					$no++;
				}
				
				$json .= ']}';
				echo $json;
			}else{
				header("HTTP/1.1 200 OK");
			}
			}
		}
	}


	public function create_material() {
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
		$client->setCredentials("tap1","abaptap092013");
		$err = $client->getError();

		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}
		
		$tabel = array(	'MATNR' => Input::get('material_number'),
						'MBRSH' => Input::get('industri_sector'),
						'MTART' => Input::get('material_type'),
						'WERKS' => Input::get('plant'),
						'LGORT' => Input::get('store_loc'),
						'VKORG' => Input::get('sales_org'),
						'VTWEG' => Input::get('dist_channel'),
						'MAKTX' => Input::get('material_name'),
						'MEINS' => Input::get('uom'),
						'MATKL' => Input::get('mat_group'),
						'SPART' => Input::get('division'),
						'ITEM_CAT' => Input::get('item_cat_group'),
						'BRGEW' => Input::get('gross_weight'),
						'NTGEW' => Input::get('net_weight'),
						'VOLUM' => Input::get('volume'),
						'GROES' => Input::get('size_dimension'),
						'GEWEI' => Input::get('weight_unit'),
						'VOLEH' => Input::get('volume_unit'),
						'SKTOF' => Input::get('cash_discount'),
						'TAX_DATA' => Input::get('tax_classification'),
						'KTGRM' => Input::get('account_assign'),
						'MTPOS' => Input::get('general_item'),
						'MTVFP' => Input::get('avail_check'),
						'TRAGR' => Input::get('transportation_group'),
						'LADGR' => Input::get('loading_group'),
						'PRCTR' => Input::get('profit_center'),
						'DISMM' => Input::get('mrp_type'),
						'DISPO' => Input::get('mrp_controller'),
						'IPRKZ' => Input::get('period_sle'),
						'BKLAS' => Input::get('valuation_class'),
						'PEINH' => Input::get('price_unit'),
						'VERPR' => Input::get('price_estimate')
						 );
		$it_return = array(	'TYPE' => '',
							'ID' => '',
							'NUMBER' => '',
							'MESSAGE' => '',
							'LOG_NO' => '',
							'LOG_MSG_NO' => '',
							'MESSAGE_V1' => '',
							'MESSAGE_V2' => '',
							'MESSAGE_V3' => '',
							'MESSAGE_V4' => '',
							'PARAMETER' => '',
							'ROW' => '',
							'FIELD' => '',
							'SYSTEM' => ''
							);
		$param = array('RETURN' => $it_return,
					'IT_DATA' => $tabel);
		
		
		$result = $client->call('ZMM_CREATE_MATERIAL', array('parameters' => $param), '', '', false, true);

		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result["RETURN"]);
			echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				if  (isset($result["RETURN"]["item"])) {
				$data = $result["RETURN"];//["item"];
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
			}else{
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



















	
}
