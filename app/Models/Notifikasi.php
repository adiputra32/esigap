<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = "tb_notifikasi";
    protected $primaryKey  = "id_notifikasi";
    protected $fillable = [
        'id_bencana',
        'notifikasi',
        'id_user',
        'status',
     ];
}
