<?php
session_start();

// Daftar pertanyaan dan jawaban yang benar
$questions = [
    [
        "question" => "Danau terbesar di dunia adalah…",
        "choices" => ["Danau Toba", "Danau Baikal", "Danau Kaspia", "Danau Victoria"],
        "correctAnswer" => 0
    ],
    [
        "question" => "Apa julukan terkenal dari negara Korea Selatan?",
        "choices" => ["Negeri Tirai Bambu", "Negeri Gingseng", "Zamrud Khatulistiwa", "Negeri Kincir Angin"],
        "correctAnswer" => 1
    ],
    [
        "question" => "Bumi berputar pada porosnya pada kemiringan… derajat",
        "choices" => ["23,5", "25,5", "24,5", "26,5"],
        "correctAnswer" => 0
    ],
    [
        "question" => "Apa nama mata uang dari negara Thailand?",
        "choices" => ["Rupiah", "Won", "Dollar", "Baht"],
        "correctAnswer" => 3
    ],
    [
        "question" => "Disebut apakah binatang yang dapat hidup di dua alam, yaitu darat dan laut?",
        "choices" => ["Amfibi", "Mamalia", "Reptil", "Pisces"],
        "correctAnswer" => 0
    ],
    [
        "question" => "Planet terbesar di tata surya kita adalah...",
        "choices" => ["Mars", "Bumi", "Jupiter", "Venus"],
        "correctAnswer" => 2
    ],
    [
        "question" => "Penemu telepon adalah...",
        "choices" => ["Thomas Edison", "Alexander Graham Bell", "Nikola Tesla", "Albert Einstein"],
        "correctAnswer" => 1
    ],
    [
        "question" => "Apakah nama ilmiah dari air?",
        "choices" => ["NaCl", "CO2", "H2SO4", "H2O"],
        "correctAnswer" => 3
    ],  
    [
        "question" => "Alat yang digunakan untuk mengukur intensitas gempa disebut?",
        "choices" => ["Barometer", "Termometer", "Sismograf", "Anemometer"],
        "correctAnswer" => 2  
    ],  
    [
        "question" => "Apa yang menjadi penyebab terjadinya musim di Bumi?",
        "choices" => ["Gerakan rotasi Bumi", "Gerakan revolusi Bumi", "Keberadaan bulan", "Pergeseran benua"],
        "correctAnswer" => 1
    ],
];

