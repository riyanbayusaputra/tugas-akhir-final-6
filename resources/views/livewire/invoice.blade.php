<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }

        .invoice-table th, .invoice-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .invoice-table th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }

        .status-lunas {
            margin-top: 20px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: green;
        }

        h3 {
            margin-top: 30px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        Bintang Rasa Catering<br>
        <strong>Invoice</strong><br>
        No. Pesanan: {{ $order->order_number }}
    </div>

    <h3>Informasi Pemesanan</h3>
    <table class="invoice-table">
        <tr>
            <th>Tanggal Pemesanan</th>
            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
        </tr>
        <tr>
            <th>Nama Penerima</th>
            <td>{{ $order->recipient_name }}</td>
        </tr>
        <tr>
            <th>Alamat Pengiriman</th>
            <td>
                {{ $order->shipping_address }}<br>
                {{ $order->kecamatan_name }}, {{ $order->kabupaten_name }}, {{ $order->provinsi_name }}
            </td>
        </tr>
        <tr>
            <th>Status Pembayaran</th>
            <td>{{ \App\Services\OrderStatusService::getPaymentStatusLabel($order->payment_status) }}</td>
        </tr>
    </table>

    @if($order->payment_status === 'paid')
        <div class="status-lunas">
            LUNAS
        </div>
    @endif

    <h3>Detail Pesanan</h3>
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Nama Menu</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->Items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($order->is_custom)
        <h3>Informasi Pesanan Custom</h3>
        <table class="invoice-table">
            <tr>
                <th>Deskripsi Custom</th>
                <td>{{ $order->custom_description }}</td>
            </tr>
        </table>
    @endif

    <h3>Detail Harga</h3>
    <table class="invoice-table">
        <tr>
            <th>Subtotal</th>
            <td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Biaya Pengiriman</th>
            <td>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
        </tr>
        @if($order->is_custom)
            <tr>
                <th>Biaya Tambahan Custom</th>
                <td>Rp {{ number_format($order->price_adjustment, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr>
            <th>Total Pembayaran</th>
            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        Terima kasih telah memilih Bintang Rasa Catering!
    </div>
</body>
</html>
