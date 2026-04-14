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
            <li style="margin-bottom: 8px;"><strong>ID Peminjam:</strong> #<?php echo e($userId); ?></li>
            <li style="margin-bottom: 8px;"><strong>Nama Peminjam:</strong> <?php echo e($userName); ?></li>
            <li style="margin-bottom: 8px;"><strong>Nomor Invoice:</strong> <?php echo e($invoiceNumber); ?></li>
            <li style="margin-bottom: 8px;"><strong>Alat Dipinjam:</strong> <?php echo e($itemName); ?></li>
            <li style="margin-bottom: 8px;"><strong>Tanggal Pinjam:</strong> <?php echo e($tanggalPinjam); ?></li>
            <li style="margin-bottom: 8px;"><strong>Tanggal Selesai:</strong> <?php echo e($tanggalSelesai); ?></li>
            <li style="margin-bottom: 8px;"><strong>Status Transaksi:</strong> <span style="color: #059669; font-weight: bold;"><?php echo e($status); ?></span></li>
            <li><strong>Metode Pembayaran:</strong> <?php echo e($paymentMethod); ?></li>
        </ul>
    </div>

    <p style="margin-top: 20px;">Silakan periksa sistem untuk menyetujui (Approve) transaksi ini atau memproses alat fisik di toko.</p>
    <br>
    <p>Terima Kasih,<br><strong>Sistem Otomatis Adora Cam</strong></p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\peminjaman-alat\resources\views/emails/payment_success.blade.php ENDPATH**/ ?>