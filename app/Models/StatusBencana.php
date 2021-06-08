<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusBencana extends Model
{
    use HasFactory;

    protected $table = "tb_status_bencana";
    protected $primaryKey  = "id_status_bencana";
    protected $fillable = [
        'id_user',
        'tingkatan',
        'status',
     ];
}
