<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\FamsEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
//use App\TR_REG_ASSET_DETAIL;

class FamsEmailController extends Controller
{

	public function index(Request $request)
	{
		$req = $request->all();
		echo "1<pre>"; print_r($_req); die();
		
		$no_registrasi = $req['noreg'];
		$document_code = str_replace("-", "/", $no_registrasi); 
	
		// 1. DATA ASSET 
		$sql = " SELECT * FROM v_email_approval WHERE document_code = '{$document_code}' ";
		$dt = DB::SELECT($sql);

		// 2. HISTORY APPROVAL 
		$sql2 = " SELECT * FROM v_history WHERE document_code = '{$document_code}' ";
		$dt_history_approval = DB::SELECT($sql2);

		// 3. EMAIL TO
		$data = new \stdClass();
        $data->noreg = array($document_code,1,2);
        $data->jenis_pemberitahuan = 'PENDAFTARAN';
        $data->sender = 'TAP Agri';
        $data->datax = $dt;
        $data->history_approval = $dt_history_approval;

		$sql3 = " SELECT b.name, b.email FROM v_history_approval a LEFT JOIN TBM_USER 
b ON a.USER_ID = b.ID WHERE a.document_code = '{$document_code}' AND status_approval = 'menunggu' "; //echo $sql3; die();
		$dt_email_to = DB::SELECT($sql3);

		#2 IT@220719 - SEND EMAIL VIA SERVICE LDAP 
		$data->email_to = $dt_email_to;
		$service = API::exec(array(
			'request' => 'GET',
			'host' => 'ldap',
			'method' => "send_email_fams?DATA=$data", 
		));
		$data = $service;
		echo "1<pre>"; print_r($data); die();
		
		/* #1 IT@220719 
		//echo "2<pre>"; print_r($dt_email_to); die();
		if(!empty($dt_email_to))
		{
			foreach($dt_email_to as $k => $v)
			{
				$data->nama_lengkap = $v->name;
				Mail::to($v->email)
					->bcc('system.administrator@tap-agri.com')
					->send(new FamsEmail($data));
			}
		}
		*/
	}

}