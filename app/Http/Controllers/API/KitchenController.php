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
        // return 1;
        $model = new Pesanan();

        try {
            return $model->get_menu_kitchen();
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }

    }

    function confirmation_menu($id, Request $request)
    {
        try {
            $pesananDetail = PesananDetail::findOrFail($id);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
        
        if ($pesananDetail->status == 2) {
            return $this->return_failed('sudah tidak bisa diubah!');
        }
        
        try {
            $pesananDetail->update([
                'status' => $request->status
            ]);
            
            if ($request->status == 1) {
                return $this->return_success('pesanan sudah siap dihidangkan!', []);
            } else {
                try {
                    $pesananDetail->update([
                        'keterangan' => $request->keterangan,
                        'harga' => 0,
                        'sub_harga' => 0,
                        'qty' => 0
                    ]);
                } catch (\Throwable $th) {
                    return $this->return_failed($th->getMessage());
                }
                return $this->return_success('pesanan batal dihidangkan!', []);
            }
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }
}