// Reset kuis jika pengguna ingin memulai ulang
if (isset($_GET['reset'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Cek apakah data pengguna sudah diisi
if (!isset($_SESSION['name']) && !isset($_SESSION['nim'])) {
    // Proses data pengguna jika form dikirim
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_SESSION['name'] = $_POST['name'];
        $_SESSION['nim'] = $_POST['nim'];
        // Inisialisasi sesi untuk kuis
        $_SESSION['questionOrder'] = array_keys($questions); // Buat urutan pertanyaan
        shuffle($_SESSION['questionOrder']); // Acak urutan pertanyaan
        $_SESSION['currentQuestion'] = 0; // Pertanyaan saat ini
        $_SESSION['correctAnswers'] = 0; // Jawaban yang benar
        $_SESSION['userAnswers'] = []; // Jawaban pengguna
        $_SESSION['originalOrder'] = array_keys($questions); // Menyimpan urutan asli untuk kotak status
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_SESSION['name']) && isset($_SESSION['nim'])) {
    // Proses kuis jika pengguna sudah mengisi data
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $currentQuestionIndex = $_SESSION['questionOrder'][$_SESSION['currentQuestion']];
        $userAnswer = isset($_POST['answer']) ? intval($_POST['answer']) : -1;

        // Jika jawaban dipilih, simpan jawaban pengguna
        if ($userAnswer != -1) {
            $_SESSION['userAnswers'][$_SESSION['currentQuestion']] = $userAnswer;

            // Cek apakah jawaban benar
            if ($userAnswer == $questions[$currentQuestionIndex]['correctAnswer']) {
                $_SESSION['correctAnswers']++;
            }
        }

        // Jika tombol 'Sebelumnya' ditekan
        if (isset($_POST['previous'])) {
            $_SESSION['currentQuestion'] = max(0, $_SESSION['currentQuestion'] - 1);
        } elseif (isset($_POST['next'])) {
            // Pindah ke pertanyaan berikutnya
            $_SESSION['currentQuestion']++;
        } elseif (isset($_POST['submit'])) {
            // Saat tombol submit ditekan, cek apakah semua soal sudah dijawab
            $allAnswered = true;
            foreach ($_SESSION['questionOrder'] as $index) {
                if (!isset($_SESSION['userAnswers'][$index]) || $_SESSION['userAnswers'][$index] == -1) {
                    $allAnswered = false;
                    break;
                }
            }

            // Jika semua soal telah dijawab, arahkan ke halaman hasil
            if ($allAnswered) {
                $totalQuestions = count($questions);
                $correctAnswers = $_SESSION['correctAnswers'];
                // Hitung skor
                $score = 0;
                if ($correctAnswers > 0) {
                    $score = ($correctAnswers / $totalQuestions) * 100; // Skor dihitung berdasarkan persentase
                }
                $_SESSION['score'] = round($score); // Simpan skor di sesi
                header("Location: " . $_SERVER['PHP_SELF'] . "?results=true");
                exit();
            } else {
                // Jika ada soal yang belum dijawab, tampilkan notifikasi
                $notAnsweredWarning = "Anda belum menjawab semua pertanyaan.";
            }
        }

        // Reload halaman untuk pertanyaan berikutnya
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Jika kotak status ditekan, arahkan ke pertanyaan yang sesuai
    if (isset($_GET['question'])) {
        $questionIndex = intval($_GET['question']);
        if ($questionIndex >= 0 && $questionIndex < count($_SESSION['questionOrder'])) {
            $_SESSION['currentQuestion'] = $questionIndex;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Pengetahuan Umum</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .question {
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        label {
            font-size: 16px;
            margin: 5px 0;
            display: block;
            padding: 10px;
            background-color: #f1f1f1;
            border: 1px solid #ced4da;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        label:hover {
            background-color: #e9ecef;
        }

        input[type="radio"] {
            margin-right: 10px;
        }

        button {
            display: block;
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .result {
            margin-top: 20px;
            font-size: 18px;
            text-align: center;
        }

        .restart-button {
            background-color: #dc3545;
            margin-top: 10px;
        }

        .restart-button:hover {
            background-color: #c82333;
        }

        .summary {
            font-size: 16px;
            margin-top: 20px;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .summary p {
            margin: 5px 0;
        }

        .status-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(30px, 1fr));
            gap: 5px;
            margin-bottom: 20px;
        }

        .status-box {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            border-radius: 5px;
            cursor: pointer; /* Menambahkan cursor pointer untuk kotak status */
        }

        .not-answered {
            background-color: #dc3545; /* Merah untuk tidak dijawab */
        }

        .answered {
            background-color: #28a745; /* Hijau untuk sudah dijawab */
        }

        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            border: 1px solid #f5c6cb;
        }

        .input-container {
            margin-bottom: 20px;
        }

        .input-container label {
            margin-bottom: 5px;
            display: block;
        }

        .input-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quiz Pengetahuan Umum</h1>

        <?php if (!isset($_SESSION['name']) && !isset($_SESSION['nim'])): ?>
            <form method="post">
                <div class="input-container">
                    <label for="name">Nama:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="input-container">
                    <label for="nim">NIM:</label>
                    <input type="text" id="nim" name="nim" required>
                </div>
                <button type="submit">Mulai Kuis</button>
            </form>
        <?php elseif (isset($_GET['results'])): ?>
            <?php
            $score = $_SESSION['score'];
            $correctAnswers = $_SESSION['correctAnswers'];
            $totalQuestions = count($questions);
            ?>
            <div class="summary">
                <p>Nilai Anda: <?= $score; ?> dari 100</p>
                <p>Jawaban Benar: <?= $correctAnswers; ?> dari <?= $totalQuestions; ?></p>
                <p><?= $score == 100 ? "Luar Biasa! Semua jawaban benar!" : ($score == 0 ? "Sayang sekali! Semua jawaban salah!" : "Terima kasih telah mengikuti kuis."); ?></p>
                <form method="get">
                    <button type="submit" name="reset" class="restart-button">Mulai Ulang Kuis</button>
                </form>
            </div>
        <?php else: ?>
            <div class="status-boxes">
                <?php foreach ($_SESSION['originalOrder'] as $index): ?>
                    <a href="?question=<?= $index; ?>">
                        <div id="status-box-<?= $index; ?>" class="status-box <?= isset($_SESSION['userAnswers'][$index]) ? 'answered' : 'not-answered'; ?>">
                            <?= $index + 1; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="question"><?= $questions[$_SESSION['questionOrder'][$_SESSION['currentQuestion']]]['question']; ?></div>
            <form method="post">
                <div>
                    <?php foreach ($questions[$_SESSION['questionOrder'][$_SESSION['currentQuestion']]]['choices'] as $choiceIndex => $choice): ?>
                        <label>
                            <input type="radio" name="answer" value="<?= $choiceIndex; ?>" <?= isset($_SESSION['userAnswers'][$_SESSION['currentQuestion']]) && $_SESSION['userAnswers'][$_SESSION['currentQuestion']] == $choiceIndex ? 'checked' : ''; ?>>
                            <?= $choice; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <?php if ($_SESSION['currentQuestion'] < count($questions) - 1): ?>
                    <button type="submit" name="next">Selanjutnya</button>
                <?php else: ?>
                    <button type="submit" name="submit">Submit</button>
                <?php endif; ?>

                <?php if ($_SESSION['currentQuestion'] > 0): ?>
                    <button type="submit" name="previous">Sebelumnya</button>
                <?php endif; ?>
            </form>

            <?php if (isset($notAnsweredWarning)): ?>
                <div class="alert"><?= $notAnsweredWarning; ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>