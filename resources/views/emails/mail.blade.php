<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=10">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email Verifikasi E-SIGAP</title>
</head>
<body>
    <p>
        Yth. {{ $user->name }} <br><br>
        Terima kasih telah bergabung bersama E-SIGAP <br><br>   
        Silakan verifikasi akun anda untuk dapat menggunakan fitur pada E-SIGAP secara lengkap dengan cara klik link verifikasi di bawah ini:
    </p>   

    <a target="_blank" href="https://esigap.000webhostapp.com/email/verify?email={{$user->email}}&un={{$user->username}}&nh={{$user->nohp}}&tl={{$user->tgl_lahir}}">https://esigap.000webhostapp.com/email/verify?email={{$user->email}}&un={{$user->username}}&nh={{$user->nohp}}&tl={{$user->tgl_lahir}}</a>
    
    <p>
        Demikian kami sampaikan. <br><br>
        Terima kasih <br><br>
        E-SIGAP
    </p>
</body>
</html>