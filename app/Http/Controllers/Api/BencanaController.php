<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bencana;
use App\Models\StatusBencana;
use App\Models\DetStatusBencana;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\Dusun;
use App\Models\Desa;
use App\Models\Kecamatan;
use DB;
use Auth;

class BencanaController extends Controller
{
    public function bencana(Request $request){
        // semua berita
        if ($request->take > 10) {
            if($request->jenis == "semua"){
                $bencanas = Bencana::select('id_bencana','name','jabatan','gambar','jenis','judul','kecamatan','desa','dusun','lokasi','koordinat','tgl_kejadian','keterangan','status')
                    ->join('users','users.id','tb_bencana.id_user')
                    ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
                    ->join('tb_kecamatan','tb_kecamatan.id_kecamatan','tb_bencana.id_kecamatan')
                    ->join('tb_desa','tb_desa.id_desa','tb_bencana.id_desa')
                    ->join('tb_dusun','tb_dusun.id_dusun','tb_bencana.id_dusun')
                    ->orderBy('id_bencana', 'DESC')
                    ->skip($request->skip)
                    ->take($request->take)
                    ->get();
            }else {
                $bencanas = Bencana::select('id_bencana','name','jabatan','gambar','jenis','judul','kecamatan','desa','dusun','lokasi','koordinat','tgl_kejadian','keterangan','status')
                    ->join('users','users.id','tb_bencana.id_user')
                    ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
                    ->join('tb_kecamatan','tb_kecamatan.id_kecamatan','tb_bencana.id_kecamatan')
                    ->join('tb_desa','tb_desa.id_desa','tb_bencana.id_desa')
                    ->join('tb_dusun','tb_dusun.id_dusun','tb_bencana.id_dusun')
                    ->where('jenis', $request->jenis)
                    ->orderBy('id_bencana', 'DESC')
                    ->skip($request->skip)
                    ->take($request->take)
                    ->get();
            }
        } else {
            if($request->jenis == "semua"){
                $bencanas = Bencana::select('id_bencana','name','jabatan','gambar','jenis','judul','kecamatan','desa','dusun','lokasi','koordinat','tgl_kejadian','keterangan','status')
                    ->join('users','users.id','tb_bencana.id_user')
                    ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
                    ->join('tb_kecamatan','tb_kecamatan.id_kecamatan','tb_bencana.id_kecamatan')
                    ->join('tb_desa','tb_desa.id_desa','tb_bencana.id_desa')
                    ->join('tb_dusun','tb_dusun.id_dusun','tb_bencana.id_dusun')
                    ->orderBy('id_bencana', 'DESC')
                    ->take($request->take)
                    ->get();
            }else {
                $bencanas = Bencana::select('id_bencana','name','jabatan','gambar','jenis','judul','kecamatan','desa','dusun','lokasi','koordinat','tgl_kejadian','keterangan','status')
                    ->join('users','users.id','tb_bencana.id_user')
                    ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
                    ->join('tb_kecamatan','tb_kecamatan.id_kecamatan','tb_bencana.id_kecamatan')
                    ->join('tb_desa','tb_desa.id_desa','tb_bencana.id_desa')
                    ->join('tb_dusun','tb_dusun.id_dusun','tb_bencana.id_dusun')
                    ->where('jenis', $request->jenis)
                    ->orderBy('id_bencana', 'DESC')
                    ->take($request->take)
                    ->get();
            }
        }

        if ($bencanas != "") {
            foreach ($bencanas as $key => $bencana) {
                // tgl 
                $hari = self::convertHari(date('l', strtotime($bencanas[$key]['tgl_kejadian'])));
                $waktu = date('H:i', strtotime($bencanas[$key]['tgl_kejadian']));
                $tgl = date('d', strtotime($bencanas[$key]['tgl_kejadian']));
                $bulan = self::convertBulan(date('m', strtotime($bencanas[$key]['tgl_kejadian'])));
                $tahun = date('Y', strtotime($bencanas[$key]['tgl_kejadian']));
    
                $str_tgl = $hari . ", " . $tgl . " " . $bulan . " " . $tahun . " " . $waktu . " WITA";
                
                // lokasi
                $lokasi = $bencanas[$key]['lokasi'] . ", " . $bencanas[$key]['desa'] . ", " . $bencanas[$key]['dusun'] . ", " . $bencanas[$key]['kecamatan'];
    
                $bencanas[$key]['tgl_kejadian'] = $str_tgl;
                $bencanas[$key]['lokasi_kejadian'] = $lokasi;
            }

            $success = "true";
        } else {
            $success = "false";
        }

        return response()->json([
            'success' => $success, 
            'msg' => $bencanas], 200);
    }

