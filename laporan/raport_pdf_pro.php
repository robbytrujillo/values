<?php
require('../tcpdf/tcpdf.php');
include '../config/koneksi.php';

$pdf = new TCPDF();
$pdf->AddPage();

$pdf->SetFont('helvetica','B',14);

// KOP
$pdf->Cell(0,10,'SMA NEGERI 1',0,1,'C');
$pdf->Cell(0,10,'RAPORT SISWA',0,1,'C');

$pdf->Ln(5);

// TABLE
$pdf->SetFont('helvetica','',10);

$q=mysqli_query($conn,"
SELECT mapel.nama_mapel,nilai.nilai,nilai.deskripsi
FROM nilai
JOIN mapel ON nilai.mapel_id=mapel.id
");

$total=0; $jumlah=0;

foreach($q as $d){
$pdf->Cell(60,10,$d['nama_mapel'],1);
$pdf->Cell(20,10,$d['nilai'],1);
$pdf->Cell(100,10,$d['deskripsi'],1);
$pdf->Ln();

$total += $d['nilai'];
$jumlah++;
}

$rata = $total / $jumlah;

$pdf->Ln(5);
$pdf->Cell(0,10,"Total: $total",0,1);
$pdf->Cell(0,10,"Rata-rata: ".round($rata,2),0,1);

// TTD
$pdf->Ln(15);

$pdf->Cell(90,10,'Kepala Sekolah',0,0,'C');
$pdf->Cell(90,10,'Wali Kelas',0,1,'C');

$pdf->Ln(20);

$pdf->Cell(90,10,'(................)',0,0,'C');
$pdf->Cell(90,10,'(................)',0,1,'C');

$pdf->Output();