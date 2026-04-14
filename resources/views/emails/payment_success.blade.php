<!DOCTYPE html>
<html>
<head>
    <title>Pemberitahuan Pembayaran</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h2>Halo Admin / Petugas Adora Cam,</h2>
    <p>Terdapat satu pembayaran yang telah berhasil dilunasi oleh Peminjam dengan rincian sebagai berikut:</p>

    <div style="background-color: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <ul style="list-style-type: none; padding-left: 0; margin: 0;">
            <li style="margin-bottom: 8px;"><strong>ID Peminjam:</strong> #{{ $userId }}</li>
            <li style="margin-bottom: 8px;"><strong>Nama Peminjam:</strong> {{ $userName }}</li>
            <li style="margin-bottom: 8px;"><strong>Nomor Invoice:</strong> {{ $invoiceNumber }}</li>
            <li style="margin-bottom: 8px;"><strong>Alat Dipinjam:</strong> {{ $itemName }}</li>
            <li style="margin-bottom: 8px;"><strong>Tanggal Pinjam:</strong> {{ $tanggalPinjam }}</li>
            <li style="margin-bottom: 8px;"><strong>Tanggal Selesai:</strong> {{ $tanggalSelesai }}</li>
            <li style="margin-bottom: 8px;"><strong>Status Transaksi:</strong> <span style="color: #059669; font-weight: bold;">{{ $status }}</span></li>
            <li><strong>Metode Pembayaran:</strong> {{ $paymentMethod }}</li>
        </ul>
    </div>

    <p style="margin-top: 20px;">Silakan periksa sistem untuk menyetujui (Approve) transaksi ini atau memproses alat fisik di toko.</p>
    <br>
    <p>Terima Kasih,<br><strong>Sistem Otomatis Adora Cam</strong></p>
</body>
</html>
