<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class EmailController extends Controller
{
    public function verify(Request $request){
        $user = User::where('email', $request->email)
            ->where('username', $request->un)
            ->where('nohp', $request->nh)
            ->where('tgl_lahir', date('Y-m-d', strtotime(str_replace('/', '-', $request->tl))));

        if ($user->first()->email_verified_at == "") {
            $success = $user->update([
                "email_verified_at" => date("Y-m-d H:i:s"),
            ]);

            if ($success == 1) {
                return "Berhasil! Akun anda telah diverifikasi.";
            } else {
                return "Gagal! Data akun tidak ditemukan.";
            }
        } else {
            return abort(404);
        }
    }
}
