<?php
session_start();

// Proteksi halaman: Jika belum login, arahkan ke halaman login
if(!isset($_SESSION['status_login'])) {
    echo '<script>alert("Anda harus Login terlebih dahulu!"); window.location="login.php";</script>';
    exit;
}

require_once('koneksi.php');

// Inisialisasi parameter pencarian dan filter
$search   = isset($_GET['search']) ? $_GET['search'] : '';
$filter   = isset($_GET['filter']) ? $_GET['filter'] : '';
$kelas    = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$predikat = isset($_GET['predikat']) ? $_GET['predikat'] : '';
$status   = isset($_GET['status']) ? $_GET['status'] : ''; 
$page     = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$limit    = 10;
$offset   = ($page - 1) * $limit; // PERBAIKAN: Format perhitungan yang valid

// Membangun Query dengan kondisi Search dan Filter
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

// Mengambil total seluruh data untuk perhitungan Pagination
$sqlCount = "SELECT COUNT(*) FROM tabel_nilai $whereClause";
$stmtCount = $koneksi->prepare($sqlCount);
$stmtCount->execute($params);
$total_data = $stmtCount->fetchColumn();

// Menghitung total halaman
$total_pages = ceil($total_data / $limit);
if ($total_pages == 0) $total_pages = 1;

// Mengambil data yang sudah dilimitasi untuk halaman aktif
$sqlData = "SELECT * FROM tabel_nilai $whereClause LIMIT $limit OFFSET $offset";
$stmtData = $koneksi->prepare($sqlData);
$stmtData->execute($params);
$hasil = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// Mengambil daftar Kelas unik untuk Dropdown Filter
$sqlKelas = "SELECT DISTINCT KELAS FROM tabel_nilai ORDER BY KELAS ASC";
$stmtKelas = $koneksi->query($sqlKelas);
$listKelas = $stmtKelas->fetchAll(PDO::FETCH_COLUMN);

