<?php
session_start();
require 'db_connect.php'; // your database connection

// Validate class_id
if (!isset($_GET['class_id'])) {
    die("Class ID missing.");
}
$class_id = intval($_GET['class_id']);

// 1. GET CLASS CODE
$q1 = $conn->prepare("SELECT class_code, title, section FROM classes WHERE id = ?");
$q1->bind_param("i", $class_id);
$q1->execute();
$class = $q1->get_result()->fetch_assoc();

if (!$class) {
    die("Class not found.");
}

$class_code = $class['class_code'];

// 2. GET STUDENTS ENROLLED IN THIS CLASS
$q2 = $conn->prepare("
    SELECT u.id AS student_id, u.full_name
    FROM student_classes sc
    JOIN users u ON sc.student_id = u.id
    WHERE sc.class_code = ?
");
$q2->bind_param("s", $class_code);
$q2->execute();
$students = $q2->get_result();

// Prepare leaderboard array
$leaderboard = [];

while ($row = $students->fetch_assoc()) {
    $student_id = $row['student_id'];

    // 3. SUM TOTAL EXP OF EACH STUDENT
    $q3 = $conn->prepare("
        SELECT IFNULL(SUM(exp_gained), 0) AS total_exp
        FROM quiz_results
        WHERE student_id = ?
        AND class_code = ?
    ");
    $q3->bind_param("is", $student_id, $class_code);
    $q3->execute();
    $exp = $q3->get_result()->fetch_assoc()['total_exp'];

    $leaderboard[] = [
        'student_id' => $student_id,
        'name' => $row['full_name'],
        'exp' => $exp
    ];
}

// 4. SORT BY EXP DESC
usort($leaderboard, function ($a, $b) {
    return $b['exp'] - $a['exp'];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard - <?= htmlspecialchars($class['title']) ?></title>
    <link rel="stylesheet" href="leaderboard_view.css">
</head>
<body>

<div class="leaderboard-container">
    <h1><?= htmlspecialchars($class['title']) ?> - Leaderboard</h1>
    <h3>Section: <?= htmlspecialchars($class['section']) ?></h3>

    <table class="leaderboard-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student</th>
                <th>Total EXP</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rank = 1;
            foreach ($leaderboard as $entry): 
            ?>
            <tr>
                <td>#<?= $rank++ ?></td>
                <td><?= htmlspecialchars($entry['name']) ?></td>
                <td><?= $entry['exp'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
