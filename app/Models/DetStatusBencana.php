<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetStatusBencana extends Model
{
    use HasFactory;

    protected $table = "tb_det_status_bencana";
    protected $primaryKey  = "id_det_status_bencana";
    protected $fillable = [
        'id_bencana',
        'id_jabatan_penerima',
        'perintah',
     ];
}
