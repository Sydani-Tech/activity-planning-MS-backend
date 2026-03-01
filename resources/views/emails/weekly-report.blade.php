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
            max-width: 650px;
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

        .kpi-grid {
            display: flex;
            gap: 12px;
            margin: 20px 0;
        }

        .kpi-card {
            flex: 1;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }

        .kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
        }

        .kpi-label {
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th {
            background: #f8f9fa;
            padding: 10px 12px;
            font-size: 12px;
            text-transform: uppercase;
            color: #7f8c8d;
            text-align: left;
            letter-spacing: 0.5px;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f2f5;
            font-size: 13px;
            color: #2c3e50;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
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

        .progress-bar {
            height: 8px;
            background: #e8ecf0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 12px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            border-radius: 4px;
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
            <h1>📊 Weekly Activity Report</h1>
            <p>Week {{ $weekNumber }} — Activity Planning & Monitoring System</p>
        </div>
        <div class="body">
            <!-- KPI Summary -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-value">{{ $reportData['total'] }}</div>
                    <div class="kpi-label">Total</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-value" style="color: #27ae60;">{{ $reportData['completed'] }}</div>
                    <div class="kpi-label">Completed</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-value" style="color: #2980b9;">{{ $reportData['ongoing'] }}</div>
                    <div class="kpi-label">Ongoing</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-value" style="color: #f39c12;">{{ $reportData['pending'] }}</div>
                    <div class="kpi-label">Pending</div>
                </div>
            </div>

            <!-- Completion Rate -->
            <div style="margin: 20px 0;">
                <strong style="font-size: 14px;">Completion Rate: {{ $reportData['completion_rate'] }}%</strong>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $reportData['completion_rate'] }}%"></div>
                </div>
            </div>

            <!-- Department Breakdown -->
            @if(count($reportData['departments']) > 0)
                <h3 style="font-size: 15px; color: #2c3e50; margin: 24px 0 12px;">Department Breakdown</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Completed</th>
                            <th>Ongoing</th>
                            <th>Pending</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['departments'] as $dept)
                            <tr>
                                <td><strong>{{ $dept['department'] }}</strong></td>
                                <td><span class="status-badge status-completed">{{ $dept['completed'] }}</span></td>
                                <td><span class="status-badge status-ongoing">{{ $dept['ongoing'] }}</span></td>
                                <td><span class="status-badge status-pending">{{ $dept['pending'] }}</span></td>
                                <td>{{ $dept['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Recent Updates -->
            @if(count($reportData['recent_updates']) > 0)
                <h3 style="font-size: 15px; color: #2c3e50; margin: 24px 0 12px;">Recent Updates This Week</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Status</th>
                            <th>Updated By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['recent_updates'] as $update)
                            <tr>
                                <td>{{ $update['activity'] }}</td>
                                <td><span
                                        class="status-badge status-{{ $update['status'] }}">{{ ucfirst($update['status']) }}</span>
                                </td>
                                <td>{{ $update['updated_by'] }}</td>
                                <td>{{ $update['date'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="footer">
            Niger State Ministry of Health — APMS<br>
            This is an automated weekly report. Do not reply.
        </div>
    </div>
</body>

</html>