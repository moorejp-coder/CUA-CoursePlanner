<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — Busch School Course Planner</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Roboto', system-ui, sans-serif;
            background: #efebe9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .site-header {
            background: #0a3255;
            height: 62px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            flex-shrink: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .22);
        }
        .site-header img { height: 36px; width: auto; }
        .gold-bar {
            height: 2px;
            background: linear-gradient(to right, #b18f50 0%, rgba(177, 143, 80, .2) 60%, transparent 100%);
            flex-shrink: 0;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 3rem 2.5rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
            border: 1px solid #e2ddd8;
        }

        .status-code {
            font-family: 'Oswald', system-ui, sans-serif;
            font-size: 5rem;
            font-weight: 700;
            color: #e8e2db;
            line-height: 1;
            margin-bottom: .75rem;
            letter-spacing: .04em;
        }

        .icon-ring {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        h1 {
            font-family: 'Oswald', system-ui, sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0a3255;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .75rem;
        }

        p.lead {
            color: #6b7280;
            font-size: .95rem;
            line-height: 1.75;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            background: #B41100;
            color: #fff;
            text-decoration: none;
            padding: .7rem 2.25rem;
            border-radius: 8px;
            font-family: 'Oswald', system-ui, sans-serif;
            font-weight: 700;
            font-size: .9rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            transition: background .12s;
        }
        .btn:hover { background: #8C0D00; }

        .btn-secondary {
            display: inline-block;
            margin-left: .75rem;
            color: #0a3255;
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
        }
        .btn-secondary:hover { text-decoration: underline; }

        .site-footer {
            padding: .85rem 1.5rem;
            background: #071e38;
            text-align: center;
            flex-shrink: 0;
        }
        .site-footer p {
            color: rgba(255, 255, 255, .65);
            font-size: 11.5px;
        }
        .site-footer a {
            color: rgba(255, 255, 255, .8);
            text-decoration: underline;
            text-underline-offset: 2px;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <a href="/">
            <img src="/images/busch_logo_white.png" alt="The Busch School of Business at The Catholic University of America">
        </a>
    </header>
    <div class="gold-bar"></div>

    <main>
        <div class="card">
            <div class="status-code">{{ $status }}</div>

            <div class="icon-ring" style="background: {{ $iconBg }};">
                {!! $iconSvg !!}
            </div>

            <h1>{{ $title }}</h1>
            <p class="lead">{{ $message }}</p>

            <a href="{{ $actionUrl }}" class="btn">{{ $actionLabel }}</a>
            @if (!empty($secondaryUrl))
                <a href="{{ $secondaryUrl }}" class="btn-secondary">{{ $secondaryLabel }}</a>
            @endif
        </div>
    </main>

    <footer class="site-footer">
        <p>
            AI guidance is informational. Always verify with a
            <a href="https://business.catholic.edu/academics/academic-services/index.html" target="_blank">human advisor</a>
            before finalizing your schedule or degree plan.
        </p>
    </footer>
</body>
</html>
