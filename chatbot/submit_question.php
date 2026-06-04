<?php
// 1. ALWAYS put error configuration at the absolute top of the file
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Declare JSON headers so the browser treats the stream correctly
header('Content-Type: application/json');

// 3. Include database safely
include '../db.php'; 

// 4. Test if the connection variable actually exists
if (!isset($conn) || !$conn) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Ralat: Sambungan pangkalan data (db.php) tidak dijumpai.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');

    if ($question === '') {
        echo json_encode(['status' => 'error', 'message' => 'Sila taip soalan anda sebelum hantar.']);
        exit;
    }

    $questionEscaped = mysqli_real_escape_string($conn, $question);
    $statusDefault = 'Belum Dijawab';

    // --- AUTOMATIC KEYWORD EXTRACTION ---
    // Clean punctuation and convert sentence to lowercase
    $cleanStr = strtolower($question);
    $cleanStr = preg_replace('/[^\w\s]/u', '', $cleanStr);
    
    // Define Malay filler words to filter out
    $stopWords = ['je', 'ke', 'di', 'dan', 'yang', 'untuk', 'ada', 'kah', 'itu', 'ini', 'saya', 'nak', 'boleh', 'tolong', 'berapa', 'berapakah', 'bila', 'bilakah', 'apa', 'apakah', 'siapa', 'siapakah', 'mengapa', 'bagaimana'];
    
    $words = explode(' ', $cleanStr);
    $extractedKeywords = [];

    foreach ($words as $word) {
        $word = trim($word);
        // Only keep words longer than 2 characters and not in the filler list
        if (strlen($word) > 2 && !in_array($word, $stopWords)) {
            $extractedKeywords[] = $word;
        }
    }

    // Join the remaining important words with commas (e.g., "harga, tiket, kanak-kanak")
    $autoKeywords = implode(', ', array_unique($extractedKeywords));
    $autoKeywordsEscaped = mysqli_real_escape_string($conn, $autoKeywords);
    // -------------------------------------

    // Save with the automatically extracted keywords!
    $query = "INSERT INTO qna (question, answer, keywords, status) VALUES ('$questionEscaped', '', '$autoKeywordsEscaped', '$statusDefault')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Pertanyaan anda telah dihantar!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sistem ralat. Sila cuba sebentar lagi']);
    }
    exit;
}