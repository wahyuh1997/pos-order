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
                    (case when harga is null then 0 else (sub_harga*qty) end) harga
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
            $data[$i]['order_detail'] = $this->get_all_detail($data[$i]['id']);
            $data[$i]['kode_unik'] = Crypt::encryptString($data[$i]['id']);
        }


        return $data;

    }

    function create_no_pesanan()
    {
        $date =  \date('Y-m-d');
        $last_pesanan = DB::table('pesanan')->whereRaw("cast(created_at as date) = current_date")->orderBy('created_at', 'desc')->first();
        return sprintf("%03d", (Int)$last_pesanan->no_order + 1);
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

    function total_pengunjung()
    {
        $sql = "
                SELECT count(*) FROM pesanan
                WHERE created_at BETWEEN cast(NOW() - INTERVAL 1 DAY AS date) AND CURRENT_DATE
                ";
    }
    
    function pendapatan_harian()
    {
        $sql = "
                SELECT sum(b.harga*qty) as pendapatan_harian 
                FROM pesanan a
                LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 2
                WHERE a.created_at BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
                ";
    }
    
    function menu_terjual_harian()
    {
        $sql = "
                SELECT sum(qty) as menu_harian 
                FROM pesanan a
                LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 2
                WHERE a.created_at BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
                ";
    }
    
    function penjualan_bulanan()
    {
        $sql = "
                SELECT sum(qty) as menu_harian 
                FROM pesanan a
                LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 2
                WHERE a.created_at BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
                ";
    }

    function top_menu()
    {
        $sql = "
                select nama_menu, attribute
                from (
                    select max(a.nama_menu) as nama_menu
                    , max(b.name_attribute) as attribute
                    , sum(qty) as jumlah
                    from menu a
                    left join pesanan_detail b on b.menu_id = a.id
                    WHERE b.created_at BETWEEN cast(NOW() - INTERVAL 30 DAY AS date) AND CURRENT_DATE
                    group by a.id, b.name_attribute
                    order by a.id
                ) as a
                order by a.jumlah desc 
                limit 1
                ";
        $sql = "
                FROM pesanan a
                LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 2
                LEFT JOIN  menu c on c.id = b.menu_id
                WHERE a.created_at BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
                GROUP BY 
                ";
    }
}
