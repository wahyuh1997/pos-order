<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Meja extends Model
{
    use HasFactory;

    protected $table = 'meja';

    protected $fillable = ['no_meja'];

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

    public function get_meja()
    {
        $sql = "select a.id, a.no_meja, a.created_at, a.updated_at
                from $this->table a
                where id not in (SELECT meja_id
                            FROM pesanan
                            where cast(created_at as date) = current_date and status = 0)
                ";
        
        return $data = json_decode(json_encode(DB::select($sql)), true);
    }
}
