<?php
session_start();
$base="/web/galeriseramikmbpg/";

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

include '../../db.php';
include '../timeout.php';

function getQnaStatus($answer) {
    return trim($answer) !== '' ? 'Dijawab' : 'Belum Dijawab';
}

$flashMessage = '';
$flashClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    $qnaId = isset($_POST['qna_id']) && is_numeric($_POST['qna_id']) ? (int) $_POST['qna_id'] : 0;
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');

    if ($question === '') {
        header('Location: qna.php?error=empty_question');
        exit;
    }

    $questionEscaped = mysqli_real_escape_string($conn, $question);
    $answerEscaped = mysqli_real_escape_string($conn, $answer);
    $statusEscaped = mysqli_real_escape_string($conn, getQnaStatus($answer));

    if ($formAction === 'save') {
        // Reuse text cleaner routine locally for safety references
        $sentenceClean = strtolower($question);
        $sentenceClean = preg_replace('/[^\w\s]/u', '', $sentenceClean);
        $stop_words = ['saya', 'awak', 'kamu', 'nak', 'ke', 'di', 'dari', 'yang', 'ini', 'itu', 'dan', 'atau', 'adakah', 'apa', 'apakah', 'bagaimana', 'macam', 'mana', 'bila', 'siapa', 'mengapa', 'kenapa', 'ada','galeri', 'Galeri'];
        $words = explode(' ', $sentenceClean);
        $filtered = array_diff($words, $stop_words);
        $keywordString = implode(', ', array_filter(array_map('trim', $filtered)));

        $keywordsEscaped = mysqli_real_escape_string($conn, strtolower($_POST['keywords'] ?? '')); 
        
        // --- 1. MODIFIED PROCESSING PART: Extract & escape admin phrases field ---
        $phrasesEscaped = mysqli_real_escape_string($conn, strtolower($_POST['phrases'] ?? ''));

        if ($qnaId > 0) {
            // Updated to save phrases column
            $updateQuery = "UPDATE qna SET question = '$questionEscaped', answer = '$answerEscaped', keywords = '$keywordsEscaped', phrases = '$phrasesEscaped', status = '$statusEscaped' WHERE qna_id = $qnaId";
            if (mysqli_query($conn, $updateQuery)) {
                header('Location: qna.php?success=qna_updated');
                exit;
            }
        } else {
            // Insert tracking to handle phrases column
            $insertQuery = "INSERT INTO qna (question, answer, keywords, phrases, status) VALUES ('$questionEscaped', '$answerEscaped', '$keywordsEscaped', '$phrasesEscaped', '$statusEscaped')";
            if (mysqli_query($conn, $insertQuery)) {
                header('Location: qna.php?success=qna_added');
                exit;
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $deleteId = (int) $_GET['id'];
    if ($deleteId > 0) {
        $deleteQuery = "DELETE FROM qna WHERE qna_id = $deleteId";
        if (mysqli_query($conn, $deleteQuery)) {
            header('Location: qna.php?success=qna_deleted');
            exit;
        }
    }
    header('Location: qna.php?error=invalid_id');
    exit;
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM qna");
$totalRules = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = max(1, (int) ceil($totalRules / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

$qnaQuery = "SELECT * FROM qna ORDER BY qna_id DESC LIMIT $limit OFFSET $offset";
$qnaResult = mysqli_query($conn, $qnaQuery);

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'qna_updated': $flashMessage = 'QnA berjaya dikemaskini.'; $flashClass = 'success-alert'; break;
        case 'qna_added': $flashMessage = 'QnA baru berjaya disimpan.'; $flashClass = 'success-alert'; break;
        case 'qna_deleted': $flashMessage = 'QnA berjaya dipadam.'; $flashClass = 'success-alert'; break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_id': $flashMessage = 'ID QnA tidak sah.'; $flashClass = 'error-alert'; break;
        case 'empty_question': $flashMessage = 'Sila masukkan pertanyaan.'; $flashClass = 'error-alert'; break;
        default: $flashMessage = 'Terdapat ralat. Sila cuba lagi.'; $flashClass = 'error-alert'; break;
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MBPG</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght=400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/tempahan.css">
  <link rel="stylesheet" href="../css/rule.css">
  <link rel="icon" href="<?= $base ?>assets/images/logogaleri.png" type="image/png">
</head>
<body>
<div class="overlay"></div>

<div class="admin-layout">
    <?php include '../sidebar.php'; ?>
    <main class="main">
    <header class="topbar">
        <button id="menu-toggle" class="menu-toggle"><i class="fa-solid fa-bars"></i></button>
        <div>
            <h1>Bahagian Pertanyaan Pengunjung</h1>
            <p>Pengaturan QnA</p>
        </div>
    </header>

    <?php if ($flashMessage): ?>
      <div class="alert <?= $flashClass ?>"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <section class="booking-panel">
        <div class="rule-actions">
          <button type="button" class="red-btn" id="openRuleModal"><i class="fa-solid fa-plus"></i> Tambah QnA</button>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Pertanyaan</th>
                        <th>Kata Kunci (Auto)</th>
                        <th>Kombinasi Frasa (Admin)</th>
                        <th>Jawapan</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1; ?>
                    <?php while ($qna = mysqli_fetch_assoc($qnaResult)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($qna['question']) ?></td>
                        <td><?= htmlspecialchars($qna['keywords']) ?></td>
                        <td><?= htmlspecialchars($qna['phrases'] ?? '') ?></td>
                        <td style="text-align: left;"><?= htmlspecialchars($qna['answer']) ?></td>
                        <td><?= htmlspecialchars($qna['status']) ?></td>
                        <td>
                            <button type="button" class="edit-qna-btn"
                                style="background: none; border: none; color: #1565c0; cursor: pointer; font-family: inherit; font-weight: bold;"
                                data-id="<?= $qna['qna_id'] ?>"
                                data-question="<?= htmlspecialchars($qna['question']) ?>"
                                data-keywords="<?= htmlspecialchars($qna['keywords']) ?>"
                                data-phrases="<?= htmlspecialchars($qna['phrases'] ?? '') ?>"
                                data-answer="<?= htmlspecialchars($qna['answer']) ?>"
                            >Edit</button>
                        </td>
                        <td>
                            <button class="delete-qna-btn" onclick="deleteQna(<?= $qna['qna_id'] ?>)"
                                style="background: none; border: none; color: #c62828; cursor: pointer; font:inherit; font-weight: bold;">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="page-btn <?= $page == $i ? 'active-page' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </section>

<div class="popup-modal" id="qnaModal">
  <div class="popup-card">
    <h2>Tambah / Edit QnA</h2>

    <form id="qnaForm" action="qna.php" method="POST">
      <input type="hidden" name="form_action" id="formAction" value="save">
      <input type="hidden" name="qna_id" id="qna_id" value="">
      
      <label>Pertanyaan</label>
      <input type="text" id="qnaQuestion" name="question" required>

      <label>Kata Kunci (Asingkan dengan koma)</label>
      <input type="text" id="qnaKeywords" name="keywords" required>

      <label>Kombinasi Frasa Admin (Asingkan dengan koma - cth: had masa tempah, lewat booking)</label>
      <input type="text" id="qnaPhrases" name="phrases">

      <label>Jawapan</label>
      <textarea id="qnaAnswer" name="answer"></textarea>

      <div class="popup-actions">
        <button type="button" class="cancel-btn close-popup">Batal</button>
        <button type="submit" class="save-btn">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="/web/galeriseramikmbpg/admin/js/sidebar.js"></script>

<script>
const openRuleModal = document.getElementById('openRuleModal');
const qnaModal = document.getElementById('qnaModal');
const qnaForm = document.getElementById('qnaForm');

openRuleModal.addEventListener('click', () => {
  qnaForm.action = 'qna.php';
  document.getElementById('formAction').value = 'save';
  document.getElementById('qna_id').value = '';
  document.getElementById('qnaQuestion').value = '';
  document.getElementById('qnaKeywords').value = '';
  // --- 4. MODIFIED SCRIPT PART: Clear phrases field on fresh add entry ---
  document.getElementById('qnaPhrases').value = '';
  document.getElementById('qnaAnswer').value = '';
  qnaModal.classList.add('active');
});

// edit buttons
document.querySelectorAll('.edit-qna-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    document.getElementById('formAction').value = 'save';
    document.getElementById('qna_id').value = id;
    document.getElementById('qnaQuestion').value = btn.dataset.question || '';
    document.getElementById('qnaKeywords').value = btn.dataset.keywords || '';
    // --- 4. MODIFIED SCRIPT PART: Populate phrase data tracking on edit click ---
    document.getElementById('qnaPhrases').value = btn.dataset.phrases || '';
    document.getElementById('qnaAnswer').value = btn.dataset.answer || '';
    qnaModal.classList.add('active');
  });
});

function deleteQna(id) {
    if (confirm("Padam soalan ini?")) {
        window.location = "qna.php?action=delete&id=" + id;
    }
}

document.querySelectorAll('.close-popup').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.popup-modal').forEach(modal => {
      modal.classList.remove('active');
    });
  });
});

document.querySelectorAll('.popup-modal').forEach(modal => {
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.remove('active');
    }
  });
});
</script>
</body>
</html>