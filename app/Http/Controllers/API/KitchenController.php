<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    function get_order_detail()
    {
        $model = new Pesanan();
    }

    function confirmation_menu($id, Request $request)
    {
        try {
            $pesananDetail = PesananDetail::findOrFail($id);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
        
        if (!$pesananDetail->status == 2) {
            return $this->return_failed('sudah tidak bisa diubah');
        }
        
        try {
            $pesananDetail->update([
                'status' => $request->status
            ]);

            if ($request->status == 1) {
                return $this->return_success('pesanan sudah siap dihidangkan!', []);
            } else {
                return $this->return_success('pesanan sudah siap dihidangkan!', []);
            }
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }
}
