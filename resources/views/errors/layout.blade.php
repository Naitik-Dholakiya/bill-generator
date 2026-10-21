<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8fafc">
    <title>@yield('title', 'Error | ' . config('app.name', 'Billora'))</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #172033;
            background: #f8fafc;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }

        * { box-sizing: border-box; }
        body { min-width: 320px; min-height: 100vh; margin: 0; }
        main {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 32px 20px;
            background:
                radial-gradient(ellipse at 15% 10%, rgba(14, 165, 233, .09), transparent 38%),
                radial-gradient(ellipse at 90% 90%, rgba(20, 184, 166, .08), transparent 35%),
                #f8fafc;
        }
        .error-card {
            width: min(100%, 560px);
            padding: clamp(32px, 7vw, 64px);
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .08);
            text-align: center;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 42px;
            color: #0f766e;
            font-size: 14px;
            font-weight: 750;
            letter-spacing: .04em;
        }
        .brand-mark {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 11px;
            background: #ccfbf1;
            font-size: 18px;
        }
        .error-code {
            display: inline-block;
            margin-bottom: 18px;
            color: #0f766e;
            font-size: clamp(64px, 15vw, 96px);
            font-weight: 800;
            letter-spacing: -.08em;
            line-height: .95;
        }
        h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(24px, 5vw, 32px);
            font-weight: 700;
            letter-spacing: -.04em;
        }
        .message {
            max-width: 390px;
            margin: 14px auto 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.75;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 32px;
        }
        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            border: 1px solid #0f766e;
            border-radius: 12px;
            background: #0f766e;
            color: white;
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
            transition: background .15s ease, transform .15s ease;
        }
        .button:hover { transform: translateY(-1px); background: #115e59; }
        .button-secondary { border-color: #e2e8f0; background: white; color: #334155; }
        .button-secondary:hover { background: #f8fafc; }
        .button:focus-visible { outline: 3px solid #5eead4; outline-offset: 3px; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <main>
        <section class="error-card" role="main" aria-labelledby="error-heading">
            <div class="brand" aria-label="{{ config('app.name', 'Billora') }}">
                <span class="brand-mark" aria-hidden="true">B</span>
                {{ config('app.name', 'Billora') }}
            </div>
            <div class="error-code" aria-hidden="true">@yield('code')</div>
            <h1 id="error-heading">@yield('heading')</h1>
            <p class="message">@yield('message')</p>
            <nav class="actions" aria-label="Error page actions">
                <a class="button" href="{{ url('/') }}">Go to home</a>
                <a class="button button-secondary" href="javascript:history.back()">Go back</a>
            </nav>
        </section>
    </main>
</body>
</html>
