<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAtribute extends Model
{
    use HasFactory;

    protected $table = 'attribute';
    protected $fillable = ['nama', 'menu_id','harga'];

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
