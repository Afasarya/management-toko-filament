<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Receipt #{{ $sale->invoice_number }}</title>
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
            max-width: 800px;
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
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .totals {
            width: 100%;
            text-align: right;
        }
        .totals table {
            width: 300px;
            float: right;
        }
        .totals td {
            padding: 2px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company['name'] }}</h1>
            <p>{{ $company['address'] }}</p>
            <p>Phone: {{ $company['phone'] }} | Email: {{ $company['email'] }}</p>
            <h2>SALES RECEIPT</h2>
        </div>
        
        <div class="info">
            <table>
                <tr>
                    <td width="50%">
                        <strong>Invoice #:</strong> {{ $sale->invoice_number }}<br>
                        <strong>Date:</strong> {{ $sale->sale_date->format('d/m/Y') }}<br>
                        <strong>Cashier:</strong> {{ $sale->user->name }}
                    </td>
                    <td width="50%">
                        <strong>Customer:</strong> {{ $sale->customer_name }}<br>
                        @if($sale->customer)
                            <strong>Address:</strong> {{ $sale->customer->address }}<br>
                            <strong>Phone:</strong> {{ $sale->customer->phone }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="50%">Item</th>
                    <th width="15%">Price</th>
                    <th width="10%">Qty</th>
                    <th width="20%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="totals">
            <table>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Payment:</strong></td>
                    <td>Rp {{ number_format($sale->payment_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Change:</strong></td>
                    <td>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        
        <div class="footer">
            <p>{{ $invoice['signature'] }}</p>
            <p>Thank you for your purchase!</p>
            <p>Printed on: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>