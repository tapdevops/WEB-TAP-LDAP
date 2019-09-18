<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
	return response()->json([
		"message" => "Microservice LDAP"
	]);
});




Route::post('/login', 'AuthController@login');

Route::get('/export', 'ExportController@index');

// Export & Sync Mongo Mobile Inspection
Route::get('/sync/tr-land-use', 'ExportController@sync_tr_land_use');
Route::get('/sync/tm-region', 'ExportController@sync_tm_region');
Route::get('/sync/tm-est', 'ExportController@sync_tm_est');
Route::get('/sync/tm-afd', 'ExportController@sync_tm_afd');
Route::get('/sync/tm-block', 'ExportController@sync_tm_block');
Route::get('/sync/tm-comp', 'ExportController@sync_tm_comp');
Route::get('/sync/tr-class-block', 'ExportController@sync_tr_class_block');
Route::get('/sync/tm-kualitas', 'ExportController@sync_tm_kualitas');
Route::get('/export/tm-employee-hris', 'ExportController@sync_tm_employee_hris');
Route::get('/export/tm-employee-sap', 'ExportController@sync_tm_employee_sap');
Route::get('/sync/mobile-inspection', 'ExportController@sync_mobile_inspection');
Route::get('/sync/generate-token', 'ExportController@generate_token');
Route::get('/sync/tr-premi-inspection', 'ExportController@sync_tr_premi_inspection');
Route::get('/sync/mobile-estate/tr-ebcc', 'ExportController@sync_mobile_estate_tr_ebcc');
Route::get('/sync/mobile-estate/tr-ebcc-kualitas', 'ExportController@sync_mobile_estate_tr_ebcc_kualitas');
Route::get('/sync/mobile-estate/tr-image', 'ExportController@sync_mobile_estate_tr_image');

Route::get('/sync/tapdw/tr-inspection', 'ExportController@sync_tapdw_tr_inspection');

Route::get('/sync/tapdw/tr-inspection/{start_date}/{end_date}', 'ExportController@sync_tapdw_tr_inspection');

Route::get('/sync/tapdw/tr-inspection-xxx', 'ExportController@sync_tapdw_tr_inspection_harian');



Route::get('/kafka', 'ExportController@test_kafka');

# SOAP
Route::get('/data-sap/material_group', 'GetDataSAPController@material_group');
Route::get('/data-sap/uom', 'GetDataSAPController@uom');
Route::get('/data-sap/store_loc/{werks}/{lgort}', 'GetDataSAPController@store_loc');
Route::get('/data-sap/store_loc/{werks}', 'GetDataSAPController@store_loc');
Route::get('/data-sap/sync_material', 'GetDataSAPController@create_material');

Route::get('/data-sap/select_po/{no_po}', 'GetDataSAPController@select_po');
Route::get('/data-sap/select_po', 'GetDataSAPController@select_po');
Route::get('/data-sap/create_asset', 'GetDataSAPController@create_asset');
Route::get('/data-sap/check_io', 'GetDataSAPController@check_io');
Route::get('/data-sap/check_gi', 'GetDataSAPController@check_gi');

Route::put('/data-sap/send_email', 'AuthController@send_email_fams', ['middleware' => 'csrf']);
Route::post('/send_email_fams', 'AuthController@send_email_fams', ['middleware' => 'csrf']);
//Route::post( '/send_email_fams', 'GetDataSAPController@send_email_fams' );
//Route::get( '/data-sap/send_email_fams', 'GetDataSAPController@send_email_fams' );
//Route::post( '/data-sap/send_email_fams', 'FamsEmailController@index' );
Route::get('/showtoken', 'AuthController@showToken', ['middleware' => 'csrf']);
