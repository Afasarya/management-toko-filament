<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .header p {
            margin: 0;
            font-size: 10px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 2px;
            vertical-align: top;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .summary {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            width: 18%;
            float: left;
            margin-right: 2%;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            clear: both;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company['name'] }}</h1>
            <p>{{ $company['address'] }}</p>
            <p>Phone: {{ $company['phone'] }}</p>
            <h2>SALES REPORT</h2>
            <p>Period: {{ $start_date->format('d/m/Y') }} - {{ $end_date->format('d/m/Y') }}</p>
        </div>
        
        <div class="summary" style="overflow: auto;">
            <div class="summary-item">
                <div class="summary-title">Total Sales</div>
                <div class="summary-value">Rp {{ number_format($totals['total_sales'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-title">Total Cost</div>
                <div class="summary-value">Rp {{ number_format($totals['total_cost'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-title">Total Profit</div>
                <div class="summary-value">Rp {{ number_format($totals['total_profit'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-title">Items Sold</div>
                <div class="summary-value">{{ number_format($totals['total_items'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-title">Transactions</div>
                <div class="summary-value">{{ number_format($totals['total_transactions'], 0, ',', '.') }}</div>
            </div>
        </div>
        
        <h3>Sales Transactions</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Cost</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td>{{ $sale->customer_name }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>{{ $sale->items->sum('quantity') }}</td>
                        <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($sale->cost_amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($sale->profit, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5">TOTAL</th>
                    <th>Rp {{ number_format($totals['total_sales'], 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($totals['total_cost'], 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($totals['total_profit'], 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
        
        <div class="footer">
            <p>Generated on: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
            <p>This is a computer generated report.</p>
        </div>
    </div>
</body>
</html>