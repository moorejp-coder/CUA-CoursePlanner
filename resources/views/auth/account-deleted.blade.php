<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Deleted — Busch School Course Planner</title>
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
            box-shadow: 0 2px 12px rgba(0,0,0,.22);
        }
        .site-header img { height: 36px; width: auto; }
        .gold-bar { height: 2px; background: linear-gradient(to right,#b18f50 0%,rgba(177,143,80,.2) 60%,transparent 100%); flex-shrink:0; }

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
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            border: 1px solid #e2ddd8;
        }

        .icon-ring {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: #ecfdf5;
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

        .lead {
            color: #6b7280;
            font-size: .95rem;
            line-height: 1.75;
            margin-bottom: 1.75rem;
        }

        .manifest {
            background: #f9f7f5;
            border: 1px solid #e8e2db;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            text-align: left;
            margin-bottom: 1.75rem;
        }
        .manifest-title {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9ca3af;
            margin-bottom: .75rem;
        }
        .manifest-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .35rem 0;
            border-bottom: 1px solid #ede8e3;
            font-size: .875rem;
            color: #374151;
        }
        .manifest-row:last-child { border-bottom: none; }
        .manifest-row span:last-child {
            font-weight: 700;
            color: #059669;
        }

        .note {
            font-size: .8rem;
            color: #9ca3af;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            background: #B41100;
            color: #fff;
            text-decoration: none;
            padding: .7rem 2.5rem;
            border-radius: 8px;
            font-family: 'Oswald', system-ui, sans-serif;
            font-weight: 700;
            font-size: .9rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            transition: background .12s;
        }
        .btn:hover { background: #8C0D00; }

        .site-footer {
            padding: .85rem 1.5rem;
            background: #071e38;
            text-align: center;
            flex-shrink: 0;
        }
        .site-footer p { color: rgba(255,255,255,.65); font-size: 11.5px; }
        .site-footer a { color: rgba(255,255,255,.8); text-decoration: underline; text-underline-offset: 2px; }
    </style>
</head>
<body>
    <header class="site-header">
        <a href="/"><img src="/images/busch_logo_white.png" alt="Busch School of Business"></a>
    </header>
    <div class="gold-bar"></div>

    <main>
        <div class="card">
            <div class="icon-ring">
                <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1>Account Permanently Deleted</h1>

            <p class="lead">
                Your account and all associated data have been permanently removed from our system.
                A confirmation email has been sent to your address.
            </p>

            <div class="manifest">
                <div class="manifest-title">Data removed</div>

                <div class="manifest-row">
                    <span>Account &amp; credentials</span>
                    <span>Deleted</span>
                </div>
                <div class="manifest-row">
                    <span>Academic profile</span>
                    <span>{{ $profile > 0 ? 'Deleted' : 'None' }}</span>
                </div>
                <div class="manifest-row">
                    <span>Course history records</span>
                    <span>{{ $courses > 0 ? $courses.' deleted' : 'None' }}</span>
                </div>
                <div class="manifest-row">
                    <span>Active sessions</span>
                    <span>Deleted</span>
                </div>
                <div class="manifest-row">
                    <span>Password reset tokens</span>
                    <span>Deleted</span>
                </div>
                <div class="manifest-row">
                    <span>Uploaded files</span>
                    <span>None stored</span>
                </div>
            </div>

            <p class="note">
                Security logs (IP address only, no academic data) are automatically purged after 14 days
                as required for abuse prevention. No other copies of your data remain in this system.
            </p>

            <a href="{{ route('login') }}" class="btn">Back to Sign In</a>
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
