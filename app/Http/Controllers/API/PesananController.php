<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PesananController extends Controller
{
    function get_all_order()
    {
        $model = new Pesanan();

        return $this->return_success('',$model->get_pesanan());
    }
    
    function get_order($id)
    {
        $model = new Pesanan();

        return $this->return_success('',$model->get_pesanan($id));
    }

    function insert_order(Request $request){
        $model = new Pesanan();

        $data = $this->get_field_pesanan_request($request);
        $data['no_order'] = $model->create_no_pesanan();
        $pesanan = Pesanan::create($data);
    
        return $this->return_success('Menu berhasil ditambahkan!', $model->get_pesanan($pesanan->id));
    }

    function insert_order_detail(Request $request)
    {
        try {
            $model = new Pesanan();
            $pesanan = Pesanan::find($request->pesanan_id);
            if ($pesanan->checkout == 0) {
                for ($i=0; $i <count($request->data); $i++) { 
                    $data = [];
                    $data['pesanan'] = $pesanan->id;

                    $insert = $request->data[$i];
                    foreach ($insert as $key => $value) {
                        $data[$key] = $value;
                    }
                    PesananDetail::create($data);
                }
                return $this->return_success('',$model->get_pesanan($pesanan->id));
            } else {
                return $this->return_failed('Orderan anda sudah selesai');
            }
        } catch (Exception $e) {
            return $this->return_failed($e->getMessage());
        }
    }

    function update_order($id, Request $request)
    {
        $model = new Pesanan();
        $data = $model->get_field($request);

        try {
            $pesanan = Pesanan::findOrFail($id);

            $pesanan->update($data);
            return $this->return_success('berhasil!', []);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }
    
    function update_order_detail($id, Request $request)
    {
        try {
            $pesanan = PesananDetail::findOrFail($id);

            $pesanan->update([
                'status' => $request->data[['status']]
            ]);
            return $this->return_success('berhasil!', []);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }

    private function get_field_pesanan_request($request)
    {
        $data = [
            'nama_pelanggan' => $request->nama_pelanggan,
            'meja_id' => $request->meja_id,
        ];

        return $data;
    }

    function get_qr_code($code)
    {
        try {
            $id = Crypt::decryptString($code);
            $model = new Pesanan();
            return $this->return_success('', $model->get_pesanan());
        } catch (\Throwable $e) {
            return $this->return_failed($e->getMessage());
        }

        
    }
}
