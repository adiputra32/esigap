<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dusun extends Model
{
    use HasFactory;

    protected $table = "tb_dusun";
    protected $primaryKey  = "id_dusun";
    protected $fillable = [
        'id_desa',
        'dusun',
     ];
}
