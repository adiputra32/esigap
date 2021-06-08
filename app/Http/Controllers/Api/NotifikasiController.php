<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notifikasi;
use App\Models\User;
use Auth;

class NotifikasiController extends Controller
{
    public function getNotifikasi(Request $request){
        if ($request->take > 10) {
            $notifications = Notifikasi::where('id_user', Auth::user()->id)
                ->orderBy('id_notifikasi','DESC')
                ->take($request->take)
                ->get();
        } else {
            $notifications = Notifikasi::where('id_user', Auth::user()->id)
                ->orderBy('id_notifikasi','DESC')
                ->skip($request->skip)
                ->take($request->take)
                ->get();
        }

        foreach ($notifications as $key => $notification) {
            // tgl 
            $hari = self::convertHari(date('l', strtotime($notification['created_at'])));
            $waktu = date('H:i', strtotime($notification['created_at']));
            $tgl = date('d', strtotime($notification['created_at']));
            $bulan = self::convertBulan(date('m', strtotime($notification['created_at'])));
            $tahun = date('Y', strtotime($notification['created_at']));

            $str_tgl = $hari . ", " . $tgl . " " . $bulan . " " . $tahun . " " . $waktu . " WITA";

            $notifications[$key]['waktu'] = $str_tgl;
        }

        return response()->json([
            'success' => "true", 
            'msg' => $notifications], 200);
    }

    public function convertBulan($m){
        switch ($m) {
            case '1':
                return "Januari";
                break;
            
            case '2':
                return "Februari";
                break;

            case '3':
                return "Maret";
                break;

            case '4':
                return "April";
                break;
            
            case '5':
                return "Mei";
                break;

            case '6':
                return "Juni";
                break;

            case '7':
                return "Juli";
                break;

            case '8':
                return "Agustus";
                break;

            case '9':
                return "September";
                break;

            case '10':
                return "Oktober";
                break;

            case '11':
                return "November";
                break;

            case '12':
                return "Desember";
                break;

            default:
                return $m;
                break;
        }
    }

    public function convertHari($l){
        switch ($l) {
            case 'Sunday':
                return "Senin";
                break;
            
            case 'Monday':
                return "Selasa";
                break;

            case 'Tuesday':
                return "Selasa";
                break;

            case 'Wednesday':
                return "Rabu";
                break;
            
            case 'Thursday':
                return "Kamis";
                break;

            case 'Friday':
                return "Jumat";
                break;

            case 'Saturday':
                return "Sabtu";
                break;

            default:
                return $l;
                break;
        }
    }
    
}
