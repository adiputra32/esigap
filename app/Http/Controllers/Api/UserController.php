<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\LevelJabatan;
use App\Models\Notifikasi;
use Auth;
use Carbon\Carbon;
use File;
use Response;
use App\Mail\EmailVerification;

class UserController extends Controller
{
    public function register(Request $request){
        $isEmailExists = User::where('email', $request->email)->get()->count();
        $isUnameExists = User::where('username', $request->username)->get()->count();

        if ($isEmailExists > 0) {
            return response()->json([
                'success' => "false", 
                'msg' => 'Email telah terdaftar! Mohoh gunakan email yang berbeda.'], 200);
        } elseif($isUnameExists > 0) {
            return response()->json([
                'success' => "false", 
                'msg' => 'Username telah digunakan! Mohon gunakan username yang berbeda.'], 200);
        } else {
            $tgl = date('Y-m-d', strtotime(str_replace('/', '-', $request->tgl_lahir)));

            User::insert([
                'name' => $request->name,
                'email' => $request->email,
                'nohp' => $request->nohp,
                'username' => $request->username,
                'id_jabatan' => $request->id_jabatan,
                'tgl_lahir' => $tgl,
                'alamat' => $request->alamat,
                'fcm' => $request->fcm,
                'password' => bcrypt($request->password),
            ]);

            Auth::attempt(['username' => $request->username, 'password' => $request->password]);

            $user = [];
            $user = Auth::user();
            $user['token'] = $user->createToken('nApp')->accessToken;
            $user['tgl_lahir'] = date('d/m/Y', strtotime($user['tgl_lahir']));
            $user['jabatan'] = Jabatan::where('id_jabatan', $user['id_jabatan'])->first()->jabatan;
            $user['level'] = LevelJabatan::where('id_jabatan', $user['id_jabatan'])->first()->level;

            if ($user['email_verified_at'] == "") {
                $user['status'] = "Belum Terverifikasi";
            }
            $success =  $user;

            self::EmailVerifikasi($user);

            return response()->json([
                'success' => "true", 
                'msg' => $success], 200);

            // return response()->json([
            //     'success' => "true", 
            //     'msg' => 'Masukkan kode OTP yang dikirimkan melalui Email'], 200);
        }
    }

    public function validasiEmail(Request $request){
        $current_date_time = Carbon::now()->toDateTimeString(); // Produces something like "2019-03-11 12:25:00"

        User::where('email', $request->email)->update([
            'email_verified_at' => $current_date_time,
            'success' => "Terverifikasi",
        ]);

        return view('validasiEmail.index');
    }

    public function login(Request $request){
        if(Auth::attempt(['username' => $request->username, 'password' => $request->password, 'isActive' => 'Aktif'])){
            User::where('id', Auth::user()->id)->update([
                'fcm' => $request->fcm,
            ]);

            $user = [];
            $user = Auth::user();
            $user['token'] = $user->createToken('nApp')->accessToken;
            $user['tgl_lahir'] = date('d/m/Y', strtotime($user['tgl_lahir']));
            $user['jabatan'] = Jabatan::where('id_jabatan', $user['id_jabatan'])->first()->jabatan;
            $user['level'] = LevelJabatan::where('id_jabatan', $user['id_jabatan'])->first()->level;

            if ($user['email_verified_at'] == "") {
                $user['status'] = "Belum Terverifikasi";
            } else {
                $user['status'] = "Terverifikasi";
            }

            $success =  $user;

            return response()->json([
                'success' => "true", 
                'msg' => $success], 200);
        }
        else{
            return response()->json([
                'success' => "false", 
                'msg' => 'Email dan Password tidak cocok!'], 401);
        }
    }

    public function update(Request $request){

        // return response()->json([
        //     "success" => 'false',
        //     'msg' => $request->id
        // ], 500);
        $tgl = date('Y-m-d', strtotime($request['tgl_lahir']));
        $jabatan = Jabatan::where('jabatan', $request['jabatan'])->first();

        // upload fotoh
        // $user = User::where('id', Auth::user()->id)->first();
        $filename = Auth::user()->foto;
        $id = Auth::user()->id;
        $name = Auth::user()->name;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $result = [];
            if ($filename == "") {
                $result = Self::uploadFile($file, $id, $name);
            } else {
                $result = Self::uploadFileUpdate($file, $id, $name, $filename);
            }

            if ($result["status"] == "true") {
                $filename = $result["message"];
            } else {
                return response()->json([
                    'success' => "false",
                    'msg' => $result['message']], 200);
            }

            User::where('id', Auth::user()->id)->update([
                'foto' => $filename,
                'name' => $request['name'],
                'nohp' => $request['nohp'],
                'id_jabatan' => $request['id_jabatan'],
                'alamat' => $request['alamat'],
                'tgl_lahir' => $tgl,
            ]);
        } else {
            User::where('id', Auth::user()->id)->update([
                'name' => $request['name'],
                'nohp' => $request['nohp'],
                'id_jabatan' => $request['id_jabatan'],
                'alamat' => $request['alamat'],
                'tgl_lahir' => $tgl,
            ]);
        }

