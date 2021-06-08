<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelJabatan extends Model
{
    use HasFactory;

    protected $table = "tb_level_jabatan";
    protected $primaryKey  = "id_level_jabatan";
    protected $fillable = [
        'id_jabatan',
        'level',
     ];
}
