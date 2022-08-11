<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuKategori extends Model
{
    use HasFactory;

    protected $table = 'menu_kategori';

    protected $fillable = ['nama_kategori'];

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

}
