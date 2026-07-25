{{--
    STANDALONE page. Do NOT @extends any ERP layout: ResolveTenant renders this
    BEFORE any tenant connection is selected, and the ERP layouts run @can() and
    App\Models\Setting lookups on render, which would turn this 503 into a 500 loop.
    The response carries Retry-After: 30; a client-side reload assists the user.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="refresh" content="30">
    <title>{{ __('tenancy.provisioning_title') }} — SAFM</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
            background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#0f172a}
        .card{background:#fff;width:100%;max-width:480px;border-radius:16px;padding:48px 40px;
            box-shadow:0 20px 45px rgba(15,23,42,.35);text-align:center}
        .brand{font-size:20px;font-weight:800;letter-spacing:.5px;color:#4f46e5;margin-bottom:28px}
        .spinner{width:56px;height:56px;margin:0 auto 24px;border-radius:50%;
            border:5px solid #e0e7ff;border-top-color:#4f46e5;animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .code{font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#94a3b8;margin-bottom:8px}
        h1{font-size:24px;font-weight:700;color:#0f172a;margin-bottom:14px}
        p{font-size:15px;line-height:1.6;color:#475569}
        .btn{display:inline-block;margin-top:28px;padding:12px 24px;border-radius:10px;
            background:#4f46e5;color:#fff;text-decoration:none;font-size:14px;font-weight:600}
        .btn:hover{background:#4338ca}
        .foot{margin-top:32px;font-size:13px;color:#94a3b8}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">SAFM</div>
        <div class="spinner"></div>
        <div class="code">503</div>
        <h1>{{ __('tenancy.provisioning_title') }}</h1>
        <p>{{ __('tenancy.provisioning_body') }}</p>
        <a class="btn" href="">{{ __('tenancy.retry') }}</a>
        <div class="foot">SAFM</div>
    </div>
</body>
</html>
