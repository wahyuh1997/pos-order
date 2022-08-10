<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    protected $fillable = ['no_order','no_receip','meja_id','nama_pelanggan','status','checkout'];

    function get_field($request){
        // return $this->fillable;
        $data = [];
        foreach ($request->data as $key => $value) {
            if (in_array($key,$this->fillable)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    public function getUpdatedAtAttribute()
    {
    return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute()
    {
    return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i:s');
    }

    function get_pesanan($id = null)
    {

        $sql = "select a.*, b.no_meja
                ,(select 
                    sum((case when sub_harga*qty is null then 0 else (sub_harga*qty) end)) harga
                from pesanan_detail 
                where pesanan_id = a.id
                ) as total_harga
                , a.meja_id, b.no_meja
                ,a.updated_at, a.created_at
                from $this->table a
                left join meja b on b.id = a.meja_id
                ";
        if (strlen($id) > 0) {
            $sql .= "where a.id = $id ";
        }
        $sql .= "order by a.created_at desc";
        
        $data = json_decode(json_encode(DB::select($sql)), true);


        for ($i=0; $i <count($data); $i++) { 
            $data[$i]['kode_unik'] = $this->encrypt_decrypt('encrypt', $data[$i]['id']);
            $data[$i]['order_detail'] = $this->get_all_detail($data[$i]['id']);
        }


        return $data;

    }

    function create_no_pesanan()
    {
        $date =  \date('Y-m-d');
        $last_pesanan = DB::table('pesanan')->whereRaw("cast(created_at as date) = current_date")->orderBy('created_at', 'desc')->first();
        if ($last_pesanan) {
            return sprintf("%03d", (Int)$last_pesanan->no_order + 1);
        } else {
            return "001";
        }
    }

    function get_all_detail($id)
    {
        $sql = "select a.id, a.harga, a.sub_harga, a.qty, a.menu_id, b.nama_menu, a.name_attribute, a.status, a.created_at, a.updated_at
                from pesanan_detail a
                left join menu b on a.menu_id = b.id
                where a.pesanan_id = :pesanan_id
        ";
        return json_decode(json_encode(DB::select($sql, ['pesanan_id' => $id])), true);
    }
    
    private function total_pengunjung()
    {
        $sql = "
        SELECT count(*) as total_pengunjung FROM pesanan
        WHERE cast(created_at AS date) BETWEEN cast(NOW() - INTERVAL 1 DAY AS date) AND CURRENT_DATE
        ";
        return json_decode(json_encode(DB::select($sql)), true);
    }
    
    private function pendapatan_harian()
    {
        $sql = "
        SELECT sum(b.harga*qty) as pendapatan_harian 
        FROM pesanan a
        LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 1
        WHERE cast(a.created_at as date) BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
        ";
        return json_decode(json_encode(DB::select($sql)), true);
        // return $sql;
    }
    
    private function menu_terjual_harian()
    {
        $sql = "
        SELECT sum(qty) as menu_harian 
        FROM pesanan a
        LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 1
        WHERE cast(a.created_at as date) BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
        ";
        return json_decode(json_encode(DB::select($sql)), true);
    }
    
    private function penjualan_bulanan()
    {
        $sql = "
        SELECT sum(qty) as menu_harian 
        FROM pesanan a
        LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 1
        WHERE cast(a.created_at as date) BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
        ";
        return json_decode(json_encode(DB::select($sql)), true);
    }
    
    private function top_menu()
    {
        $sql = "
        select nama_menu, attribute
        from (
            select max(a.nama_menu) as nama_menu
            , max(b.name_attribute) as attribute
            , sum(qty) as jumlah
            from menu a
            left join pesanan_detail b on b.menu_id = a.id and b.status = 1
            WHERE cast(b.created_at as date) BETWEEN cast(NOW() - INTERVAL 30 DAY AS date) AND CURRENT_DATE
            group by a.id, b.name_attribute
            order by a.id
        ) as a
        order by a.jumlah desc 
        limit 5
        ";
        
        return json_decode(json_encode(DB::select($sql)), true);
    }

    function get_dashboard()
    {
        $data = [
            'total_pengunjung' => $this->total_pengunjung()['total_pengunjung'],
            'pendapatan_harian' => $this->pendapatan_harian()['pendapatan_harian'],
            'menu_terjual_harian' => $this->menu_terjual_harian()['menu_terjual_harian'],
            'penjualan_bulanan' => $this->penjualan_bulanan()['penjualan_bulanan'],
            'top_menu' => $this->top_menu()['top_menu'],
        ];
        return $data;
    }

    function encrypt_decrypt($action, $string){
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'key_one';
        $secret_iv = 'key_two';
        // hash
        $key = hash('sha256', $secret_key);
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}
        