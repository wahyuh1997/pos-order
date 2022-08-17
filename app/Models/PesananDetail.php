<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PesananDetail extends Model
{
    use HasFactory;

    protected $table = 'pesanan_detail';

    protected $guarded = ['id'];

    public function getUpdatedAtAttribute()
    {
    return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute()
    {
    return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i:s');
    }

    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->timestamps = false;
            $model->created_at = now();
        });
    }
    
    public function report($from_date = null, $thru_date = null)
    {
        // $from_date = $from_date??\date('Y-m-d');
        // $from_date = '2022-08-01';
        // $thru_date = $thru_date??\date('Y-m-d');
        $sql = "
            select max(a.nama_menu) as nama_menu
            , coalesce(max(b.name_attribute), '-') as attribute
            , max(a.harga) +  coalesce(max(c.harga),0) as harga
            , sum(qty) as jumlah
            , max(b.sub_harga) as total_harga
            from menu a
            left join attribute c on a.id = c.menu_id
            left join pesanan_detail b on b.menu_id = a.id and b.status = 2
            WHERE cast(b.created_at as date) BETWEEN cast(:from_date as date) AND cast(:thru_date as date)
            group by a.id, b.name_attribute
            order by a.id
            ";
    
        $data = [];
    
        $data['report'] = json_decode(json_encode(DB::select($sql, ['from_date'=>$from_date,'thru_date'=> $thru_date])), true);
    
        $sql = "select sum(total_harga) as jumlah_pendapatan
                from ($sql) as a";
        $data['jumlah_pendapatan'] = json_decode(json_encode(DB::select($sql, ['from_date'=>$from_date,'thru_date'=> $thru_date])), true)[0]['jumlah_pendapatan'];
        return $data;
    }
}
