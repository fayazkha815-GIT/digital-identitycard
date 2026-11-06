<?php
require __DIR__ . '/includes/db.php';

$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $studentId = sanitizeInput($_POST['student_id'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $course = sanitizeInput($_POST['course'] ?? '');
    $year = sanitizeInput($_POST['year'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $dob = sanitizeInput($_POST['dob'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    if ($fullName === '' || $studentId === '' || $email === '' || $course === '' || $department === '') {
        setFlash('error', 'Please fill in all required fields.');
        header('Location: index.php');
        exit;
    }

    if (getStudentByStudentId($studentId) !== null) {
        setFlash('error', 'A student with this ID already exists.');
        header('Location: index.php');
        exit;
    }

    $photoPath = null;
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = appRoot() . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $_FILES['photo']['name']);
        $targetPath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photoPath = 'uploads/' . $fileName;
        }
    }

    $saved = saveStudent([
        'full_name' => $fullName,
        'student_id' => $studentId,
        'email' => $email,
        'phone' => $phone,
        'course' => $course,
        'year' => $year,
        'department' => $department,
        'dob' => $dob,
        'address' => $address,
    ], $photoPath);

    if ($saved) {
        setFlash('success', 'Your registration has been submitted successfully. Please wait for admin approval.');
    } else {
        setFlash('error', 'Registration failed. Please try again.');
    }

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Identity Card</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="brand">Digital Identity Card</div>
            <div class="nav-links">
                <a href="index.php">Register</a>
                <a href="admin.php">Admin Panel</a>
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

            <div class="grid">
                <section class="card">
                    <h1>Student Registration</h1>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input id="full_name" name="full_name" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="student_id">Student ID</label>
                                <input id="student_id" name="student_id" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input id="email" name="email" type="email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input id="phone" name="phone" type="tel">
                            </div>
                            <div class="form-group">
                                <label for="course">Course</label>
                                <input id="course" name="course" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="year">Academic Year</label>
                                <select id="year" name="year">
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input id="department" name="department" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="dob">Date of Birth</label>
                                <input id="dob" name="dob" type="date">
                            </div>
                            <div class="form-group full">
                                <label for="address">Address</label>
                                <textarea id="address" name="address"></textarea>
                            </div>
                            <div class="form-group full">
                                <label for="photo">Student Photo</label>
                                <input id="photo" name="photo" type="file" accept="image/*">
                            </div>
                            <div class="form-group full">
                                <button type="submit" name="register">Register Student</button>
                            </div>
                        </div>
                    </form>
                </section>

                <aside class="info-box">
                    <h2>How it works</h2>
                    <ul class="info-list">
                        <li>1. Student fills out the registration form.</li>
                        <li>2. Admin reviews all student submissions.</li>
                        <li>3. Admin approves the student.</li>
                        <li>4. A digital ID card is automatically generated with QR code.</li>
                        <li>5. Anyone can scan the QR code to view the student's information.</li>
                    </ul>
                </aside>
            </div>
        </div>
    </main>
</body>
</html>
