<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StatusBencana;
use App\Models\DetStatusBencana;
use App\Models\LevelJabatan;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Bencana;
use Auth;

class StatusBencanaController extends Controller
{
    public function getStatusBencana(Request $request){
        $statusBencanas = StatusBencana::select('a.jabatan AS jabatan','status','b.jabatan as kepada','perintah')
            ->where('id_bencana', $request->id_bencana)
            ->join('tb_jabatan as a','a.id_jabatan','tb_status_bencana.id_jabatan')
            ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','a.id_jabatan')
            ->leftJoin('tb_det_status_bencana', 'tb_det_status_bencana.id_status_bencana', 'tb_status_bencana.id_status_bencana')
            ->leftJoin('tb_jabatan as b','b.id_jabatan','tb_det_status_bencana.id_jabatan')
            ->orderBy('level','ASC')
            ->get();

        return response()->json([
            'success' => "true", 
            'msg' => $statusBencanas], 200);
    }

    public function updateStatusBencana(Request $request){
        $level = LevelJabatan::where('id_jabatan', Auth::user()->id_jabatan)->first()->level;
        $statusBencana = StatusBencana::where('id_bencana', $request->id_bencana)
            ->where('id_jabatan', Auth::user()->id_jabatan);

        if ($statusBencana->get()->count() > 0) {
            if ($level == "5") {
                $sukses = StatusBencana::where('id_bencana', $request->id_bencana)->update([
                    'status' => 'Selesai',
                ]);
                $id_jabatan = "";
            } else {
                $sukses = $statusBencana->update([
                    'status' => 'Dikonfirmasi',
                ]);
                $id_jabatan = $request->id_jabatan;
            }
    
            if ($sukses === 1) {
                $isExist = DetStatusBencana::where('id_status_bencana', $statusBencana->first()->id_status_bencana)
                    ->where('id_jabatan', $request->id_jabatan)
                    ->get()
                    ->count();
                    
                if ($isExist == 0) {
                    DetStatusBencana::insert([
                        'id_status_bencana' => $statusBencana->first()->id_status_bencana,
                        'id_jabatan' => $id_jabatan,
                        'perintah' => $request->perintah,
                    ]);
                            
                    if ($level != "5") {
                        //send notif
                        $fcm = User::select('fcm')
                            ->where('isActive', 'Aktif')
                            ->where('fcm', '!=', '')
                            ->where('id_jabatan', $id_jabatan)
                            ->get();
                            
                        $jabatanPengirim = Jabatan::where('id_jabatan', Auth::user()->id_jabatan)->first()->jabatan;
                        $notif = "Mohon segera melakukan konfirmasi bencana yang telah diperintahkan oleh " . $jabatanPengirim . " kepada anda. Ketuk untuk melanjutkan.";
                        
                    } else {
                        //send notif
                        $fcm = StatusBencana::select('fcm')
                            ->where('isActive', 'Aktif')
                            ->where('fcm', '!=', '')
                            ->where('id_bencana', $statusBencana->first()->id_status_bencana)
                            ->get();
                            
                        $notif = "Bencana telah ditangani!";
                    }
                    
                    $usr = array();
                    foreach ($fcm as $key => $data) {
                        $usr[$key] = $data->fcm;
                    }
                    
                    self::sendNotification($usr, "Konfirmasi Bencana", $notif, $statusBencana->first()->id_bencana);
                    
                    return response()->json([
                        'success' => "true", 
                        'msg' => 'Berhasil memperbarui status bencana!'], 200);
                } else {
                    return response()->json([
                        'success' => "false", 
                        'msg' => 'Gagal! Pejabat telah menerima perintah sebelumnya.'], 200);
                }
            } else {
                return response()->json([
                    'success' => "false", 
                    'msg' => 'Gagal memperbarui status bencana! Jabatan anda tidak ditemukan.'], 500);
            }
        } else {
            return response()->json([
                'success' => "false", 
                'msg' => 'Mohon menunggu konfirmasi dari pejabat sebelumnya sesuai dengan SOP Tanggap Darurat Bencana.'], 200);
        }
    }

    public function updateStatusBencanaDitangani(Request $request){
        StatusBencana::where('id_status_bencana', $request->id_status_bencana)->update([
            'status' => 'Telah ditangani',
        ]);

        self::sendNotification('all');

        return response()->json([
            'success' => "true", 
            'msg' => 'Berhasil memperbarui status bencana telah ditangani!'], 200);
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
}
