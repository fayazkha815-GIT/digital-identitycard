<?php
require __DIR__ . '/includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$student = $id > 0 ? getStudentById($id) : null;

if ($student === null) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Student Not Found</title></head><body><h1>Student record not found.</h1><p><a href="index.php">Go back to registration</a></p></body></html>';
    exit;
}

$status = $student['status'] ?? 'pending';
$canShowCard = $status === 'approved';
$profilePhoto = getPhotoUrl($student['photo_path']);
$qrPayload = "Student ID: {$student['student_id']}\nName: {$student['full_name']}\nCourse: {$student['course']}\nDepartment: {$student['department']}\nEmail: {$student['email']}\nPhone: {$student['phone']}";
$qrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($qrPayload);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Digital ID Card</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="brand">Student Digital Identity</div>
            <div class="nav-links">
                <a href="index.php">Register</a>
                <a href="admin.php">Admin</a>
            </div>
        </div>
    </nav>

    <main class="page">
        <div class="container">
            <?php if (!$canShowCard): ?>
                <div class="card" style="max-width: 640px; margin: 0 auto; text-align: center;">
                    <h1>Digital identity card is not available yet.</h1>
                    <p class="small-text">Current status: <strong><?= htmlspecialchars(ucfirst($status)) ?></strong></p>
                    <?php if ($status === 'pending'): ?>
                        <p>Your student information has been submitted successfully. Please wait for admin approval.</p>
                    <?php else: ?>
                        <p>Your application was rejected. Please contact the admin for more details.</p>
                    <?php endif; ?>
                    <a class="button-link" href="index.php" style="margin-top: 16px; padding: 12px 18px; border-radius: 10px;">Back to Home</a>
                </div>
            <?php else: ?>
                <div class="id-card">
                    <div class="id-card-header">
                        <div>
                            <div style="font-size: 0.82rem; letter-spacing: 0.12em; font-weight: 700; text-transform: uppercase; opacity: 0.8;">Student Verified</div>
                            <h2 style="margin: 8px 0 0; font-size: 1.7rem;">University Digital ID</h2>
                        </div>
                        <div style="font-size: 0.9rem; font-weight: 700; background: rgba(255,255,255,0.12); padding: 8px 12px; border-radius: 10px;">
                            <?= htmlspecialchars($student['status']) ?>
                        </div>
                    </div>

                    <div class="id-card-body">
                        <div class="id-profile">
                            <img class="id-photo" src="<?= htmlspecialchars($profilePhoto) ?>" alt="Student photo">

                            <div>
                                <div class="small-text">Student Name</div>
                                <h3 style="margin: 6px 0 0; font-size: 2rem;"><?= htmlspecialchars($student['full_name']) ?></h3>
                                <div class="small-text" style="margin-top: 12px;">Student ID: <strong><?= htmlspecialchars($student['student_id']) ?></strong></div>
                                <div class="small-text">Course: <strong><?= htmlspecialchars($student['course']) ?></strong></div>
                                <div class="small-text">Department: <strong><?= htmlspecialchars($student['department']) ?></strong></div>
                            </div>

                            <div class="qr-box">
                                <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR Code">
                                <div class="small-text" style="margin-top: 8px;">Scan to verify</div>
                            </div>
                        </div>

                        <div class="id-meta">
                            <div class="meta-item">
                                <span>Email</span>
                                <strong><?= htmlspecialchars($student['email']) ?></strong>
                            </div>
                            <div class="meta-item">
                                <span>Phone</span>
                                <strong><?= htmlspecialchars($student['phone'] ?: 'Not provided') ?></strong>
                            </div>
                            <div class="meta-item">
                                <span>Academic Year</span>
                                <strong><?= htmlspecialchars($student['year'] ?: 'Not provided') ?></strong>
                            </div>
                            <div class="meta-item">
                                <span>Date of Birth</span>
                                <strong><?= htmlspecialchars($student['dob'] ?: 'Not provided') ?></strong>
                            </div>
                            <div class="meta-item" style="grid-column: 1 / -1;">
                                <span>Address</span>
                                <strong><?= htmlspecialchars($student['address'] ?: 'Not provided') ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
