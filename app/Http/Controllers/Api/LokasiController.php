<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Dusun;

class LokasiController extends Controller
{
    public function getKecamatan(){
        $data = Kecamatan::get();

        return response()->json([
            'success' => "true", 
            'msg' => $data], 200);
    }

    public function getDesa(){
        $data = Desa::get();

        return response()->json([
            'success' => "true", 
            'msg' => $data], 200);
    }

    public function getDusun(){
        $data = Dusun::get();

        return response()->json([
            'success' => "true", 
            'msg' => $data], 200);
    }
}
