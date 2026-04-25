<?php
header('Content-Type: application/json');
include '../config/koneksi.php';

$siswa_id   = $_POST['siswa_id'] ?? 0;
$userMessage = $_POST['message'] ?? '';

if(!$siswa_id){
    echo json_encode(["response" => "siswa_id wajib"]);
    exit;
}

/* ================= AMBIL DATA NILAI ================= */
$data_nilai = "";

$q = mysqli_query($conn,"
    SELECT m.nama_mapel, n.nilai, n.tanggal
    FROM nilai n
    JOIN mapel m ON n.mapel_id = m.id
    WHERE n.siswa_id='$siswa_id'
    ORDER BY n.tanggal ASC
");

while($d = mysqli_fetch_assoc($q)){
    $data_nilai .= "{$d['nama_mapel']} ({$d['tanggal']}): {$d['nilai']}\n";
}

/* ================= DEFAULT MESSAGE ================= */
if(empty($userMessage)){
    $userMessage = "Berikan analisa dan rekomendasi dari data nilai siswa.";
}

/* ================= GEMINI CONFIG ================= */
$apiKey = "API_KEY_GEMINI_KAMU"; // ganti
$model  = "gemini-2.5-flash";

$url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";

/* ================= PROMPT ================= */
$prompt = "
Data nilai siswa:
$data_nilai

Pertanyaan:
$userMessage

Instruksi:
- Jawab singkat
- Gunakan bullet point
- Sertakan insight & rekomendasi jika relevan
";

/* ================= CALL GEMINI ================= */
$payload = [
    "contents" => [[
        "parts" => [[
            "text" => $prompt
        ]]
    ]]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT => 30
]);

$result = curl_exec($ch);

/* ================= ERROR HANDLING ================= */
if(curl_errno($ch)){
    echo json_encode([
        "response" => "Error cURL: " . curl_error($ch)
    ]);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($result, true);

if($http_code != 200){
    $msg = $data['error']['message'] ?? 'Error API';
    echo json_encode([
        "response" => "AI Error: " . $msg
    ]);
    exit;
}

/* ================= AMBIL HASIL ================= */
if(!empty($data['candidates'][0]['content']['parts'][0]['text'])){
    $response = trim($data['candidates'][0]['content']['parts'][0]['text']);
} else {
    $response = "AI tidak memberikan jawaban.";
}

/* ================= OUTPUT ================= */
echo json_encode([
    "response" => $response
]);