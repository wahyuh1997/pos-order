<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    function get_dashboard()
    {
        $model = new Pesanan();

        return $model->get_dashboard();
    }
}
