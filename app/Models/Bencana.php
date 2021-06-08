<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class Bencana extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = "tb_bencana";
    protected $primaryKey  = "id_bencana";
    protected $fillable = [
        'id_user',
        'gambar',
        'jenis',
        'judul',
        'id_kecamatan',
        'id_desa',
        'id_dusun',
        'lokasi',
        'koordinat',
        'tgl_kejadian',
        'keterangan',
        'status',
     ];
}
