<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    function get_dashboard()
    {
        $model = new Pesanan();

        return $this->return_success('', $model->get_dashboard());
    }

    function get_report(Request $request)
    {
        $model = new PesananDetail();

        return $model->report($request->from_date??\date('Y-m-d'), $request->thru_date??\date('Y-m-d'));
    }
}
