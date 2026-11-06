<?php
require __DIR__ . '/includes/db.php';

$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '' && verifyAdminLogin($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        setFlash('success', 'Login successful.');
        header('Location: admin.php');
        exit;
    }

    setFlash('error', 'Invalid admin username or password.');
    header('Location: admin.php');
    exit;
}

if (isset($_GET['action']) && isAdminLoggedIn()) {
    $action = $_GET['action'];
    $id = (int)($_GET['id'] ?? 0);

    if ($action === 'approve' && $id > 0) {
        updateStudentStatus($id, 'approved');
        setFlash('success', 'Student approved successfully.');
    }

    if ($action === 'reject' && $id > 0) {
        updateStudentStatus($id, 'rejected');
        setFlash('success', 'Student rejected.');
    }

    if ($action === 'logout') {
        session_destroy();
        header('Location: admin.php');
        exit;
    }

    header('Location: admin.php');
    exit;
}

$students = isAdminLoggedIn() ? getAllStudents() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="brand">Admin Panel</div>
            <div class="nav-links">
                <a href="index.php">Register</a>
                <a href="admin.php">Dashboard</a>
                <?php if (isAdminLoggedIn()): ?>
                    <a href="admin.php?action=logout">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="page">
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert <?= htmlspecialchars($flash['type']) ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (!isAdminLoggedIn()): ?>
                <div class="card" style="max-width: 500px; margin: 0 auto;">
                    <h2>Admin Login</h2>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group full">
                                <label for="username">Username</label>
                                <input id="username" name="username" type="text" value="admin" required>
                            </div>
                            <div class="form-group full">
                                <label for="password">Password</label>
                                <input id="password" name="password" type="password" value="admin123" required>
                            </div>
                            <div class="form-group full">
                                <button type="submit" name="login">Login</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2>Student Applications</h2>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Student ID</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string)$student['id']) ?></td>
                                        <td><?= htmlspecialchars($student['full_name']) ?></td>
                                        <td><?= htmlspecialchars($student['student_id']) ?></td>
                                        <td><?= htmlspecialchars($student['course']) ?> / <?= htmlspecialchars($student['department']) ?></td>
                                        <td><span class="status <?= htmlspecialchars($student['status']) ?>"><?= htmlspecialchars($student['status']) ?></span></td>
                                        <td>
                                            <div class="action-group">
                                                <a class="button-link" href="card.php?id=<?= (int)$student['id'] ?>" target="_blank" style="padding: 10px 12px; border-radius: 10px;">View</a>
                                                <?php if ($student['status'] !== 'approved'): ?>
                                                    <a class="button-link secondary" href="admin.php?action=approve&id=<?= (int)$student['id'] ?>" style="padding: 10px 12px; border-radius: 10px;">Approve</a>
                                                <?php endif; ?>
                                                <?php if ($student['status'] !== 'rejected'): ?>
                                                    <a class="button-link danger" href="admin.php?action=reject&id=<?= (int)$student['id'] ?>" style="padding: 10px 12px; border-radius: 10px;">Reject</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
