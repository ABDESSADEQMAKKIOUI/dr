{{--
    STANDALONE error page. Do NOT @extends('layouts.app') or any ERP layout:
    a 500 already means something failed; if the tenant connection points at the
    sentinel schema, the layouts' @can() and App\Models\Setting lookups would fail
    again on render and produce an infinite 500 loop. Keep this page dependency-free.
--}}
@php $fr = app()->getLocale() !== 'en'; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fr ? 'Erreur serveur' : 'Server error' }} — SAFM</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
            background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#0f172a}
        .card{background:#fff;width:100%;max-width:460px;border-radius:16px;padding:48px 40px;
            box-shadow:0 20px 45px rgba(15,23,42,.35);text-align:center}
        .brand{font-size:20px;font-weight:800;letter-spacing:.5px;color:#4f46e5;margin-bottom:24px}
        .code{font-size:64px;font-weight:800;line-height:1;color:#e2e8f0;margin-bottom:8px}
        h1{font-size:22px;font-weight:700;color:#0f172a;margin-bottom:12px}
        p{font-size:15px;line-height:1.6;color:#475569}
        .btn{display:inline-block;margin-top:28px;padding:12px 24px;border-radius:10px;
            background:#4f46e5;color:#fff;text-decoration:none;font-size:14px;font-weight:600}
        .btn:hover{background:#4338ca}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">SAFM</div>
        <div class="code">500</div>
        <h1>{{ $fr ? 'Une erreur est survenue' : 'Something went wrong' }}</h1>
        <p>{{ $fr ? 'Une erreur inattendue est survenue de notre côté. Notre équipe a été informée. Veuillez réessayer dans un instant.' : 'An unexpected error occurred on our side. Our team has been notified. Please try again shortly.' }}</p>
        <a class="btn" href="/">{{ $fr ? "Retour à l'accueil" : 'Back to home' }}</a>
    </div>
</body>
</html>