    public function bencanaTerbaru(){
        // headline
        $bencana = Bencana::select('id_bencana','name','jabatan','gambar','jenis','judul','kecamatan','desa','dusun','lokasi','koordinat','tgl_kejadian','keterangan','status')
            ->join('users','users.id','tb_bencana.id_user')
            ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
            ->join('tb_kecamatan','tb_kecamatan.id_kecamatan','tb_bencana.id_kecamatan')
            ->join('tb_desa','tb_desa.id_desa','tb_bencana.id_desa')
            ->join('tb_dusun','tb_dusun.id_dusun','tb_bencana.id_dusun')
            ->orderBy('id_bencana', 'DESC')
            ->first();

        if ($bencana != "") {
            // tgl 
            $hari = self::convertHari(date('l', strtotime($bencana['tgl_kejadian'])));
            $waktu = date('H:i', strtotime($bencana['tgl_kejadian']));
            $tgl = date('d', strtotime($bencana['tgl_kejadian']));
            $bulan = self::convertBulan(date('m', strtotime($bencana['tgl_kejadian'])));
            $tahun = date('Y', strtotime($bencana['tgl_kejadian']));

            $str_tgl = $hari . ", " . $tgl . " " . $bulan . " " . $tahun . " " . $waktu . " WITA";
            
            // lokasi
            $lokasi = $bencana['lokasi'] . ", " . $bencana['desa'] . ", " . $bencana['dusun'] . ", " . $bencana['kecamatan'];

            $bencana['tgl_kejadian'] = $str_tgl;
            $bencana['lokasi_kejadian'] = $lokasi;

            $success = "true";
        } else {
            $success = "false";
        }

        return response()->json([
            'success' => $success, 
            'msg' => $bencana], 200);
    }

    public function bencanaDetail($id){
        // headline
        $bencana = Bencana::select('id_bencana','name','jabatan','gambar','jenis','judul','kecamatan','desa','dusun','lokasi','koordinat','tgl_kejadian','keterangan','status')
            ->join('users','users.id','tb_bencana.id_user')
            ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
            ->join('tb_kecamatan','tb_kecamatan.id_kecamatan','tb_bencana.id_kecamatan')
            ->join('tb_desa','tb_desa.id_desa','tb_bencana.id_desa')
            ->join('tb_dusun','tb_dusun.id_dusun','tb_bencana.id_dusun')
            ->where('id_bencana', $id)
            ->first();

        $hari = self::convertHari(date('l', strtotime($bencana['tgl_kejadian'])));
        $waktu = date('H:i', strtotime($bencana['tgl_kejadian']));
        $tgl = date('d', strtotime($bencana['tgl_kejadian']));
        $bulan = self::convertBulan(date('m', strtotime($bencana['tgl_kejadian'])));
        $tahun = date('Y', strtotime($bencana['tgl_kejadian']));

        $str_tgl = $hari . ", " . $tgl . " " . $bulan . " " . $tahun . " " . $waktu . " WITA";
        
        // lokasi
        $lokasi = $bencana['lokasi'] . ", " . $bencana['desa'] . ", " . $bencana['dusun'] . ", " . $bencana['kecamatan'];

        $bencana['tgl_kejadian'] = $str_tgl;
        $bencana['lokasi_kejadian'] = $lokasi;

        return response()->json([
            'success' => 'true', 
            'msg' => $bencana], 200);
    }

