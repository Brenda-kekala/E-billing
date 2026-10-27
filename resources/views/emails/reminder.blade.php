<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; color: #1f2937; line-height: 1.6;">
    <h2>Turnover Tax Reminder</h2>
    <p>Hi {{ $userName }},</p>

    <p>
        This is a reminder about your Tax for <strong>{{ $period }}</strong>.
    </p>

    <table style="margin: 16px 0;">
        <tr>
            <td style="padding: 4px 12px 4px 0;">Amount due:</td>
            <td><strong>KES {{ number_format($taxDue, 2) }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0;">Deadline:</td>
            <td><strong>{{ $dueDate }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0;">Status:</td>
            <td><strong>{{ $status }}</strong></td>
        </tr>
    </table>

    <p>
        Please log in to your E-Billing dashboard to record your payment and stay compliant.
    </p>

    <p style="color: #6b7280; font-size: 12px; margin-top: 24px;">
        This is an automated reminder. Figures are estimates, confirm with KRA.
    </p>
</body>
</html>