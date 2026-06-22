<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #111827;
            margin: 0;
            padding: 24px;
            background: #f3f4f6;
        }
        .page {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 32px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .toolbar {
            max-width: 900px;
            margin: 0 auto 16px;
            display: flex;
            gap: 12px;
        }
        .btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
            background: #111827;
            color: #fff;
        }
        .btn-secondary {
            background: #fff;
            color: #111827;
            border: 1px solid #d1d5db;
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #111827;
        }
        .brand {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .brand img {
            height: 56px;
            width: auto;
        }
        .brand h1 {
            margin: 0;
            font-size: 24px;
        }
        .brand p {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 14px;
        }
        .meta {
            text-align: right;
            font-size: 14px;
        }
        .meta h2 {
            margin: 0 0 8px;
            font-size: 18px;
        }
        .meta p {
            margin: 4px 0;
            color: #374151;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6b7280;
            margin: 0 0 8px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 8px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background: #f9fafb;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #6b7280;
        }
        td.num, th.num { text-align: right; }
        .totals {
            margin-top: 24px;
            margin-left: auto;
            width: 320px;
        }
        .totals table td {
            border-bottom: none;
            padding: 6px 0;
        }
        .totals .grand td {
            font-size: 18px;
            font-weight: 700;
            border-top: 2px solid #111827;
            padding-top: 10px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 13px;
            text-align: center;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .page { border: 0; border-radius: 0; padding: 0; max-width: none; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn" onclick="window.print()">Print</button>
        <button class="btn btn-secondary" onclick="window.close()">Close</button>
    </div>

    <div class="page">
        @yield('content')
    </div>
</body>
</html>
