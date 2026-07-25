{{--
    STANDALONE page. Do NOT @extends any ERP layout: ResolveTenant renders this
    BEFORE any tenant connection is selected, and the ERP layouts run @can() and
    App\Models\Setting lookups on render, which would turn this 404 into a 500 loop.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('tenancy.unknown_title') }} — SAFM</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
            background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#0f172a}
        .card{background:#fff;width:100%;max-width:480px;border-radius:16px;padding:48px 40px;
            box-shadow:0 20px 45px rgba(15,23,42,.35);text-align:center}
        .brand{font-size:20px;font-weight:800;letter-spacing:.5px;color:#4f46e5;margin-bottom:28px}
        .icon{width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;
            margin:0 auto 24px;background:#eef2ff;color:#4f46e5}
        .icon svg{width:36px;height:36px}
        .code{font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#94a3b8;margin-bottom:8px}
        h1{font-size:24px;font-weight:700;color:#0f172a;margin-bottom:14px}
        p{font-size:15px;line-height:1.6;color:#475569}
        .foot{margin-top:32px;font-size:13px;color:#94a3b8}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">SAFM</div>
        <div class="icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="code">404</div>
        <h1>{{ __('tenancy.unknown_title') }}</h1>
        <p>{{ __('tenancy.unknown_body') }}</p>
        <div class="foot">SAFM</div>
    </div>
</body>
</html>
