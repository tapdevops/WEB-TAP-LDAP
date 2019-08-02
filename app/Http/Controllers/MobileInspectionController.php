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

class MobileInspectionController extends Controller {

	public function export_inspeksi() {
		print 'ABC';
	}

}