// URL Parameters untuk mempertahankan pencarian/filter saat pindah halaman & cetak
$search_param   = urlencode($search);
$filter_param   = urlencode($filter);
$kelas_param    = urlencode($kelas);
$predikat_param = urlencode($predikat);
$status_param   = urlencode($status);
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Data Nilai Mahasiswa</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container-fluid" style="padding: 30px;">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="mb-4 text-center"><i class="fa fa-users"></i> Data Penilaian Mahasiswa</h3>
                
                <!-- KONTROL BAGIAN ATAS TABEL YANG SUDAH DIGABUNG DAN DIRAPIKAN -->
                <div class="d-flex justify-content-between align-items-end mb-3">
                    
                    <!-- Bagian Kiri: Tombol Tambah, Print, dan Filter Cepat -->
                    <div class="d-flex align-items-center">
                        <a href="tambah.php" class="btn btn-success btn-md"><span class="fa fa-plus"></span> Tambah Data</a>
                        <a href="print.php?search=<?php echo $search_param; ?>&filter=<?php echo $filter_param; ?>&kelas=<?php echo $kelas_param; ?>&predikat=<?php echo $predikat_param; ?>&status=<?php echo $status_param; ?>" class="btn btn-secondary btn-md ml-2" target="_blank"><span class="fa fa-print"></span> Print</a>
                        
                        <div class="ml-3 border-left pl-3 d-flex">
                            <a href="?filter=kurang_60&search=<?php echo $search_param; ?>&kelas=<?php echo $kelas_param; ?>&predikat=<?php echo $predikat_param; ?>&status=<?php echo $status_param; ?>" class="btn btn-warning btn-md <?php echo ($filter == 'kurang_60') ? 'active' : ''; ?>">Nilai < 60</a>
                            <a href="?filter=lebih_60&search=<?php echo $search_param; ?>&kelas=<?php echo $kelas_param; ?>&predikat=<?php echo $predikat_param; ?>&status=<?php echo $status_param; ?>" class="btn btn-info btn-md ml-1 <?php echo ($filter == 'lebih_60') ? 'active' : ''; ?>">Nilai > 60</a>
                            <?php if(!empty($filter) || !empty($search) || !empty($kelas) || !empty($predikat) || !empty($status)): ?>
                                <a href="index.php" class="btn btn-danger btn-md ml-1">Reset</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Bagian Kanan: Search & LOG OUT -->
                    <div class="d-flex flex-column align-items-end">
                        <a href="logout.php" class="btn btn-danger btn-sm mb-2 font-weight-bold">
                            <i class="fa fa-sign-out"></i> LOG OUT
                        </a>
                        
                        <form action="" method="GET" class="form-inline">
                            <?php if(!empty($filter)): ?>
                                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                            <?php endif; ?>
                            
                            <select name="kelas" class="form-control form-control-sm mr-2">
                                <option value="">Semua Kelas</option>
                                <?php foreach($listKelas as $k): ?>
                                    <option value="<?php echo htmlspecialchars($k); ?>" <?php if($kelas == $k) echo 'selected'; ?>><?php echo htmlspecialchars($k); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="predikat" class="form-control form-control-sm mr-2">
                                <option value="">Semua Predikat</option>
                                <option value="A" <?php if($predikat == 'A') echo 'selected'; ?>>A</option>
                                <option value="B" <?php if($predikat == 'B') echo 'selected'; ?>>B</option>
                                <option value="C" <?php if($predikat == 'C') echo 'selected'; ?>>C</option>
                                <option value="D" <?php if($predikat == 'D') echo 'selected'; ?>>D</option>
                                <option value="E" <?php if($predikat == 'E') echo 'selected'; ?>>E</option>
                            </select>

                            <select name="status" class="form-control form-control-sm mr-2">
                                <option value="">Semua Status</option>
                                <option value="BERHASIL" <?php if(strtoupper($status) == 'BERHASIL') echo 'selected'; ?>>Berhasil</option>
                                <option value="MENGULANG" <?php if(strtoupper($status) == 'MENGULANG') echo 'selected'; ?>>Mengulang</option>
                            </select>

                            <input type="text" name="search" class="form-control form-control-sm mr-2" style="width: 150px;" placeholder="Ketik data..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-outline-primary btn-sm">Search</button>
                        </form>
                    </div>
                </div>
                
                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped text-center" style="font-size: 14px;">
                        <thead class="thead-dark">
                            <tr>
                                <th width="50px">NO</th>
                                <th>NIM</th>
                                <th>NAMA MAHASISWA</th>
                                <th>TGL LAHIR</th>
                                <th>FOTO</th>
                                <th>KELAS</th>
                                <th>N_WEB2</th>
                                <th>B_ING2</th>
                                <th>B_INDO</th>
                                <th>TOTAL NILAI</th>
                                <th>RATA-RATA</th>
                                <th>KETERANGAN</th>
                                <th>PREDIKAT</th>
                                <th>YUDISIUM</th>
                                <th style="min-width: 100px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $a = $offset + 1;
                            if (count($hasil) > 0) {
                                foreach($hasil as $isi){
                            ?>
                            <tr>
                                <td><?php echo $a ?></td>
                                <td><?php echo htmlspecialchars($isi['NIM']) ?></td>
                                <td class="text-left"><?php echo htmlspecialchars($isi['NAMA']) ?></td>
                                <td><?php echo date('d-m-Y', strtotime($isi['TANGGAL_LAHIR'])); ?></td>
                                <td>
                                    <img src="images/<?php echo htmlspecialchars($isi['FOTO']) ?>" alt="Foto" style="width: 60px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                                </td>
                                <td><?php echo htmlspecialchars($isi['KELAS']) ?></td>
                                <td><?php echo $isi['N_WEB2'] ?></td>
                                <td><?php echo $isi['B_ING2'] ?></td>
                                <td><?php echo $isi['B_INDO'] ?></td>
                                <td><strong><?php echo $isi['TOTAL'] ?></strong></td>
                                <td><strong><?php echo number_format($isi['RATA_RATA'], 2) ?></strong></td>
                                <td><?php echo $isi['KETERANGAN'] ?></td>
                                <td><?php echo $isi['PREDIKAT'] ?></td>
                                <td><strong><?php echo $isi['YUDISIUM'] ?></strong></td>
                                <td>
                                    <a href="edit.php?nim=<?php echo urlencode($isi['NIM']); ?>" class="btn btn-primary btn-sm mb-1" title="Edit"><span class="fa fa-edit"></span></a>
                                    <a href="print_khs.php?nim=<?php echo urlencode($isi['NIM']); ?>" target="_blank" class="btn btn-secondary btn-sm mb-1" title="Cetak KHS"><span class="fa fa-print"></span></a>
                                    <a onclick="return confirm('Apakah yakin data dengan NIM <?php echo htmlspecialchars($isi['NIM']); ?> akan dihapus?')" href="hapus.php?nim=<?php echo urlencode($isi['NIM']); ?>" class="btn btn-danger btn-sm mb-1" title="Hapus"><span class="fa fa-trash"></span></a>
                                </td>
                            </tr>
                            <?php
                                    $a++;
                                }
                            } else {
                                echo '<tr><td colspan="15" class="text-center">Data tidak ditemukan.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div style="font-size: 16px;">
                        <strong>Total Data : <?php echo $total_data; ?></strong>
                    </div>
                    
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item <?php if($page <= 1) { echo 'disabled'; } ?>">
                                <!-- PERBAIKAN: Format URL Pagination diperbaiki -->
                                <a class="page-link" href="<?php if($page <= 1){ echo '#'; } else { echo "?halaman=".($page - 1)."&search=$search_param&filter=$filter_param&kelas=$kelas_param&predikat=$predikat_param&status=$status_param"; } ?>">Previous</a>
                            </li>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if($page == $i) { echo 'active'; } ?>">
                                <a class="page-link" href="?halaman=<?php echo $i; ?>&search=<?php echo $search_param; ?>&filter=<?php echo $filter_param; ?>&kelas=<?php echo $kelas_param; ?>&predikat=<?php echo $predikat_param; ?>&status=<?php echo $status_param; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php if($page >= $total_pages) { echo 'disabled'; } ?>">
                                <a class="page-link" href="<?php if($page >= $total_pages){ echo '#'; } else { echo "?halaman=".($page + 1)."&search=$search_param&filter=$filter_param&kelas=$kelas_param&predikat=$predikat_param&status=$status_param"; } ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>
</body>
</html>