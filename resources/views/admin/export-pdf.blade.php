<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Taxpayer Report</h2>
    <p>Generated: {{ now()->format('d M Y, H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Occupation</th>
                <th>Total Income (KES)</th>
                <th>Est. Tax (KES)</th>
                <th>Paid (KES)</th>
                <th>Flagged</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $row)
                <tr>
                    <td>{{ $row['user']->name }}</td>
                    <td>{{ config('tax.occupations')[$row['user']->occupation] ?? '—' }}</td>
                    <td>{{ number_format($row['income'], 2) }}</td>
                    <td>{{ number_format($row['estimated_tax'], 2) }}</td>
                    <td>{{ number_format($row['paid'], 2) }}</td>
                    <td>{{ $row['flagged'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>