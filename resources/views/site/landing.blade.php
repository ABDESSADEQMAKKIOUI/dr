{{--
    PUBLIC LANDING PAGE — apex host (facturation.cfpss.ma).

    STANDALONE ON PURPOSE. Do NOT @extends('layouts.app') or the platform layout,
    and do NOT link public/css/app.css: this page renders on the apex, where NO
    tenant is resolved (no tenant connection, no App\Models\Setting, no @can()),
    and there is no npm build step, so any Tailwind class not already compiled in
    the committed bundle would silently do nothing. Everything the page needs is
    inline below. Same rule as resources/views/errors/*.blade.php.

    Data contract: $plans — active plans, ordered (App\Models\Platform\Plan).
--}}
@php
    /** @var \Illuminate\Support\Collection $planList */
    $planList = collect($plans ?? []);

    // Highlight the middle offer once there is a real ladder of plans.
    $featuredIndex = $planList->count() >= 3 ? 1 : null;

    $periodKeys = [
        'monthly'   => 'app.site_period_monthly',
        'quarterly' => 'app.site_period_quarterly',
        'yearly'    => 'app.site_period_yearly',
        'lifetime'  => 'app.site_period_lifetime',
    ];

    $formatPrice = static function ($value): string {
        $value = (float) $value;
        $decimals = fmod($value, 1.0) === 0.0 ? 0 : 2;

        return app()->getLocale() === 'en'
            ? number_format($value, $decimals, '.', ',')
            : number_format($value, $decimals, ',', ' ');
    };

    $modules = [
        ['key' => 'invoicing',  'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['key' => 'stock',      'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['key' => 'purchases',  'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        ['key' => 'sales',      'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['key' => 'accounting', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
        ['key' => 'hr',         'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['key' => 'pos',        'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ['key' => 'reports',    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ];

    $benefits = [
        ['key' => 'languages', 'icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
        ['key' => 'isolation', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['key' => 'cloud',     'icon' => 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.002 4.002 0 003 15z'],
        ['key' => 'roles',     'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
        ['key' => 'compliance','icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['key' => 'support',   'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ __('app.site_meta_description') }}">
    <meta name="theme-color" content="#0b1622">
    <title>SAFM — {{ __('app.site_meta_title') }}</title>
    <style>
        :root{
            --ink:#0b1622; --ink-2:#12212f; --ink-3:#1b2c3d;
            --teal:#0d9488; --teal-dark:#0f766e; --teal-light:#14b8a6; --teal-soft:#ccfbf1;
            --text:#0f172a; --body:#475569; --muted:#64748b;
            --line:#e2e8f0; --line-soft:#f1f5f9; --surface:#ffffff; --surface-soft:#f8fafc;
            --danger:#b91c1c; --danger-bg:#fef2f2; --danger-line:#fecaca;
            --ok:#065f46; --ok-bg:#ecfdf5; --ok-line:#a7f3d0;
            --radius:14px; --shadow:0 1px 2px rgba(15,23,42,.05),0 12px 32px -12px rgba(15,23,42,.18);
            --wrap:1140px;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{-webkit-text-size-adjust:100%;scroll-behavior:smooth}
        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,"Noto Sans Arabic",sans-serif;
            background:var(--surface);color:var(--body);font-size:16px;line-height:1.65;
            -webkit-font-smoothing:antialiased;overflow-x:hidden;
        }
        img,svg{max-width:100%}
        a{color:var(--teal-dark);text-decoration:none}
        a:hover{text-decoration:underline}
        h1,h2,h3,h4{color:var(--text);line-height:1.2;font-weight:800;letter-spacing:-.02em}
        ul{list-style:none}

        :focus-visible{outline:3px solid var(--teal-light);outline-offset:3px;border-radius:6px}

        .wrap{width:100%;max-width:var(--wrap);margin:0 auto;padding:0 20px}
        .skip{position:absolute;left:-9999px;top:0;z-index:99;background:#fff;color:var(--text);
            padding:12px 18px;border-radius:0 0 10px 0;font-weight:700}
        .skip:focus{left:0}

        /* ── Header ───────────────────────────────────────────── */
        .site-head{position:sticky;top:0;z-index:50;background:rgba(11,22,34,.92);
            backdrop-filter:saturate(140%) blur(10px);border-bottom:1px solid rgba(255,255,255,.08)}
        .head-in{display:flex;align-items:center;gap:16px;min-height:66px}
        .logo{display:flex;align-items:center;gap:10px;color:#fff;font-weight:800;letter-spacing:.5px;
            font-size:19px;text-decoration:none;flex:0 0 auto}
        .logo:hover{text-decoration:none}
        .logo-mark{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;
            background:linear-gradient(135deg,var(--teal-light),var(--teal-dark));
            color:#04211e;font-size:14px;font-weight:900;letter-spacing:0}
        .logo small{display:block;font-size:10.5px;font-weight:600;letter-spacing:1.3px;
            text-transform:uppercase;color:#5eead4;line-height:1.2}
        .site-nav{display:flex;align-items:center;gap:4px;margin-left:auto}
        .site-nav a{color:#cbd5e1;font-size:14.5px;font-weight:600;padding:8px 12px;border-radius:9px}
        .site-nav a:hover{color:#fff;background:rgba(255,255,255,.08);text-decoration:none}
        .head-cta{margin-left:8px}

        /* ── Buttons ──────────────────────────────────────────── */
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;
            padding:12px 22px;border-radius:11px;font-size:15px;font-weight:700;
            border:1px solid transparent;cursor:pointer;text-decoration:none;
            transition:background .15s ease,border-color .15s ease,transform .12s ease}
        .btn:hover{text-decoration:none;transform:translateY(-1px)}
        .btn svg{width:17px;height:17px;flex:0 0 auto}
        .btn-primary{background:var(--teal);color:#fff}
        .btn-primary:hover{background:var(--teal-dark);color:#fff}
        .btn-ghost{background:rgba(255,255,255,.07);color:#e2e8f0;border-color:rgba(255,255,255,.2)}
        .btn-ghost:hover{background:rgba(255,255,255,.14);color:#fff}
        .btn-outline{background:#fff;color:var(--teal-dark);border-color:var(--line)}
        .btn-outline:hover{border-color:var(--teal);background:#f0fdfa}
        .btn-sm{padding:9px 16px;font-size:14px;border-radius:9px}
        .btn-block{width:100%}
        .btn-lg{padding:15px 28px;font-size:16px}

        /* ── Hero ─────────────────────────────────────────────── */
        .hero{position:relative;background:var(--ink);color:#cbd5e1;overflow:hidden;
            padding:74px 0 88px}
        .hero::before{content:"";position:absolute;inset:0;
            background:
                radial-gradient(760px 400px at 12% -10%,rgba(13,148,136,.34),transparent 62%),
                radial-gradient(620px 380px at 92% 8%,rgba(56,189,248,.16),transparent 60%);
            pointer-events:none}
        .hero::after{content:"";position:absolute;inset:0;opacity:.35;pointer-events:none;
            background-image:linear-gradient(rgba(255,255,255,.045) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(255,255,255,.045) 1px,transparent 1px);
            background-size:56px 56px;
            -webkit-mask-image:radial-gradient(circle at 50% 0,#000,transparent 72%);
            mask-image:radial-gradient(circle at 50% 0,#000,transparent 72%)}
        .hero-grid{position:relative;z-index:1;display:grid;gap:52px;
            grid-template-columns:minmax(0,1.05fr) minmax(0,.95fr);align-items:center}
        .pill{display:inline-flex;align-items:center;gap:9px;padding:6px 14px 6px 8px;border-radius:999px;
            background:rgba(13,148,136,.18);border:1px solid rgba(45,212,191,.35);
            color:#99f6e4;font-size:13px;font-weight:700;letter-spacing:.2px}
        .pill b{background:var(--teal);color:#fff;border-radius:999px;padding:2px 9px;font-size:11px;
            font-weight:800;letter-spacing:.6px;text-transform:uppercase}
        .hero h1{color:#fff;font-size:clamp(2rem,4.6vw,3.35rem);margin:22px 0 18px;letter-spacing:-.03em}
        .hero h1 em{font-style:normal;color:#5eead4}
        .hero-lead{font-size:clamp(1rem,1.6vw,1.13rem);max-width:35em;color:#a8bbcd}
        .hero-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:30px}
        .hero-points{margin-top:32px;display:grid;gap:11px}
        .hero-points li{display:flex;align-items:flex-start;gap:11px;font-size:14.5px;color:#b6c7d6}
        .tick{flex:0 0 auto;width:21px;height:21px;border-radius:50%;display:grid;place-items:center;
            background:rgba(45,212,191,.16);color:#5eead4;margin-top:2px}
        .tick svg{width:12px;height:12px}
        .hero-langs{margin-top:26px;font-size:13px;color:#7d93a8;letter-spacing:.4px}
        .hero-langs strong{color:#cbd5e1;font-weight:700}

        /* ── Hero mockup ──────────────────────────────────────── */
        .mock{background:#fff;border-radius:16px;box-shadow:0 30px 70px -22px rgba(0,0,0,.6);
            overflow:hidden;transform:perspective(1400px) rotateY(-7deg) rotateX(2deg)}
        .mock-bar{display:flex;align-items:center;gap:6px;padding:11px 14px;background:#f1f5f9;
            border-bottom:1px solid var(--line)}
        .mock-bar i{width:9px;height:9px;border-radius:50%;background:#cbd5e1;display:block}
        .mock-bar span{margin-left:8px;font-size:11.5px;color:var(--muted);font-weight:600;font-style:normal}
        .mock-body{display:flex;min-height:290px}
        .mock-side{flex:0 0 58px;background:var(--ink);padding:14px 0;display:grid;gap:10px;
            justify-items:center;align-content:start}
        .mock-side i{width:26px;height:26px;border-radius:8px;background:rgba(255,255,255,.09);display:block}
        .mock-side i:first-child{background:var(--teal)}
        .mock-main{flex:1;padding:16px;min-width:0}
        .mock-h{font-size:12.5px;font-weight:800;color:var(--text);margin-bottom:12px}
        .mock-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:9px;margin-bottom:16px}
        .mock-kpi{border:1px solid var(--line);border-radius:10px;padding:9px 10px;background:var(--surface-soft)}
        .mock-kpi span{display:block;font-size:9.5px;text-transform:uppercase;letter-spacing:.7px;
            color:var(--muted);font-weight:700}
        .mock-kpi i{display:block;height:8px;border-radius:4px;margin-top:7px;background:#cbd5e1;width:70%}
        .mock-kpi:nth-child(1) i{background:var(--teal);width:82%}
        .mock-kpi:nth-child(2) i{background:#7dd3fc;width:58%}
        .mock-chart{display:flex;align-items:flex-end;gap:7px;height:96px;padding:10px;
            border:1px solid var(--line);border-radius:10px;margin-bottom:14px}
        .mock-chart i{flex:1;border-radius:4px 4px 0 0;background:linear-gradient(180deg,var(--teal-light),var(--teal));display:block}
        .mock-lines{display:grid;gap:8px}
        .mock-lines i{display:block;height:8px;border-radius:4px;background:var(--line)}
        .mock-lines i:nth-child(2){width:82%}
        .mock-lines i:nth-child(3){width:64%}

        /* ── Sections ─────────────────────────────────────────── */
        section{scroll-margin-top:80px}
        .band{padding:82px 0}
        .band-soft{background:var(--surface-soft);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
        .kicker{display:block;font-size:12.5px;font-weight:800;letter-spacing:1.6px;text-transform:uppercase;
            color:var(--teal-dark);margin-bottom:12px}
        .sec-head{max-width:44rem;margin-bottom:46px}
        .sec-head.center{margin-left:auto;margin-right:auto;text-align:center}
        .sec-head h2{font-size:clamp(1.6rem,3vw,2.3rem);margin-bottom:14px}
        .sec-head p{font-size:17px;color:var(--body)}

        .cards{display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(250px,1fr))}
        .card{background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:24px;
            transition:border-color .15s ease,box-shadow .15s ease,transform .15s ease}
        .card:hover{border-color:#99f6e4;box-shadow:var(--shadow);transform:translateY(-2px)}
        .card h3{font-size:16.5px;margin-bottom:8px}
        .card p{font-size:14.5px;color:var(--body)}
        .ico{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;margin-bottom:16px;
            background:#f0fdfa;color:var(--teal-dark);border:1px solid #ccfbf1}
        .ico svg{width:21px;height:21px}
        .band-soft .card{background:#fff}

        /* ── Pricing ──────────────────────────────────────────── */
        .plans{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(272px,1fr));align-items:start}
        .plan{position:relative;display:flex;flex-direction:column;background:#fff;border:1px solid var(--line);
            border-radius:16px;padding:28px 24px;height:100%}
        .plan.is-featured{border-color:var(--teal);box-shadow:0 22px 48px -20px rgba(13,148,136,.5);
            border-width:2px;padding:32px 24px}
        .plan-tag{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:var(--teal);
            color:#fff;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;
            padding:5px 14px;border-radius:999px;white-space:nowrap}
        .plan h3{font-size:19px}
        .plan-desc{font-size:14px;color:var(--muted);margin-top:6px;min-height:2.6em}
        .plan-price{display:flex;align-items:baseline;flex-wrap:wrap;gap:7px;margin:20px 0 6px}
        .plan-amount{font-size:34px;font-weight:800;color:var(--text);letter-spacing:-.03em;line-height:1}
        .plan-cur{font-size:15px;font-weight:700;color:var(--body)}
        .plan-per{font-size:14px;color:var(--muted);font-weight:600}
        .plan-trial{display:inline-block;font-size:12.5px;font-weight:700;color:var(--ok);
            background:var(--ok-bg);border:1px solid var(--ok-line);border-radius:999px;padding:3px 11px}
        .plan-limits{margin:20px 0;padding-top:18px;border-top:1px solid var(--line-soft);display:grid;gap:11px}
        .plan-limits li{display:flex;align-items:flex-start;gap:10px;font-size:14.5px;color:var(--text)}
        .plan-limits .tick{background:var(--teal-soft);color:var(--teal-dark)}
        .plan-limits .tick svg{width:12px;height:12px}
        .plan-cta{margin-top:auto;padding-top:6px}
        .pricing-note{margin-top:26px;text-align:center;font-size:13.5px;color:var(--muted)}
        .empty-note{border:1px dashed var(--line);border-radius:var(--radius);padding:34px;text-align:center;
            color:var(--muted);background:#fff;font-size:15px}

        /* ── Demo form ────────────────────────────────────────── */
        .demo{background:var(--ink);color:#a8bbcd;padding:82px 0;position:relative;overflow:hidden}
        .demo::before{content:"";position:absolute;inset:0;pointer-events:none;
            background:radial-gradient(680px 380px at 88% 100%,rgba(13,148,136,.28),transparent 62%)}
        .demo-grid{position:relative;z-index:1;display:grid;gap:44px;
            grid-template-columns:minmax(0,.85fr) minmax(0,1.15fr);align-items:start}
        .demo h2{color:#fff;font-size:clamp(1.6rem,3vw,2.2rem);margin-bottom:14px}
        .demo .kicker{color:#5eead4}
        .demo-lead{font-size:16px;color:#a8bbcd}
        .demo-facts{margin-top:28px;display:grid;gap:16px}
        .demo-fact{display:flex;gap:13px;align-items:flex-start}
        .demo-fact .ico{margin:0;flex:0 0 auto;width:38px;height:38px;border-radius:10px;
            background:rgba(45,212,191,.14);border-color:rgba(45,212,191,.28);color:#5eead4}
        .demo-fact .ico svg{width:18px;height:18px}
        .demo-fact h4{color:#e2e8f0;font-size:14.5px;margin-bottom:2px}
        .demo-fact p{font-size:13.5px;color:#8ea4b8}

        .form-card{background:#fff;border-radius:18px;padding:30px;box-shadow:0 30px 70px -26px rgba(0,0,0,.65)}
        .form-grid{display:grid;gap:18px;grid-template-columns:1fr 1fr}
        .field{display:flex;flex-direction:column;gap:7px;min-width:0}
        .field.full{grid-column:1 / -1}
        .field label{font-size:13.5px;font-weight:700;color:var(--text)}
        .field label .req{color:var(--danger);margin-left:2px}
        .field label .opt{color:var(--muted);font-weight:600}
        .field input,.field select,.field textarea{
            width:100%;font:inherit;font-size:15px;color:var(--text);background:#fff;
            border:1px solid #cbd5e1;border-radius:10px;padding:11px 13px;transition:border-color .15s,box-shadow .15s}
        .field textarea{resize:vertical;min-height:118px;line-height:1.55}
        .field select{appearance:none;-webkit-appearance:none;padding-right:38px;cursor:pointer;
            background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2364748b' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat:no-repeat;background-position:right 12px center;background-size:16px}
        .field input:hover,.field select:hover,.field textarea:hover{border-color:#94a3b8}
        .field input:focus,.field select:focus,.field textarea:focus{
            outline:none;border-color:var(--teal);box-shadow:0 0 0 4px rgba(13,148,136,.16)}
        .field.has-error input,.field.has-error select,.field.has-error textarea{
            border-color:var(--danger);background:#fffafa}
        .field.has-error input:focus,.field.has-error select:focus,.field.has-error textarea:focus{
            box-shadow:0 0 0 4px rgba(185,28,28,.14)}
        .err{font-size:13px;font-weight:600;color:var(--danger);display:flex;align-items:flex-start;gap:6px}
        .err svg{width:14px;height:14px;flex:0 0 auto;margin-top:3px}
        .hint{font-size:12.5px;color:var(--muted)}
        .hp{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;
            clip:rect(0 0 0 0);clip-path:inset(50%);white-space:nowrap;border:0}
        .form-foot{margin-top:22px;display:flex;flex-direction:column;gap:14px}
        .privacy{font-size:12.5px;color:var(--muted);line-height:1.55}

        .alert{border-radius:12px;padding:14px 16px;font-size:14px;font-weight:600;margin-bottom:22px;
            display:flex;gap:11px;align-items:flex-start}
        .alert svg{width:18px;height:18px;flex:0 0 auto;margin-top:1px}
        .alert-ok{background:var(--ok-bg);border:1px solid var(--ok-line);color:var(--ok)}
        .alert-err{background:var(--danger-bg);border:1px solid var(--danger-line);color:var(--danger)}

        /* ── Footer ───────────────────────────────────────────── */
        .site-foot{background:#07101a;color:#8ea4b8;padding:54px 0 30px;font-size:14px}
        .foot-grid{display:grid;gap:36px;grid-template-columns:minmax(0,1.4fr) repeat(2,minmax(0,1fr))}
        .foot-grid h4{color:#e2e8f0;font-size:13px;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:14px}
        .foot-grid ul{display:grid;gap:9px}
        .foot-grid a{color:#8ea4b8}
        .foot-grid a:hover{color:#5eead4}
        .foot-brand p{margin-top:14px;max-width:30em;font-size:13.5px}
        .foot-login{margin-top:16px;font-size:13px;color:#7d93a8;background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:11px 13px;line-height:1.55}
        .foot-login code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:#5eead4;font-size:12.5px}
        .foot-bottom{margin-top:40px;padding-top:20px;border-top:1px solid rgba(255,255,255,.08);
            display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;font-size:13px;color:#64798d}

        /* ── Responsive ───────────────────────────────────────── */
        @media (max-width:960px){
            .hero-grid,.demo-grid{grid-template-columns:1fr}
            .mock{transform:none;max-width:560px}
            .foot-grid{grid-template-columns:1fr 1fr}
            .foot-brand{grid-column:1 / -1}
        }
        @media (max-width:760px){
            .site-nav a{display:none}
            .site-nav .head-cta{display:inline-flex}
            .hero{padding:52px 0 62px}
            .band,.demo{padding:60px 0}
            .form-card{padding:22px 18px}
            .form-grid{grid-template-columns:1fr}
            .plan.is-featured{padding:28px 22px}
        }
        @media (max-width:520px){
            .logo small{display:none}
            .hero-actions .btn{width:100%}
            .foot-grid{grid-template-columns:1fr}
        }
        @media (prefers-reduced-motion:reduce){
            html{scroll-behavior:auto}
            *,*::before,*::after{transition:none!important;animation:none!important}
            .btn:hover,.card:hover{transform:none}
        }
        @media print{.site-head,.demo,.site-foot{display:none}}
    </style>
</head>
<body>
    <a class="skip" href="#main">{{ __('app.site_skip_to_content') }}</a>

    <header class="site-head">
        <div class="wrap head-in">
            <a class="logo" href="{{ route('site.home') }}">
                <span class="logo-mark" aria-hidden="true">S</span>
                <span>SAFM<small>{{ __('app.site_brand_tagline') }}</small></span>
            </a>
            <nav class="site-nav" aria-label="{{ __('app.site_nav_label') }}">
                <a href="#modules">{{ __('app.site_nav_modules') }}</a>
                <a href="#benefits">{{ __('app.site_nav_features') }}</a>
                <a href="#pricing">{{ __('app.site_nav_pricing') }}</a>
                <a class="btn btn-primary btn-sm head-cta" href="#demo">{{ __('app.site_nav_demo') }}</a>
            </nav>
        </div>
    </header>

    <main id="main">

        {{-- ── Hero ─────────────────────────────────────────── --}}
        <section class="hero">
            <div class="wrap hero-grid">
                <div>
                    <span class="pill"><b>SAFM</b> {{ __('app.site_hero_badge') }}</span>
                    <h1>{{ __('app.site_hero_title') }} <em>{{ __('app.site_hero_title_accent') }}</em></h1>
                    <p class="hero-lead">{{ __('app.site_hero_lead') }}</p>

                    <div class="hero-actions">
                        <a class="btn btn-primary btn-lg" href="#demo">
                            {{ __('app.site_hero_cta_primary') }}
                            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
                            </svg>
                        </a>
                        <a class="btn btn-ghost btn-lg" href="#modules">{{ __('app.site_hero_cta_secondary') }}</a>
                    </div>

                    <ul class="hero-points">
                        @foreach (['site_hero_point_1', 'site_hero_point_2', 'site_hero_point_3'] as $point)
                            <li>
                                <span class="tick" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                                <span>{{ __('app.'.$point) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="hero-langs">
                        {{ __('app.site_hero_langs_label') }} <strong>{{ __('app.site_hero_langs') }}</strong>
                    </p>
                </div>

                {{-- Decorative product illustration: no data, purely presentational. --}}
                <div class="mock" role="img" aria-label="{{ __('app.site_mock_alt') }}">
                    <div class="mock-bar" aria-hidden="true">
                        <i></i><i></i><i></i>
                        <span>votre-entreprise.facturation.cfpss.ma</span>
                    </div>
                    <div class="mock-body" aria-hidden="true">
                        <div class="mock-side"><i></i><i></i><i></i><i></i><i></i><i></i></div>
                        <div class="mock-main">
                            <div class="mock-h">{{ __('app.site_mock_title') }}</div>
                            <div class="mock-kpis">
                                <div class="mock-kpi"><span>{{ __('app.site_mock_kpi_1') }}</span><i></i></div>
                                <div class="mock-kpi"><span>{{ __('app.site_mock_kpi_2') }}</span><i></i></div>
                                <div class="mock-kpi"><span>{{ __('app.site_mock_kpi_3') }}</span><i></i></div>
                            </div>
                            <div class="mock-chart">
                                @foreach ([38, 62, 45, 78, 55, 88, 70, 96] as $h)
                                    <i style="height:{{ $h }}%"></i>
                                @endforeach
                            </div>
                            <div class="mock-lines"><i></i><i></i><i></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── Modules ──────────────────────────────────────── --}}
        <section id="modules" class="band">
            <div class="wrap">
                <div class="sec-head center">
                    <span class="kicker">{{ __('app.site_modules_kicker') }}</span>
                    <h2>{{ __('app.site_modules_title') }}</h2>
                    <p>{{ __('app.site_modules_lead') }}</p>
                </div>
                <div class="cards">
                    @foreach ($modules as $module)
                        <article class="card">
                            <div class="ico" aria-hidden="true">
                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $module['icon'] }}"/>
                                </svg>
                            </div>
                            <h3>{{ __('app.site_module_'.$module['key'].'_title') }}</h3>
                            <p>{{ __('app.site_module_'.$module['key'].'_desc') }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ── Why SAFM ─────────────────────────────────────── --}}
        <section id="benefits" class="band band-soft">
            <div class="wrap">
                <div class="sec-head center">
                    <span class="kicker">{{ __('app.site_benefits_kicker') }}</span>
                    <h2>{{ __('app.site_benefits_title') }}</h2>
                    <p>{{ __('app.site_benefits_lead') }}</p>
                </div>
                <div class="cards">
                    @foreach ($benefits as $benefit)
                        <article class="card">
                            <div class="ico" aria-hidden="true">
                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $benefit['icon'] }}"/>
                                </svg>
                            </div>
                            <h3>{{ __('app.site_benefit_'.$benefit['key'].'_title') }}</h3>
                            <p>{{ __('app.site_benefit_'.$benefit['key'].'_desc') }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ── Pricing ──────────────────────────────────────── --}}
        <section id="pricing" class="band">
            <div class="wrap">
                <div class="sec-head center">
                    <span class="kicker">{{ __('app.site_pricing_kicker') }}</span>
                    <h2>{{ __('app.site_pricing_title') }}</h2>
                    <p>{{ __('app.site_pricing_lead') }}</p>
                </div>

                @if ($planList->isEmpty())
                    <p class="empty-note">{{ __('app.site_pricing_empty') }}</p>
                @else
                    <div class="plans">
                        @foreach ($planList as $index => $plan)
                            <article class="plan{{ $index === $featuredIndex ? ' is-featured' : '' }}">
                                @if ($index === $featuredIndex)
                                    <span class="plan-tag">{{ __('app.site_pricing_recommended') }}</span>
                                @endif

                                <h3>{{ $plan->name }}</h3>
                                <p class="plan-desc">{{ $plan->description ?: __('app.site_plan_no_description') }}</p>

                                <p class="plan-price">
                                    <span class="plan-amount">{{ $formatPrice($plan->price) }}</span>
                                    <span class="plan-cur">{{ $plan->currency }}</span>
                                    <span class="plan-per">{{ __($periodKeys[$plan->billing_period] ?? 'app.site_period_monthly') }}</span>
                                </p>

                                @if ((int) $plan->trial_days > 0)
                                    <span class="plan-trial">{{ trans_choice('app.site_pricing_trial', (int) $plan->trial_days) }}</span>
                                @endif

                                <ul class="plan-limits">
                                    @php
                                        $limits = [
                                            ['value' => $plan->max_users,      'one' => 'app.site_limit_users',      'all' => 'app.site_limit_users_unlimited'],
                                            ['value' => $plan->max_warehouses, 'one' => 'app.site_limit_warehouses', 'all' => 'app.site_limit_warehouses_unlimited'],
                                            ['value' => $plan->max_products,   'one' => 'app.site_limit_products',   'all' => 'app.site_limit_products_unlimited'],
                                        ];
                                    @endphp
                                    @foreach ($limits as $limit)
                                        <li>
                                            <span class="tick" aria-hidden="true">
                                                <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                            <span>
                                                @if ($limit['value'] === null)
                                                    {{ __($limit['all']) }}
                                                @else
                                                    {{ trans_choice($limit['one'], (int) $limit['value']) }}
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach

                                    @if (is_array($plan->features))
                                        @foreach ($plan->features as $feature)
                                            @if (is_string($feature) && $feature !== '')
                                                <li>
                                                    <span class="tick" aria-hidden="true">
                                                        <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </span>
                                                    <span>{{ $feature }}</span>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                </ul>

                                <div class="plan-cta">
                                    <a class="btn btn-block {{ $index === $featuredIndex ? 'btn-primary' : 'btn-outline' }}"
                                       href="#demo"
                                       data-plan="{{ $plan->id }}">
                                        {{ __('app.site_pricing_cta') }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <p class="pricing-note">{{ __('app.site_pricing_note') }}</p>
                @endif
            </div>
        </section>

        {{-- ── Demo request ─────────────────────────────────── --}}
        <section id="demo" class="demo">
            <div class="wrap demo-grid">
                <div>
                    <span class="kicker">{{ __('app.site_demo_kicker') }}</span>
                    <h2>{{ __('app.site_demo_title') }}</h2>
                    <p class="demo-lead">{{ __('app.site_demo_lead') }}</p>

                    <div class="demo-facts">
                        @foreach ([
                            ['key' => 'call',    'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                            ['key' => 'tailored','icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
                            ['key' => 'nocost',  'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                        ] as $fact)
                            <div class="demo-fact">
                                <div class="ico" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $fact['icon'] }}"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4>{{ __('app.site_demo_fact_'.$fact['key'].'_title') }}</h4>
                                    <p>{{ __('app.site_demo_fact_'.$fact['key'].'_desc') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-card">
                    @if (session('success'))
                        <div class="alert alert-ok" role="status">
                            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-err" role="alert">
                            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <span>{{ __('app.site_form_error_summary') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('site.demo.store') }}" novalidate>
                        @csrf
                        <input type="hidden" name="source" value="demo">

                        {{-- Honeypot: hidden from humans, irresistible to bots. --}}
                        <div class="hp" aria-hidden="true">
                            <label for="website">{{ __('app.site_form_honeypot') }}</label>
                            <input type="text" id="website" name="website" value="" tabindex="-1" autocomplete="off">
                        </div>

                        @php
                            $textFields = [
                                ['name' => 'company_name', 'type' => 'text',  'label' => 'site_form_company', 'required' => true,  'autocomplete' => 'organization', 'maxlength' => 150],
                                ['name' => 'contact_name', 'type' => 'text',  'label' => 'site_form_contact', 'required' => true,  'autocomplete' => 'name',         'maxlength' => 150],
                                ['name' => 'email',        'type' => 'email', 'label' => 'site_form_email',   'required' => true,  'autocomplete' => 'email',        'maxlength' => 190],
                                ['name' => 'phone',        'type' => 'tel',   'label' => 'site_form_phone',   'required' => false, 'autocomplete' => 'tel',          'maxlength' => 40],
                            ];
                        @endphp

                        <div class="form-grid">
                            @foreach ($textFields as $formField)
                                <div class="field @error($formField['name']) has-error @enderror">
                                    <label for="{{ $formField['name'] }}">
                                        {{ __('app.'.$formField['label']) }}@if ($formField['required'])<span class="req" aria-hidden="true">*</span>@else <span class="opt">({{ __('app.site_form_optional') }})</span>@endif
                                    </label>
                                    <input type="{{ $formField['type'] }}"
                                           id="{{ $formField['name'] }}"
                                           name="{{ $formField['name'] }}"
                                           value="{{ old($formField['name']) }}"
                                           autocomplete="{{ $formField['autocomplete'] }}"
                                           maxlength="{{ $formField['maxlength'] }}"
                                           @if ($formField['required']) required @endif
                                           @error($formField['name']) aria-invalid="true" aria-describedby="{{ $formField['name'] }}-error" @enderror>
                                    @error($formField['name'])
                                        <p class="err" id="{{ $formField['name'] }}-error">
                                            <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ $message }}</span>
                                        </p>
                                    @enderror
                                </div>
                            @endforeach

                            @if ($planList->isNotEmpty())
                                <div class="field full @error('plan_id') has-error @enderror">
                                    <label for="plan_id">
                                        {{ __('app.site_form_plan') }} <span class="opt">({{ __('app.site_form_optional') }})</span>
                                    </label>
                                    <select id="plan_id" name="plan_id" @error('plan_id') aria-invalid="true" aria-describedby="plan_id-error" @enderror>
                                        <option value="">{{ __('app.site_form_plan_none') }}</option>
                                        @foreach ($planList as $plan)
                                            <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>
                                                {{ $plan->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plan_id')
                                        <p class="err" id="plan_id-error">
                                            <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ $message }}</span>
                                        </p>
                                    @enderror
                                </div>
                            @endif

                            <div class="field full @error('message') has-error @enderror">
                                <label for="message">
                                    {{ __('app.site_form_message') }} <span class="opt">({{ __('app.site_form_optional') }})</span>
                                </label>
                                <textarea id="message" name="message" rows="5" maxlength="2000"
                                          placeholder="{{ __('app.site_form_message_placeholder') }}"
                                          @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="err" id="message-error">
                                        <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="form-foot">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                {{ __('app.site_form_submit') }}
                                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
                                </svg>
                            </button>
                            <p class="privacy">{{ __('app.site_form_privacy') }}</p>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-foot">
        <div class="wrap">
            <div class="foot-grid">
                <div class="foot-brand">
                    <a class="logo" href="{{ route('site.home') }}">
                        <span class="logo-mark" aria-hidden="true">S</span>
                        <span>SAFM<small>{{ __('app.site_brand_tagline') }}</small></span>
                    </a>
                    <p>{{ __('app.site_footer_tagline') }}</p>
                    <p class="foot-login">
                        {{ __('app.site_footer_login_note') }}<br>
                        <code>votre-entreprise.facturation.cfpss.ma</code>
                    </p>
                </div>
                <div>
                    <h4>{{ __('app.site_footer_product') }}</h4>
                    <ul>
                        <li><a href="#modules">{{ __('app.site_nav_modules') }}</a></li>
                        <li><a href="#benefits">{{ __('app.site_nav_features') }}</a></li>
                        <li><a href="#pricing">{{ __('app.site_nav_pricing') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4>{{ __('app.site_footer_contact') }}</h4>
                    <ul>
                        <li><a href="#demo">{{ __('app.site_nav_demo') }}</a></li>
                        <li>{{ __('app.site_footer_hours') }}</li>
                        <li>{{ __('app.site_footer_reply') }}</li>
                    </ul>
                </div>
            </div>
            <div class="foot-bottom">
                <span>&copy; {{ date('Y') }} SAFM. {{ __('app.site_footer_rights') }}</span>
                <span>{{ __('app.site_hero_langs') }}</span>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            // Pre-select the offer the visitor clicked in the pricing table.
            document.querySelectorAll('[data-plan]').forEach(function (link) {
                link.addEventListener('click', function () {
                    var select = document.getElementById('plan_id');
                    if (select) {
                        select.value = link.getAttribute('data-plan');
                    }
                });
            });
        })();
    </script>
    @if ($errors->any())
        <script>
            // Validation failed: bring the visitor straight back to the form.
            (function () {
                var section = document.getElementById('demo');
                if (section) {
                    section.scrollIntoView();
                }
                var firstError = document.querySelector('.field.has-error input, .field.has-error select, .field.has-error textarea');
                if (firstError) {
                    firstError.focus({ preventScroll: true });
                }
            })();
        </script>
    @endif
</body>
</html>
