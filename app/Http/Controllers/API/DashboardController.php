<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    function get_dashboard(Request $request)
    {
        $model = new Pesanan();
        return $this->return_success('', $model->get_dashboard($request->tahun??null));
    }
    
    function get_report(Request $request)
    {
        $model = new PesananDetail();

        if ($request->type  == 2) {
            return $this->return_success('Laporan Product', $model->report_product($request->start??\date('Y-m-d'), $request->end??\date('Y-m-d')));
        } elseif ($request->type == 1){
            return $this->return_success('Laporan Penjualan', $model->report_penjualan($request->start??\date('Y-m-d'), $request->end??\date('Y-m-d')));
        } else {
            return $this->return_failed('Type is wrong!');
        }
        
    }
}
