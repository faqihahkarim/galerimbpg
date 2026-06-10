<?php
// chatbot/chatbot_handler.php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../db.php';

if (!isset($conn) || !$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Sambungan database gagal.']);
    exit;
}

// Handle GET request for dynamic suggestions
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get_suggestions') {
    
    $query = "SELECT qna_id, question FROM qna WHERE qna_id IN (1, 2, 3, 4) AND status = 'Dijawab'";
    $result = mysqli_query($conn, $query);
    $data = [];
    
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

// Handle POST request for chat messaging
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMsg = trim($_POST['message'] ?? '');

    if ($userMsg === '') {
        echo json_encode(['status' => 'error', 'message' => 'Mesej kosong.']);
        exit;
    }

    // 1. Normalize user message (Lowercase and strip punctuation)
    $cleanMsg = strtolower($userMsg);
    $cleanMsg = preg_replace('/[^\w\s]/u', '', $cleanMsg); // Removes symbols/question marks
    $escapedMsg = mysqli_real_escape_string($conn, $cleanMsg);

    // LEVEL 1: Check for direct string matching (Broad match)
    $query = "SELECT answer FROM qna WHERE status = 'Dijawab' AND (LOWER(question) LIKE '%$escapedMsg%' OR LOWER(keywords) LIKE '%$escapedMsg%') LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['status' => 'success', 'answer' => $row['answer']]);
        exit;
    }

    // 1.5: Short Phrase Matching 
    // Fetch answers and phrases to check multi-word intent directly
    $phraseQuery = "SELECT answer, phrases FROM qna WHERE status = 'Dijawab' AND phrases IS NOT NULL AND phrases != ''";
    $phraseResult = mysqli_query($conn, $phraseQuery);

    if ($phraseResult && mysqli_num_rows($phraseResult) > 0) {
        while ($row = mysqli_fetch_assoc($phraseResult)) {
            // Split phrases by comma
            $phraseArray = explode(',', $row['phrases']);
            foreach ($phraseArray as $phrase) {
                $trimmedPhrase = trim(strtolower($phrase));
                
                // If the user's cleaned input text explicitly contains this admin phrase
                if ($trimmedPhrase !== '' && strpos($cleanMsg, $trimmedPhrase) !== false) {
                    echo json_encode(['status' => 'success', 'answer' => $row['answer']]);
                    exit; // Match found! Stop execution and reply immediately
                }
            }
        }
    }

    // --- LEVEL 2: Tokenized Keyword Relevance Match (Fallback if phrases do not match) ---
    $stopWords = ['je', 'ke', 'di', 'dan', 'yang', 'untuk', 'ada', 'kah', 'itu', 'ini', 'saya', 'nak', 'boleh', 'tolong', 'bila', 'nak', 'pergi',
                  'adakah','bilakah','bolehkah','berapa','berapakah','apa','apakah','siapa','siapakah','mengapa','bagaimana','galeri'];
    
    $words = explode(' ', $cleanMsg);
    $filteredKeywords = [];

    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) > 1 && !in_array($word, $stopWords)) {
            $filteredKeywords[] = mysqli_real_escape_string($conn, $word);
        }
    }

    if (!empty($filteredKeywords)) {
        // Dynamic scoring system using CASE WHEN
        $scoreFields = [];
        foreach ($filteredKeywords as $keyword) {
            $scoreFields[] = "(CASE WHEN LOWER(question) LIKE '%$keyword%' THEN 2 ELSE 0 END)";
            $scoreFields[] = "(CASE WHEN LOWER(keywords) LIKE '%$keyword%' THEN 1 ELSE 0 END)";
        }
        
        // Sum up the scores for each matched word
        $scoringSql = implode(' + ', $scoreFields);
        
        // Strict safety filter so it must match at least ONE keyword
        $conditions = [];
        foreach ($filteredKeywords as $keyword) {
            $conditions[] = "LOWER(question) LIKE '%$keyword%'";
            $conditions[] = "LOWER(keywords) LIKE '%$keyword%'";
        }
        $sqlConditions = implode(' OR ', $conditions);

        // Query rows ordering them by the highest match score first!
        $tokenQuery = "SELECT answer, ($scoringSql) as relevance 
                       FROM qna 
                       WHERE status = 'Dijawab' AND ($sqlConditions) 
                       HAVING relevance > 0
                       ORDER BY relevance DESC 
                       LIMIT 1";
        
        $tokenResult = mysqli_query($conn, $tokenQuery);
        
        if ($tokenResult && mysqli_num_rows($tokenResult) > 0) {
            $row = mysqli_fetch_assoc($tokenResult);
            echo json_encode(['status' => 'success', 'answer' => $row['answer']]);
            exit;
        }
    }

    // LEVEL 3: Fallback Form Context Instruction
    echo json_encode([
        'status' => 'fallback', 
        'message' => 'Maaf, saya tidak menemui jawapan tepat untuk soalan itu. 
                      Sila gunakan borang pertanyaan di bahagian "Hubungi Kami" (footer bawah halaman) untuk menghantar soalan ini secara langsung kepada pengurus galeri kami!
                      
                      Anda juga boleh berhubung secara terus kepada:
                      013-2988693 / 019-2028241 
                      (Pn. ....) untuk bantuan segera.'
    ]);
    exit;
}