        $user = User::where('id', $request['id'])->first();
        $user['token'] = $user->createToken('nApp')->accessToken;
        $user['tgl_lahir'] = date('d/m/Y', strtotime($user['tgl_lahir']));
        $user['jabatan'] = Jabatan::where('id_jabatan', $user['id_jabatan'])->first()->jabatan;
        $user['level'] = LevelJabatan::where('id_jabatan', $user['id_jabatan'])->first()->level;
        if ($user['email_verified_at'] == "") {
            $user['status'] = "Belum Terverifikasi";
        } else {
            $user['status'] = "Terverifikasi";
        }

        return response()->json([
            'success' => "true",
            'msg' => $user], 200);
    }

    public function resetPassword(Request $request){
        User::where('id', Auth::user()->id)->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'success' => "true",
            'msg' => 'Reset password berhasil!'], 200);
    }

    public function logout(){
        if (Auth::user()) {
            User::where('id', Auth::user()->id)->update([
                'fcm' => "",
            ]);

            $user = Auth::user()->token();
            $user->revoke();

            return response()->json([
                'success' => "true",
                'msg' => 'Logout berhasil!'], 200);
        } else {
            return response()->json([
                'success' => false,
                'msg' => 'Terjadi kesalahan!'], 200);
        }
    }

    public function checkValidasiEmail(Request $request){
        $valid = User::where('id', $request->id)->first();

        if ($valid->email_verified_at != "") {
            $isValid = "Terverifikasi";

            return response()->json([
                'success' => "true",
                'msg' => $isValid], 200);
        } else {
            $isValid = "Belum Terverifikasi";

            return response()->json([
                'success' => false,
                'msg' => $isValid], 200);
        }
    }

    public function getJabatan(){
        $data = Jabatan::where('jabatan', '!=', 'ADMIN')
            ->orderBy('id_jabatan', 'ASC')
            ->get();

        return response()->json([
            'success' => "true", 
            'msg' => $data], 200);
    }

    public function getJabatanAll(){
        $data = Jabatan::orderBy('id_jabatan', 'ASC')
            ->get();

        return response()->json([
            'success' => "true", 
            'msg' => $data], 200);
    }

    public function getJabatanKonfirmasi2(){
        $level = LevelJabatan::where('id_jabatan', Auth::user()->id_jabatan)->first()->level;
        $level = $level + 1;
        $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
            ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
            ->where('level', $level)
            ->orderBy('tb_jabatan.id_jabatan', 'ASC')
            ->get();

        return response()->json([
            'success' => "true", 
            'msg' => $data], 200);
    }
    
    public function getJabatanKonfirmasi(){
        $jbtn = LevelJabatan::where('id_jabatan', Auth::user()->id_jabatan)->first();
        $level = $jbtn->level + 1;
        $data = [];

        switch (Auth::user()->id_jabatan) {
            case '11':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['23','28'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '12':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['29','32'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '13':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['31','40'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '14':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['41','47'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '15':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['48','51'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '16':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['52','55'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '17':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['56','59'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '18':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['60','63'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '19':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['64','69'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '20':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['70','75'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '21':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['76','80'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            case '22':
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->whereBetween('tb_jabatan.id_jabatan',['81','86'])
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
            default:
                $data = Jabatan::select('tb_jabatan.id_jabatan as id_jabatan', 'jabatan', 'level')
                    ->join('tb_level_jabatan','tb_level_jabatan.id_jabatan','tb_jabatan.id_jabatan')
                    ->where('level', $level)
                    ->orderBy('tb_jabatan.id_jabatan', 'ASC')
                    ->get();
                break;
        }

        return response()->json([
            'success' => "true", 
            'msg' => $data], 200);
    }
    
    public function getDataUser($id){
        $user = User::where('id', Auth::user()->id)->first();
        $user['tgl_lahir'] = date('d/m/Y', strtotime($user['tgl_lahir']));
        $user['jabatan'] = Jabatan::where('id_jabatan', $user['id_jabatan'])->first()->jabatan;
        $user['level'] = LevelJabatan::where('id_jabatan', $user['id_jabatan'])->first()->level;

        if ($user['email_verified_at'] == "") {
            $user['status'] = "Belum Terverifikasi";
        } else {
            $user['status'] = "Terverifikasi";
        }

        $success =  $user;

        return response()->json([
            'success' => "true", 
            'msg' => $success], 200);
    }

    public function getDataUsers(){
        $isAdmin = User::where('id', Auth::user()->id)
            ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
            ->first()
            ->jabatan;

        if ($isAdmin == "ADMIN") {
            $users = User::where('id', '!=', Auth::user()->id)->where('isActive', 'Aktif')->get();

            //tgl
            foreach ($users as $key => $user) {
                $tgl = date('d', strtotime($user['tgl_lahir']));
                $bulan = self::convertBulan(date('m', strtotime($user['tgl_lahir'])));
                $tahun = date('Y', strtotime($user['tgl_lahir']));

                $str_tgl = $tgl . " " . $bulan . " " . $tahun;

                $user['tgl_lahir'] = $str_tgl;
                $user['jabatan'] = Jabatan::where('id_jabatan', $user['id_jabatan'])->first()->jabatan;

                if ($user['email_verified_at'] == "") {
                    $user['status'] = "Belum Terverifikasi";
                } else {
                    $user['status'] = "Terverifikasi";
                }
            }

            return response()->json([
                'success' => "true", 
                'msg' => $users], 200);
        } else {
            return response()->json([
                'success' => "true", 
                'msg' => "Unauthorized"], 401);
        }
    }

    public function uploadFile($file, $id, $name){
        $result = [];

        // $ukuranFile = $file->getSize();
        // if ($ukuranFile > 5120000) { // 5mb
        //     $result["status"] = "false";
        //     $result["message"] = "Ukuran file lebih dari 5 mb";
        //     return $result;
        // }
 
        // $filename = time() . $file->getClientOriginalName();
        $filename = $id . "_" . time() . "_" . $name . ".". $file->getClientOriginalExtension();
 
        // isi dengan nama folder tempat kemana file diupload
		$tujuan_upload = public_path() . '/image/photoProfile';
 
        // upload file
		$file->move($tujuan_upload, $filename);

        $result["status"] = "true";
        $result["message"] = $filename;

        return $result;
	}

    public function uploadFileUpdate($file, $id, $name, $filePrev){
        $result = [];
 
        $filename = $id . "_" . time() . "_" . $name . ".". $file->getClientOriginalExtension();
 
        // isi dengan nama folder tempat kemana file diupload
		$tujuan_upload = public_path() . '/image/photoProfile';

        // hapus file lama
        File::delete($tujuan_upload . '/' . $filePrev);
 
        // upload file
		$file->move($tujuan_upload, $filename);

        $result["status"] = "true";
        $result["message"] = $filename;

        return $result;
	}

    public function hapusUser(Request $request){
        $isAdmin = User::where('id', Auth::user()->id)
            ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
            ->first()
            ->jabatan;

        if ($isAdmin == "ADMIN") {
            Notifikasi::where('id_user', $request->id_user)->delete();
            // User::where('id', $request->id_user)->update([
            //     'isActive' => 'Tidak Aktif',
            //     'username' => $request->id_user,
            //     'email' => $request->id_user,
            //     'fcm' => "",
            //     'email_verified_at' => "",
            // ]);
            User::where('id', $request->id_user)->delete();

            return response()->json([
                'success' => "true", 
                'msg' => "Berhasil menghapus user!"], 200);
        } else {
            return response()->json([
                'success' => "true", 
                'msg' => "Unauthorized"], 401);
        }
    }

    public function resetJabatan(Request $request){
        $isAdmin = User::where('id', Auth::user()->id)
            ->join('tb_jabatan','tb_jabatan.id_jabatan','users.id_jabatan')
            ->first()
            ->jabatan;

        if ($isAdmin == "ADMIN") {
            User::where('id', $request->id_user)->update([
                'id_jabatan' => $request->id_jabatan,
            ]);

            return response()->json([
                'success' => "true", 
                'msg' => "Berhasil memperbarui jabatan!"], 200);
        } else {
            return response()->json([
                'success' => "true", 
                'msg' => "Unauthorized"], 401);
        }
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

    public function EmailVerifikasi($user){
        \Mail::to($user->email)
                ->send(new \App\Mail\EmailVerification($user));
                
    }

}
