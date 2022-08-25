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

        try {
            if (count($model->get_menu_kitchen()) == 0) {
                return $this->return_failed('data tidak ada!');
            }
            return $this->return_success('', $model->get_menu_kitchen());
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
        
        if ($pesananDetail->status != 2) {
            return $this->return_failed('sudah tidak bisa diubah!');
        }
        
        try {
            $pesananDetail->update([
                'status' => $request->status
            ]);
            
            if ($request->status == 1) {
                return $this->return_success('pesanan sudah siap dihidangkan!', []);
            } else if($request->status == 3) {
                try {
                    $pesanan = Pesanan::findOrFail($pesananDetail->pesanan_id);
                    $pesanan->update([
                        'sub_total' => $pesanan->sub_total - $pesananDetail->sub_harga,
                        'pajak' => $pesanan->pajak - ($pesananDetail->sub_harga * 10/100),
                        'total_harga' => $pesanan->total_harga - (($pesananDetail->sub_harga * 10/100) + $pesananDetail->sub_harga),
                    ]);
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
