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

        try {
            return $this->return_success('',$model->get_pesanan($id)[0]);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }

    }

    function insert_order(Request $request){
        $model = new Pesanan();

        $data = $this->get_field_pesanan_request($request);
        try {
            $data['no_order'] = $model->create_no_pesanan();
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
        $pesanan = Pesanan::create($data);
    
        return $this->return_success('Menu berhasil ditambahkan!', $model->get_pesanan($pesanan->id)[0]);
    }

    function insert_order_detail(Request $request)
    {
        try {
            $model = new Pesanan();
            $pesanan = Pesanan::findOrFail($request->pesanan_id);
            if ($pesanan->checkout == 0) {
                PesananDetail::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $request->menu_id,
                    'name_attribute' => $request->name_attribute,
                    'harga' => $request->harga,
                    'sub_harga' => $request->sub_harga,
                    'qty' => $request->qty, 
                    'status' => 0,
                ]);
                return $this->return_success('',$model->get_pesanan($pesanan->id)[0]);
            } else {
                return $this->return_failed('Orderan anda sudah selesai, silahkan ke kasir untuk membuat orderan baru');
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
            // $id = Crypt::decryptString($code);
            $id = base64_decode($code);
            $id =$this->encrypt_decrypt('decrypt',$code);
            $model = new Pesanan();
            return $this->return_success('', $model->get_pesanan()[0]);
        } catch (\Throwable $e) {
            return $this->return_failed($e->getMessage());
        }
    }
    
    function delete_order_detail($id)
    {
        try {
            PesananDetail::findOrFail($id)->delete();
            return $this->return_success('Berhasil dihapus', []);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
    }
    
    function update_order_detail($id, Request $request)
    {
        try {
            $pesananDetail = PesananDetail::findOrFail($id);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
        try {
            $pesananDetail->update([
                'harga' => $request->harga,
                'sub_harga' => $request->sub_harga,
                'qty' => $request->qty
            ]);
        } catch (\Throwable $th) {
            return $this->return_failed($th->getMessage());
        }
        $model = new Pesanan();

        return $this->return_success('Berhasil diubah!', $model->get_pesanan($pesananDetail->id)[0]);
    }
    
    function final_order_detail($id, Request $request)
    {
        try {
            Pesanan::findOrfail($id)->update([
                'pajak' => $request->pajak,
                'total_harga' => $request->total_harga,
                'sub_total' => $request->sub_total
            ]);
        } catch (\Throwable $e) {
            return $this->return_failed($e->getMessage());
        }

        try {
            $pesananDetail = PesananDetail::where(['pesanan_id' => $id, 'status' => 0]);
        } catch (\Throwable $e) {
            return $this->return_failed($e->getMessage());
        }
        
        try{
            $pesananDetail->update([
                'status' => 2
            ]);
        } catch (\Throwable $e) {
            return $this->return_failed($e->getMessage());
        }
        
        return $this->return_success('Menu akan di proses!', []);
    }
    
    function history_all_order(){
        $model = new Pesanan();
        try {
            return $model->get_history_order();
            //code...
        } catch (\Throwable $e) {
            //throw $th;
            return $this->return_failed($e->getMessage());
        }
    }
    
    function final_pembayaran($id, Request $request)
    {
        try {
            $pesanan = Pesanan::findOrFail($id);
        } catch (\Throwable $e) {
            return $this->return_failed($e->getMessage());
        }
        
        try {
            $pesanan->update([
                'pajak' => $request->pajak,
                'total_harga' => $request->total_harga,
                'sub_total' => $request->sub_total,
                'sub_total' => $request->sub_total,
                'total_harga' => $request->total_harga,
                'pajak' => $request->total_harga,
                'bayar' => $request->bayar,
                'kembalian' => $request->kembalian,
                'payment_type' => $request->payment_type,
                'no_receip' => date('Ymd').$pesanan->no_receip,
                'status' => 1,
                'checkout' => 1
            ]);
        } catch (\Throwable $e) {
            return $this->return_failed($e->getMessage());
        }

        return $this->return_success('Pesanan sudah selesai!', []);
    }
    // ini commit

    
}
