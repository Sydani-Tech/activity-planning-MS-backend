<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: linear-gradient(135deg, #0f2940, #1a5276);
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 8px 0 0;
            color: #8899aa;
            font-size: 13px;
        }

        .body {
            padding: 30px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-ongoing {
            background: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #f0f2f5;
        }

        .info-label {
            font-weight: 600;
            color: #7f8c8d;
            width: 140px;
            font-size: 13px;
        }

        .info-value {
            color: #2c3e50;
            font-size: 14px;
        }

        .arrow {
            font-size: 18px;
            margin: 0 8px;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #95a5a6;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Activity Status Updated</h1>
            <p>Activity Planning & Monitoring System</p>
        </div>
        <div class="body">
            <h2 style="color: #2c3e50; font-size: 18px; margin-bottom: 20px;">{{ $activity->title }}</h2>

            <div style="text-align: center; margin: 20px 0;">
                <span class="status-badge status-{{ $oldStatus }}">{{ ucfirst($oldStatus) }}</span>
                <span class="arrow">→</span>
                <span class="status-badge status-{{ $activity->status }}">{{ ucfirst($activity->status) }}</span>
            </div>

            <div style="margin: 20px 0;">
                <div class="info-row">
                    <span class="info-label">Department</span>
                    <span class="info-value">{{ $activity->department->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Week</span>
                    <span class="info-value">{{ $activity->week ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Range</span>
                    <span class="info-value">{{ $activity->start_date->format('M d, Y') }} —
                        {{ $activity->end_date->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Updated By</span>
                    <span class="info-value">{{ $updatedBy }}</span>
                </div>
            </div>
        </div>
        <div class="footer">
            Niger State Ministry of Health — APMS
        </div>
    </div>
</body>

</html>