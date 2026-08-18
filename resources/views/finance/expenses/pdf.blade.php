<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report - {{ $expense->expense_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; }
        .info-table { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .info-table th { text-align: left; padding: 8px; width: 30%; color: #555; vertical-align: top; }
        .info-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .amount-row { font-size: 18px; font-weight: bold; background: #f9f9f9; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
        .signatures { width: 100%; margin-top: 50px; text-align: center; border-collapse: collapse; }
        .signatures td { width: 33.33%; padding: 10px; }
        .signatures .sign-line { border-bottom: 1px solid #000; margin: 40px 20px 5px; height: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>EXPENSE RECORD</h1>
        <p>No: {{ $expense->expense_number }}</p>
    </div>

    <table class="info-table">
        <tr>
            <th>Date</th>
            <td>{{ $expense->expense_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <th>Project</th>
            <td>{{ $expense->project ? $expense->project->project_name : '-' }}</td>
        </tr>
        <tr>
            <th>Category</th>
            <td>{{ $expense->category ? $expense->category->name : '-' }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $expense->description }}</td>
        </tr>
        <tr class="amount-row">
            <th>Amount</th>
            <td>Rp {{ number_format($expense->amount, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Payment Method</th>
            <td>{{ $expense->payment_method }}</td>
        </tr>
        <tr>
            <th>Paid To</th>
            <td>{{ $expense->paid_to ?? '-' }}</td>
        </tr>
        <tr>
            <th>Reference No</th>
            <td>{{ $expense->reference_number ?? '-' }}</td>
        </tr>
        <tr>
            <th>Notes</th>
            <td>{{ $expense->notes ?? '-' }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Prepared By
                <div class="sign-line"></div>
                {{ $expense->creator->name ?? '______________' }}
            </td>
            <td>
                Checked By
                <div class="sign-line"></div>
                Finance Dept.
            </td>
            <td>
                Approved By
                <div class="sign-line"></div>
                Management
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y H:i') }}
    </div>

</body>
</html>
