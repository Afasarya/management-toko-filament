<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice #{{ $purchase->invoice_number }}</title>
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
        .notes {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company['name'] }}</h1>
            <p>{{ $company['address'] }}</p>
            <p>Phone: {{ $company['phone'] }} | Email: {{ $company['email'] }}</p>
            <h2>PURCHASE INVOICE</h2>
        </div>
        
        <div class="info">
            <table>
                <tr>
                    <td width="50%">
                        <strong>Invoice #:</strong> {{ $purchase->invoice_number }}<br>
                        <strong>Date:</strong> {{ $purchase->purchase_date->format('d/m/Y') }}<br>
                        <strong>Purchaser:</strong> {{ $purchase->user->name }}
                    </td>
                    <td width="50%">
                        <strong>Supplier:</strong> {{ $purchase->supplier->name }}<br>
                        <strong>Address:</strong> {{ $purchase->supplier->address }}<br>
                        <strong>Phone:</strong> {{ $purchase->supplier->phone }}
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
                @foreach($purchase->items as $index => $item)
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
                    <td>Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        
        @if($purchase->notes)
            <div class="notes">
                <strong>Notes:</strong><br>
                {{ $purchase->notes }}
            </div>
        @endif
        
        <div class="footer">
            <p>This is a computer generated invoice. No signature required.</p>
            <p>Printed on: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>