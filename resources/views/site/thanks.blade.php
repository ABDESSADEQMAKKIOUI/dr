{{--
    PUBLIC CONFIRMATION PAGE — apex host, shown after a demo request is stored.

    STANDALONE, like resources/views/site/landing.blade.php: no ERP layout, no
    public/css/app.css. The apex resolves no tenant, and there is no npm build
    step, so every style this page needs is inline below.

    Optional data: $lead (App\Models\Platform\Lead) — used only to echo back a
    reference number and the address we will reply to.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#0b1622">
    <title>{{ __('app.site_thanks_title') }} — SAFM</title>
    <style>
        :root{
            --ink:#0b1622; --teal:#0d9488; --teal-dark:#0f766e; --teal-light:#14b8a6;
            --text:#0f172a; --body:#475569; --muted:#64748b; --line:#e2e8f0;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{-webkit-text-size-adjust:100%}
        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,"Noto Sans Arabic",sans-serif;
            min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:22px;padding:40px 20px;color:var(--body);line-height:1.65;
            background:var(--ink);position:relative;overflow-x:hidden;
            -webkit-font-smoothing:antialiased;
        }
        body::before{content:"";position:absolute;inset:0;pointer-events:none;
            background:
                radial-gradient(760px 420px at 50% -8%,rgba(13,148,136,.36),transparent 62%),
                radial-gradient(560px 340px at 92% 100%,rgba(56,189,248,.14),transparent 62%)}
        a{color:var(--teal-dark);text-decoration:none}
        a:hover{text-decoration:underline}
        :focus-visible{outline:3px solid var(--teal-light);outline-offset:3px;border-radius:8px}

        .brand{position:relative;display:flex;align-items:center;gap:10px;color:#fff;
            font-size:19px;font-weight:800;letter-spacing:.5px;text-decoration:none}
        .brand:hover{text-decoration:none}
        .brand-mark{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;
            background:linear-gradient(135deg,var(--teal-light),var(--teal-dark));
            color:#04211e;font-size:14px;font-weight:900;letter-spacing:0}
        .brand small{display:block;font-size:10.5px;font-weight:600;letter-spacing:1.3px;
            text-transform:uppercase;color:#5eead4;line-height:1.2}

        .card{position:relative;background:#fff;width:100%;max-width:560px;border-radius:18px;
            padding:44px 40px;text-align:center;box-shadow:0 34px 80px -28px rgba(0,0,0,.7)}
        .badge{width:76px;height:76px;border-radius:50%;display:grid;place-items:center;margin:0 auto 24px;
            background:#f0fdfa;border:1px solid #ccfbf1;color:var(--teal-dark)}
        .badge svg{width:38px;height:38px}
        h1{color:var(--text);font-size:clamp(1.4rem,3.6vw,1.75rem);font-weight:800;
            letter-spacing:-.02em;line-height:1.25;margin-bottom:14px}
        .lead{font-size:16px;color:var(--body)}
        .next{margin-top:14px;font-size:15px;color:var(--muted)}

        .meta{margin-top:26px;padding:16px 18px;border:1px solid var(--line);border-radius:12px;
            background:#f8fafc;display:grid;gap:8px;text-align:left}
        .meta div{display:flex;flex-wrap:wrap;gap:8px;justify-content:space-between;
            font-size:13.5px;color:var(--muted)}
        .meta b{color:var(--text);font-weight:700;word-break:break-word}
        .meta code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
            color:var(--teal-dark);font-weight:700;font-size:13.5px}

        .actions{margin-top:30px;display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;
            padding:13px 24px;border-radius:11px;font-size:15px;font-weight:700;
            border:1px solid transparent;text-decoration:none;transition:background .15s,border-color .15s}
        .btn:hover{text-decoration:none}
        .btn svg{width:17px;height:17px;flex:0 0 auto}
        .btn-primary{background:var(--teal);color:#fff}
        .btn-primary:hover{background:var(--teal-dark);color:#fff}
        .btn-outline{background:#fff;color:var(--teal-dark);border-color:var(--line)}
        .btn-outline:hover{border-color:var(--teal);background:#f0fdfa}

        .foot{position:relative;font-size:13px;color:#64798d;text-align:center}

        @media (max-width:520px){
            .card{padding:34px 22px}
            .actions .btn{width:100%}
        }
        @media (prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
    </style>
</head>
<body>
    <a class="brand" href="{{ route('site.home') }}">
        <span class="brand-mark" aria-hidden="true">S</span>
        <span>SAFM<small>{{ __('app.site_brand_tagline') }}</small></span>
    </a>

    <main class="card">
        <div class="badge" aria-hidden="true">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1>{{ __('app.site_thanks_heading') }}</h1>
        <p class="lead">{{ session('success') ?: __('app.site_thanks_body') }}</p>
        <p class="next">{{ __('app.site_thanks_next') }}</p>

        @isset($lead)
            <div class="meta">
                <div>
                    <span>{{ __('app.site_thanks_reference') }}</span>
                    <code>SAFM-{{ str_pad((string) $lead->id, 5, '0', STR_PAD_LEFT) }}</code>
                </div>
                @if ($lead->email)
                    <div>
                        <span>{{ __('app.site_thanks_reply_to') }}</span>
                        <b>{{ $lead->email }}</b>
                    </div>
                @endif
            </div>
        @endisset

        <div class="actions">
            <a class="btn btn-primary" href="{{ route('site.home') }}">
                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5 5-5M18 12H6"/>
                </svg>
                {{ __('app.site_thanks_back') }}
            </a>
            <a class="btn btn-outline" href="{{ route('site.home') }}#modules">{{ __('app.site_thanks_explore') }}</a>
        </div>
    </main>

    <p class="foot">&copy; {{ date('Y') }} SAFM &middot; {{ __('app.site_hero_langs') }}</p>
</body>
</html>
