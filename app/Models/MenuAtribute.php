<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAtribute extends Model
{
    use HasFactory;

    protected $table = 'attribute';
    protected $fillable = ['nama', 'menu_id','harga'];

}
