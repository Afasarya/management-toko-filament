<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
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
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
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
            <h2>INVENTORY REPORT</h2>
        </div>
        
        <div class="filters">
            <strong>Filters:</strong> 
            Category: {{ $filters['category'] }} | 
            Brand: {{ $filters['brand'] }} | 
            Stock Status: {{ $filters['stock_status'] }}
        </div>
        
        <div class="summary" style="overflow: auto;">
            <div class="summary-item">
                <div class="summary-title">Total Products</div>
                <div class="summary-value">{{ number_format($summary['total_products'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-title">Total Items</div>
                <div class="summary-value">{{ number_format($summary['total_items'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-title">Inventory Value</div>
                <div class="summary-value">Rp {{ number_format($summary['total_value'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-title">Low Stock</div>
                <div class="summary-value">{{ number_format($summary['low_stock_products'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-title">Out of Stock</div>
                <div class="summary-value">{{ number_format($summary['out_of_stock_products'], 0, ',', '.') }}</div>
            </div>
        </div>
        
        <h3>Product List</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Purchase Price</th>
                    <th>Selling Price</th>
                    <th>Stock</th>
                    <th>Min Stock</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->code }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->brand->name }}</td>
                        <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                        <td 
                            @if($product->stock <= $product->min_stock && $product->stock > 0)
                                style="background-color: #ffeeba;"
                            @elseif($product->stock == 0)
                                style="background-color: #f8d7da;"
                            @endif
                        >
                            {{ $product->stock }}
                        </td>
                        <td>{{ $product->min_stock }}</td>
                        <td>Rp {{ number_format($product->getTotalValue(), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6">TOTAL</th>
                    <th>{{ number_format($summary['total_items'], 0, ',', '.') }}</th>
                    <th></th>
                    <th>Rp {{ number_format($summary['total_value'], 0, ',', '.') }}</th>
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