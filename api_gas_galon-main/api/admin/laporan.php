<?php
require_once '../../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Verifikasi token
$user = verifyToken($conn);
if ($user['role'] !== 'admin') {
    sendResponse(false, "Akses ditolak");
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type = $_GET['type'] ?? 'overview';
    
    switch ($type) {
        case 'overview':
            // Dashboard overview
            $overview = [];
            
            // Total pesanan hari ini
            $stmt = $conn->query("SELECT COUNT(*) FROM pesanan WHERE DATE(tanggal_pesan) = CURDATE()");
            $overview['pesanan_hari_ini'] = $stmt->fetchColumn();
            
            // Total pendapatan hari ini (hanya pesanan selesai)
            $stmt = $conn->query("SELECT COALESCE(SUM(total_harga), 0) FROM pesanan WHERE DATE(tanggal_pesan) = CURDATE() AND status = 'selesai'");
            $overview['pendapatan_hari_ini'] = $stmt->fetchColumn();
            
            // Total pesanan pending
            $stmt = $conn->query("SELECT COUNT(*) FROM pesanan WHERE status = 'menunggu'");
            $overview['pesanan_pending'] = $stmt->fetchColumn();
            
            // Produk stok menipis (< 10)
            $stmt = $conn->query("SELECT COUNT(*) FROM produk WHERE stok < 10");
            $overview['produk_stok_menipis'] = $stmt->fetchColumn();
            
            // Total bukti pengiriman hari ini (BARU!)
            $stmt = $conn->query("SELECT COUNT(*) FROM pesanan WHERE DATE(waktu_upload_bukti) = CURDATE() AND bukti_pengiriman IS NOT NULL");
            $overview['total_bukti_pengiriman'] = $stmt->fetchColumn();
            
            // Produk terlaris bulan ini
            $stmt = $conn->query("
                SELECT p.nama_produk, p.jenis, SUM(dp.jumlah) as total_terjual
                FROM detail_pesanan dp
                JOIN produk p ON dp.id_produk = p.id
                JOIN pesanan ps ON dp.id_pesanan = ps.id
                WHERE MONTH(ps.tanggal_pesan) = MONTH(CURDATE()) 
                AND YEAR(ps.tanggal_pesan) = YEAR(CURDATE())
                AND ps.status = 'selesai'
                GROUP BY dp.id_produk
                ORDER BY total_terjual DESC
                LIMIT 5
            ");
            $overview['produk_terlaris'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Statistik tambahan
            $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
            $overview['total_customer'] = $stmt->fetchColumn();
            
            $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'kurir'");
            $overview['total_kurir'] = $stmt->fetchColumn();
            
            $stmt = $conn->query("SELECT COUNT(*) FROM produk");
            $overview['total_produk'] = $stmt->fetchColumn();
            
            sendResponse(true, "Data overview berhasil diambil", $overview);
            break;
            
        case 'stok':
            // Laporan stok produk
            $stmt = $conn->query("
                SELECT 
                    id,
                    nama_produk,
                    jenis,
                    harga,
                    stok,
                    CASE 
                        WHEN stok = 0 THEN 'Habis'
                        WHEN stok < 10 THEN 'Menipis'
                        ELSE 'Aman'
                    END as status_stok
                FROM produk 
                ORDER BY stok ASC, nama_produk
            ");
            $stok = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(true, "Laporan stok berhasil diambil", $stok);
            break;
            
        case 'transaksi':
            // Laporan transaksi dengan filter tanggal
            $tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-01');
            $tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');
            
            // Detail transaksi per hari
            $stmt = $conn->prepare("
                SELECT 
                    DATE(p.tanggal_pesan) as tanggal,
                    COUNT(*) as jumlah_transaksi,
                    SUM(CASE WHEN p.status = 'selesai' THEN p.total_harga ELSE 0 END) as total_pendapatan,
                    SUM(CASE WHEN p.status = 'menunggu' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN p.status = 'diproses' THEN 1 ELSE 0 END) as diproses,
                    SUM(CASE WHEN p.status = 'dikirim' THEN 1 ELSE 0 END) as dikirim,
                    SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN p.status = 'dibatalkan' THEN 1 ELSE 0 END) as dibatalkan
                FROM pesanan p
                WHERE DATE(p.tanggal_pesan) BETWEEN ? AND ?
                GROUP BY DATE(p.tanggal_pesan)
                ORDER BY tanggal DESC
            ");
            $stmt->execute([$tanggal_dari, $tanggal_sampai]);
            $transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total summary
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_transaksi,
                    SUM(CASE WHEN status = 'selesai' THEN total_harga ELSE 0 END) as total_pendapatan,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as transaksi_selesai,
                    SUM(CASE WHEN status = 'dibatalkan' THEN 1 ELSE 0 END) as transaksi_dibatalkan
                FROM pesanan
                WHERE DATE(tanggal_pesan) BETWEEN ? AND ?
            ");
            $stmt->execute([$tanggal_dari, $tanggal_sampai]);
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, "Laporan transaksi berhasil diambil", [
                'detail' => $transaksi,
                'summary' => $summary,
                'periode' => [
                    'dari' => $tanggal_dari,
                    'sampai' => $tanggal_sampai
                ]
            ]);
            break;
            
        case 'kurir':
            // Laporan performa kurir
            $tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-01');
            $tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');
            
            $stmt = $conn->prepare("
                SELECT 
                    u.id, 
                    u.name, 
                    u.phone,
                    COUNT(p.id) as total_pesanan,
                    SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as pesanan_selesai,
                    SUM(CASE WHEN p.status = 'dikirim' THEN 1 ELSE 0 END) as pesanan_dikirim,
                    SUM(CASE WHEN p.status = 'diproses' THEN 1 ELSE 0 END) as pesanan_diproses,
                    SUM(CASE WHEN p.status = 'selesai' THEN p.total_harga ELSE 0 END) as total_nilai_pesanan,
                    SUM(CASE WHEN p.status = 'selesai' AND p.bukti_pengiriman IS NOT NULL THEN 1 ELSE 0 END) as pesanan_dengan_bukti
                FROM users u
                LEFT JOIN pesanan p ON u.id = p.id_kurir 
                    AND DATE(p.tanggal_pesan) BETWEEN ? AND ?
                WHERE u.role = 'kurir'
                GROUP BY u.id
                ORDER BY pesanan_selesai DESC, total_pesanan DESC
            ");
            $stmt->execute([$tanggal_dari, $tanggal_sampai]);
            $kurir_performa = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, "Laporan performa kurir berhasil diambil", [
                'data' => $kurir_performa,
                'periode' => [
                    'dari' => $tanggal_dari,
                    'sampai' => $tanggal_sampai
                ]
            ]);
            break;
            
        default:
            sendResponse(false, "Tipe laporan tidak valid");
    }
}
?>