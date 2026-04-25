<?php
header('Content-Type: application/json');
include '../config/koneksi.php';

/* ================= INPUT ================= */
$siswa_id   = $_POST['siswa_id'] ?? 0;
$userMessage = $_POST['question'] ?? $_POST['message'] ?? '';
$mode = $_POST['mode'] ?? 'chat';

if(!$siswa_id){
    echo json_encode(["response" => "siswa_id wajib"]);
    exit;
}

/* ================= AMBIL DATA NILAI ================= */
$data_nilai = "";
$mapel_nilai = [];

$stmt = $conn->prepare("
    SELECT m.nama_mapel, n.nilai, n.tanggal
    FROM nilai n
    JOIN mapel m ON n.mapel_id = m.id
    WHERE n.siswa_id=?
    ORDER BY n.tanggal ASC
");
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$result = $stmt->get_result();

while($d = $result->fetch_assoc()){
    $data_nilai .= "{$d['nama_mapel']} ({$d['tanggal']}): {$d['nilai']}\n";

    // kumpulkan per mapel
    $mapel_nilai[$d['nama_mapel']][] = $d['nilai'];
}
$stmt->close();

if(empty($data_nilai)){
    echo json_encode(["response" => "Belum ada data nilai."]);
    exit;
}

/* ================= DEFAULT MESSAGE ================= */
if(empty($userMessage)){
    $userMessage = "Analisa nilai siswa.";
}

/* ================= GEMINI CONFIG ================= */
$apiKey = "AIzaSyCOLN4JVNuM7IYwMRDuNPVXBDPAmcbcYoo";
$model  = "gemini-1.5-flash"; // lebih ringan

$url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";

/* ================= PROMPT ================= */
$prompt = "
Data nilai siswa:
$data_nilai

Pertanyaan:
$userMessage

Jawab singkat dalam bullet point, beri insight & rekomendasi.
";

/* ================= RETRY ================= */
function callGemini($url, $payload){
    for($i=0; $i<2; $i++){

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 20
        ]);

        $result = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($result, true);

        if($http == 200 && !empty($data['candidates'])){
            return trim($data['candidates'][0]['content']['parts'][0]['text']);
        }

        $msg = $data['error']['message'] ?? '';

        if(strpos($msg, 'high demand') !== false){
            sleep(2);
            continue;
        }

        return "AI Error";
    }

    return "AI sibuk";
}

/* ================= CALL ================= */
$payload = [
    "contents" => [[
        "parts" => [[ "text" => $prompt ]]
    ]]
];

$response = callGemini($url, $payload);

/* ================= FALLBACK CERDAS ================= */
if($response == "AI Error" || $response == "AI sibuk"){

    $total = 0;
    $count = 0;

    foreach($mapel_nilai as $mapel => $nilaiArr){
        foreach($nilaiArr as $n){
            $total += $n;
            $count++;
        }
    }

    $avg = $count ? round($total/$count,2) : 0;

    // cari mapel terbaik & terlemah
    $avg_mapel = [];

    foreach($mapel_nilai as $mapel => $nilaiArr){
        $avg_mapel[$mapel] = array_sum($nilaiArr)/count($nilaiArr);
    }

    arsort($avg_mapel);
    $terbaik = array_key_first($avg_mapel);

    asort($avg_mapel);
    $terlemah = array_key_first($avg_mapel);

    $response = "
⚠️ AI sedang sibuk, analisa sistem:

• Rata-rata nilai: $avg
• Performa: " . ($avg >= 85 ? "Sangat baik 🔥" : ($avg >= 75 ? "Baik 👍" : "Perlu peningkatan 📚")) . "

• Mapel terbaik: $terbaik
• Mapel perlu ditingkatkan: $terlemah

📌 Rekomendasi:
• Fokus latihan di $terlemah
• Pertahankan performa di $terbaik
";
}

/* ================= OUTPUT ================= */
echo json_encode([
    "response" => $response
]);