    public function fotoBencana(Request $request){
        //
    }

    // hapus bencana tidak valid oleh admin
    public function hapusBencana(Request $request){
        Notifikasi::where('id_bencana', $request->id_bencana)->delete();
        $statusBencana = StatusBencana::where('id_bencana', $request->id_bencana);
        DetStatusBencana::where('id_status_bencana', $statusBencana->first()->id_status_bencana)->delete();
        StatusBencana::where('id_bencana', $request->id_bencana)->delete();
        Bencana::where('id_bencana', $request->id_bencana)->delete();

        return response()->json([
            'success' => "true", 
            'msg' => 'Berhasil menghapus data bencana!'], 200);
    }

    public function laporBencana(Request $request){
        // upload foto
        $msg = "Laporan gagal terkirim! Mohon hubungi admin.";

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $jenis = $request->jenis;
            $filename = Self::uploadFile($file, $jenis);

            Bencana::insert([
                'id_user' => Auth::user()->id,
                'gambar' => $filename,
                'jenis' => $jenis,
                'judul' => $request->judul,
                'id_kecamatan' => "1",
                'id_desa' => $request->id_desa,
                'id_dusun' => $request->id_dusun,
                'lokasi' => $request->lokasi,
                'koordinat' => $request->koordinat,
                'tgl_kejadian' => date('Y-m-d', strtotime($request->tgl)) . ' ' . date('H:i', strtotime($request->waktu)),
                'keterangan' => $request->keterangan,
            ]);
            $id_bencana = DB::getPdo()->lastInsertId();

            $msg = "Laporan berhasil terkirim!";
            
            //send notif
            $fcm = User::select('fcm')->where('isActive', 'Aktif')->where('fcm', '!=', '')->get();
            $dusun = Dusun::where('id_dusun', $request->id_dusun)->first()->dusun;
            $desa = Desa::where('id_desa', $request->id_desa)->first()->desa;
            $kecamatan = Kecamatan::where('id_kecamatan', "1")->first()->kecamatan;
            $notif = $request->judul . " di " . $request->lokasi . ", " . $dusun . ", " . $desa . ", " . $kecamatan;

            $usr = array();
            foreach ($fcm as $key => $data) {
                $usr[$key] = $data->fcm;
            }
            
            self::sendNotification($usr, $jenis, $notif, $id_bencana);
        } 

        return response()->json([
            'success' => "true", 
            'msg' => $msg], 200);
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
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function sendNotification($device_token, $jenis, $message, $id_bencana)
    {
        $SERVER_API_KEY = 'AAAAeCVndNw:APA91bH76jTkSMago_KF5HJz0e5tVJgyw0xXXdxANjAZaBSi4CElhZImeaEA7Qx6xYz9WLvMlM7UfSsrcr0xYqV6wU1sBVU-VDsxHr-1n98wCdPmk-ZIB-lG2nCMKXFZPmBM-YG6DMeU';
  
        // payload data, it will vary according to requirement
        $data = array (
            'registration_ids' => $device_token,
            'data' => array (
                "title" => $jenis,
                "body" => $message,
                "id_bencana" => (string)$id_bencana
            ),
            'priority' => "high",
        );
        $dataString = json_encode($data);
    
        $headers = [
            'Authorization:key=' . $SERVER_API_KEY,
            'Content-Type:application/json',
        ];
    
        $ch = curl_init();
      
        $url = 'https://fcm.googleapis.com/fcm/send';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
               
        $result = curl_exec($ch);
      
        curl_close($ch);
      
        return $result;
    }

    public function uploadFile($file, $jenis){
        $result = [];
 
        // $filename = time() . $file->getClientOriginalName();
        $filename = time() . "_" . $jenis . ".". $file->getClientOriginalExtension();
 
        // isi dengan nama folder tempat kemana file diupload
		$tujuan_upload = public_path() . '/image/bencana';
 
        // upload file
		$file->move($tujuan_upload, $filename);

        return $filename;
	}
}
