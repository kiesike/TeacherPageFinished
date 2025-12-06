<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$mysqli = new mysqli("localhost","root","","quizmaker");
if ($mysqli->connect_error) die("Connection failed: ".$mysqli->connect_error);

$teacher_id = (int)$_SESSION['user_id'];
$teacher_name = $_SESSION['username'] ?? 'Teacher';

// fetch classes
$stmt = $mysqli->prepare("SELECT id, title, section, class_code, created_at 
                          FROM classes 
                          WHERE teacher_id = ? 
                          ORDER BY created_at DESC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
$classes = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Leaderboard - QuizQuest</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Base teacher style -->
<link rel="stylesheet" href="teacher.css">

<!-- New leaderboard-specific CSS -->
<link rel="stylesheet" href="leaderboard.css">
</head>

<body>

<canvas id="background-canvas"></canvas>

<!-- Sidebar -->
<div class="sidebar">
    <img src="assets/images/logo.png" class="logo-img" alt="QuizQuest">
    <div class="menu-wrapper">
        <div class="nav">
            <a class="nav-item" href="profile.php"><i data-lucide="user"></i> Profile (<?= htmlspecialchars($teacher_name) ?>)</a>
            <a class="nav-item" href="classes.php"><i data-lucide="layout"></i> Classes</a>
            <a class="nav-item active" href="leaderboard.php"><i data-lucide="award"></i> Leaderboard</a>
        </div>
    </div>
    <a class="logout" href="logout.php"><i data-lucide="log-out"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <div class="avatar-container">
        <span class="greeting">Leaderboard</span>
        <img src="https://i.imgur.com/oQEsWSV.png" class="freiren-avatar" alt="avatar">
    </div>

    <h2 class="quizzes-title mb-4">Select a Class</h2>

    <div class="row g-4">
        <?php foreach ($classes as $class): ?>
        <div class="col-md-4 col-sm-6">
            <div class="card subject-card h-100 leaderboard-card"
                 onclick="openLeaderboard(<?= (int)$class['id'] ?>)">
                 
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($class['title']) ?></h5>
                        <span class="badge class-code-badge">
                            Code: <?= htmlspecialchars($class['class_code']) ?>
                        </span>
                    </div>

                    <p class="card-text small mb-1">Section: <?= htmlspecialchars($class['section']) ?></p>

                    <div class="mt-auto text-end">
                        <small class="text-muted">
                            Created: <?= date('M d, Y', strtotime($class['created_at'])) ?>
                        </small>
                    </div>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lucide@0.259.0/dist/lucide.js"></script>
<script>
function openLeaderboard(classId) {
    window.location.href = `leaderboard_view.php?class_id=${classId}`;
}
lucide.replace();
</script>

<!-- Background animation JS -->
<script src="teacherscripts.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>
