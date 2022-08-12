<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';
    protected $fillable = ['nama_menu', 'jenis', 'kategori_id', 'harga','image','status','keterangan'];
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->timestamps = false;
            $model->created_at = now();
        });
    }

    public function getUpdatedAtAttribute()
    {
    return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute()
    {
    return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i:s');
    }

    function get_menu($id = null)
    {

        $sql = "select a.id, a.nama_menu, a.jenis, a.keterangan, b.nama_kategori, a.kategori_id, a.harga, a.image, a.updated_at, a.status, a.created_at
                from $this->table a
                left join menu_kategori b on a.kategori_id = b.id
                
                ";
        if (strlen($id) > 0) {
            $sql .= "where a.id = $id";
        }
        
        $data = json_decode(json_encode(DB::select($sql)), true);

        for ($i=0; $i <count($data); $i++) { 
            $data[$i]['atribute'] = $this->get_all_atribute($data[$i]['id']);
        }

        return $data;
    }

    function get_all_atribute($id)
    {
        $sql = "select nama, harga
                from attribute
                where menu_id = $id
        ";
        return json_decode(json_encode(DB::select($sql)), true);
    }
}
