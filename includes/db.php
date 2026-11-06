<?php
session_start();

function appRoot(): string
{
    return __DIR__ . '/..';
}

function initDatabase(): void
{
    $dbFile = appRoot() . '/database/digital_identity.sqlite';
    $directory = dirname($dbFile);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $db->exec("
        CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            student_id TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL,
            phone TEXT,
            course TEXT,
            year TEXT,
            department TEXT,
            dob TEXT,
            address TEXT,
            photo_path TEXT,
            status TEXT NOT NULL DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            approved_at DATETIME NULL
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT OR IGNORE INTO admins (username, password_hash) VALUES ('admin', '$adminHash')");
}

function getDb(): PDO
{
    static $db = null;

    if ($db === null) {
        initDatabase();
        $db = new PDO('sqlite:' . appRoot() . '/database/digital_identity.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    return $db;
}

function getAllStudents(): array
{
    $db = getDb();
    $query = $db->query("SELECT * FROM students ORDER BY created_at DESC");
    return $query->fetchAll();
}

function getStudentById(int $id): ?array
{
    $db = getDb();
    $stmt = $db->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $student = $stmt->fetch();
    return $student ?: null;
}

function getStudentByStudentId(string $studentId): ?array
{
    $db = getDb();
    $stmt = $db->prepare('SELECT * FROM students WHERE student_id = :student_id LIMIT 1');
    $stmt->execute([':student_id' => $studentId]);
    $student = $stmt->fetch();
    return $student ?: null;
}

function saveStudent(array $data, ?string $photoPath = null): bool
{
    $db = getDb();
    $stmt = $db->prepare(
        'INSERT INTO students (full_name, student_id, email, phone, course, year, department, dob, address, photo_path, status)
         VALUES (:full_name, :student_id, :email, :phone, :course, :year, :department, :dob, :address, :photo_path, :status)'
    );

    return $stmt->execute([
        ':full_name' => $data['full_name'],
        ':student_id' => $data['student_id'],
        ':email' => $data['email'],
        ':phone' => $data['phone'] ?? '',
        ':course' => $data['course'] ?? '',
        ':year' => $data['year'] ?? '',
        ':department' => $data['department'] ?? '',
        ':dob' => $data['dob'] ?? '',
        ':address' => $data['address'] ?? '',
        ':photo_path' => $photoPath,
        ':status' => 'pending',
    ]);
}

function updateStudentStatus(int $id, string $status): bool
{
    $db = getDb();
    $stmt = $db->prepare(
        'UPDATE students SET status = :status, approved_at = :approved_at WHERE id = :id'
    );

    return $stmt->execute([
        ':status' => $status,
        ':approved_at' => $status === 'approved' ? date('Y-m-d H:i:s') : null,
        ':id' => $id,
    ]);
}

function verifyAdminLogin(string $username, string $password): bool
{
    $db = getDb();
    $stmt = $db->prepare('SELECT password_hash FROM admins WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();

    return $admin !== false && password_verify($password, $admin['password_hash']);
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function sanitizeInput(string $value): string
{
    return trim(strip_tags($value));
}

function getPhotoUrl(?string $photoPath): string
{
    if (empty($photoPath)) {
        return 'https://ui-avatars.com/api/?name=Student&background=0d6efd&color=fff';
    }

    return '/digital-identity/' . ltrim($photoPath, '/');
}
