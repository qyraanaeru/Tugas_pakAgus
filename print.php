<?php
require_once('koneksi.php');

// Tangkap parameter filter dari URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$predikat = isset($_GET['predikat']) ? $_GET['predikat'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : ''; 

// Susun Query
$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (NAMA LIKE :search OR NIM LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($filter == 'kurang_60') {
    $whereClause .= " AND RATA_RATA < 60";
} elseif ($filter == 'lebih_60') {
    $whereClause .= " AND RATA_RATA >= 60";
}
if (!empty($kelas)) {
    $whereClause .= " AND KELAS = :kelas";
    $params[':kelas'] = $kelas;
}
if (!empty($predikat)) {
    $whereClause .= " AND PREDIKAT = :predikat";
    $params[':predikat'] = $predikat;
}
if (!empty($status)) {
    // PERBAIKAN: Menggunakan UPPER agar mengabaikan huruf besar/kecil di DB
    $whereClause .= " AND UPPER(KETERANGAN) = UPPER(:status)";
    $params[':status'] = $status;
}

// Eksekusi Query
$sql = "SELECT * FROM tabel_nilai $whereClause";
$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$hasil = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_data = count($hasil); 

$limit_per_page = 10;
$halaman_data = array_chunk($hasil, $limit_per_page);
$total_halaman_pdf = count($halaman_data);

ob_start();
?>
<style>
    body {
        font-family: Arial, sans-serif;
    }
    .header-container {
        width: 100%;
        margin: 0 auto;
        margin-bottom: 10px;
    }
    .filter-info {
        float: left;
        font-size: 10px;
        color: #333;
    }
    .total-text {
        text-align: right;
        font-size: 11px;
        font-weight: bold;
    }
    .main-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 8px;
    }
    .main-table th, .main-table td {
        border: 1px solid black;
        padding: 4px;
        text-align: center;
        vertical-align: middle;
    }
    .main-table th {
        background-color: #f2f2f2;
    }
    h2 {
        text-align: center;
        margin-bottom: 5px;
    }
</style>

<?php
if($total_data == 0) {
    echo '<page backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm">';
    echo '<h2>Data Penilaian Mahasiswa</h2>';
    echo '<p style="text-align:center;">Data tidak ditemukan.</p>';
    echo '</page>';
} else {
    $no = 1; 
    
    for($i = 0; $i < $total_halaman_pdf; $i++) {
        $data_halaman_ini = $halaman_data[$i];
        echo '<page backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm">';
?>
        <h2>Data Penilaian Mahasiswa</h2>
        <div class="header-container">
            <div class="filter-info">
                <?php 
                    $activeFilters = [];
                    if(!empty($kelas)) $activeFilters[] = "Kelas: $kelas";
                    if(!empty($predikat)) $activeFilters[] = "Predikat: $predikat";
                    if(!empty($status)) $activeFilters[] = "Status: " . strtoupper($status);
                    if(!empty($search)) $activeFilters[] = "Pencarian: $search";
                    
                    if(count($activeFilters) > 0) {
                        echo "Filter Aktif: " . implode(" | ", $activeFilters);
                    }
                ?>
            </div>
            <div class="total-text">Total Data: <?php echo $total_data; ?> (Halaman <?php echo ($i + 1) . ' dari ' . $total_halaman_pdf; ?>)</div>
        </div>
        
        <table class="main-table" align="center">
            <thead>
                <tr>
                    <th width="20">NO</th>
                    <th width="50">NIM</th>
                    <th width="120">NAMA MAHASISWA</th>
                    <th width="50">TGL LAHIR</th>
                    <th width="40">FOTO</th>
                    <th width="40">KELAS</th>
                    <th width="40">N_WEB2</th>
                    <th width="40">B_ING2</th>
                    <th width="40">B_INDO</th>
                    <th width="50">TOTAL NILAI</th>
                    <th width="50">RATA-RATA</th>
                    <th width="80">KETERANGAN</th>
                    <th width="60">PREDIKAT</th>
                    <th width="80">YUDISIUM</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($data_halaman_ini as $data) {
                    echo "<tr>";
                    echo "<td>".$no."</td>";
                    echo "<td>".htmlspecialchars($data['NIM'])."</td>";
                    echo "<td style='text-align: left;'>".htmlspecialchars($data['NAMA'])."</td>";
                    echo "<td>".date('d-m-Y', strtotime($data['TANGGAL_LAHIR']))."</td>";
                    // Pastikan path image benar. Pada html2pdf path kadang perlu absolute path
                    echo "<td><img src='images/".htmlspecialchars($data['FOTO'])."' style='width: 25px; height: 35px;'></td>";
                    echo "<td>".htmlspecialchars($data['KELAS'])."</td>";
                    echo "<td>".$data['N_WEB2']."</td>";
                    echo "<td>".$data['B_ING2']."</td>";
                    echo "<td>".$data['B_INDO']."</td>";
                    echo "<td>".$data['TOTAL']."</td>";
                    echo "<td>".number_format($data['RATA_RATA'], 2)."</td>"; 
                    echo "<td>".$data['KETERANGAN']."</td>";
                    echo "<td>".$data['PREDIKAT']."</td>";
                    echo "<td>".$data['YUDISIUM']."</td>";
                    echo "</tr>";
                    $no++; 
                }
                ?>
            </tbody>
        </table>
<?php
        echo '</page>';
    }
}
?>

<?php
$html = ob_get_contents();
ob_end_clean();

require 'html2pdf/autoload.php';

try {
    $pdf = new Spipu\Html2Pdf\Html2Pdf('L', 'A4', 'en'); 
    $pdf->writeHTML($html);
    $pdf->output('Data_Penilaian_Semua.pdf', 'I');
} catch (Exception $e) {
    $pdf->clean();
    print 'Terjadi kesalahan saat mencetak PDF: ' . $e->getMessage();
}
?>