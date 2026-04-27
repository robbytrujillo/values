<?php
session_start();
include '../config/koneksi.php';

function getRankingByMapel($conn, $siswa_id, $jenis, $mapel_id=''){
    $q = mysqli_query($conn,"SELECT kelas_id FROM siswa WHERE id='$siswa_id'");
    $s = mysqli_fetch_assoc($q);
    $kelas_id = $s['kelas_id'];

    $filter_mapel = '';
    if($mapel_id != ''){
        $filter_mapel = " AND n.mapel_id='$mapel_id'";
    }

    $sql = "
    SELECT n.siswa_id, SUM(n.nilai) as total
    FROM nilai n
    JOIN siswa s ON n.siswa_id = s.id
    WHERE n.jenis='$jenis'
    AND s.kelas_id='$kelas_id'
    $filter_mapel
    GROUP BY n.siswa_id
    ORDER BY total DESC
    ";

    $rank = mysqli_query($conn,$sql);

    $no = 1;
    while($r=mysqli_fetch_assoc($rank)){
        if($r['siswa_id']==$siswa_id){
            return $no;
        }
        $no++;
    }

    return '-';
}

$siswa_id = $_SESSION['user']['id'];
$mapel_id = $_GET['mapel_id'] ?? '';

$where = "WHERE siswa_id='$siswa_id'";
if($mapel_id != ''){
    $where .= " AND mapel_id='$mapel_id'";
}

// statistik
$q = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT 
    AVG(nilai) as rata,
    MAX(nilai) as max,
    MIN(nilai) as min,
    SUM(nilai) as total
FROM nilai
$where
"));

// data tabel
$data = mysqli_query($conn,"
SELECT n.*, m.nama_mapel
FROM nilai n
JOIN mapel m ON n.mapel_id=m.id
$where
ORDER BY n.tanggal DESC
");

$list = [];
while($d=mysqli_fetch_assoc($data)){
    $list[] = [
        'tanggal' => $d['tanggal'],
        'nama_mapel' => $d['nama_mapel'],
        'nilai' => $d['nilai'],
        'jenis' => ucfirst($d['jenis'])
    ];
}

// ranking
$rank_harian = getRankingByMapel($conn,$siswa_id,'harian',$mapel_id);
$rank_bulanan = getRankingByMapel($conn,$siswa_id,'bulanan',$mapel_id);
$rank_semester = getRankingByMapel($conn,$siswa_id,'semester',$mapel_id);

// top 5
$qk = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT kelas_id FROM siswa WHERE id='$siswa_id'
"));

$kelas_id = $qk['kelas_id'];

$filter_top = '';
if($mapel_id != ''){
    $filter_top = " AND n.mapel_id='$mapel_id'";
}

$q_top = mysqli_query($conn,"
SELECT s.nama, SUM(n.nilai) as total
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
WHERE s.kelas_id='$kelas_id'
$filter_top
GROUP BY s.id
ORDER BY total DESC
LIMIT 5
");

$top_list = [];
while($t=mysqli_fetch_assoc($q_top)){
    $top_list[] = $t;
}

echo json_encode([
    'rata' => round($q['rata'],2),
    'max' => $q['max'] ?? 0,
    'min' => $q['min'] ?? 0,
    'total' => $q['total'] ?? 0,
    'rank_harian' => $rank_harian,
    'rank_bulanan' => $rank_bulanan,
    'rank_semester' => $rank_semester,
    'top_siswa' => $top_list,
    'data' => $list
]);