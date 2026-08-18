<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Finance Expense Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #000; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #555; font-size: 11px; }
        .filter-info { margin-bottom: 20px; font-size: 11px; }
        .filter-info span { margin-right: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 6px 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .grand-total { font-weight: bold; background-color: #f9f9f9; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ddd; padding-top: 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <div class="header">
        <h1>FINANCE EXPENSE REPORT</h1>
        <p>PT BIO PILAR</p>
    </div>

    <div class="filter-info">
        @if($request->filled('date_from') || $request->filled('date_to'))
            <span>Period:</span> {{ $request->date_from ? date('d/m/Y', strtotime($request->date_from)) : 'Start' }} - {{ $request->date_to ? date('d/m/Y', strtotime($request->date_to)) : 'End' }}<br>
        @endif
        @if($request->filled('project_id'))
            @php $p = \App\Models\Project::find($request->project_id); @endphp
            <span>Project:</span> {{ $p ? $p->project_name : 'All' }}<br>
        @endif
        @if($request->filled('category_id'))
            @php $c = \App\Models\FinanceExpenseCategory::find($request->category_id); @endphp
            <span>Category:</span> {{ $c ? $c->name : 'All' }}<br>
        @endif
        @if($request->filled('payment_method'))
            <span>Method:</span> {{ $request->payment_method }}<br>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">Date</th>
                <th width="12%">Expense No</th>
                <th width="20%">Project</th>
                <th width="15%">Category</th>
                <th width="20%">Description</th>
                <th width="10%">Method</th>
                <th width="13%" class="text-right">Amount (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td>{{ $expense->expense_number }}</td>
                    <td>{{ $expense->project->project_name ?? '-' }}</td>
                    <td>{{ $expense->category->name ?? '-' }}</td>
                    <td>{{ \Str::limit($expense->description, 50) }}</td>
                    <td>{{ $expense->payment_method }}</td>
                    <td class="text-right">{{ number_format($expense->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        @if($expenses->count() > 0)
        <tfoot>
            <tr class="grand-total">
                <td colspan="6" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($totalAmount, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Generated on {{ date('d M Y H:i') }} | Finance Dept.
    </div>

</body>
</html>
