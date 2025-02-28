<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    // التحقق من أن البيانات غير فارغة
    if (!$email || !$password) {
        die("يرجى ملء جميع الحقول.");
    }

    // الحد من عدد محاولات تسجيل الدخول (5 محاولات كحد أقصى خلال 10 دقائق)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }

    if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt_time']) < 600) {
        die("تم تجاوز الحد الأقصى لمحاولات تسجيل الدخول. يرجى المحاولة لاحقًا.");
    }

    // البحث عن المستخدم في قاعدة البيانات
    $stmt = $conn->prepare("SELECT id, fullname, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // إعادة تعيين عدد المحاولات عند تسجيل الدخول بنجاح
        $_SESSION['login_attempts'] = 0;

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        header("Location: ../index.php");
        exit();
    } else {
        // زيادة عدد المحاولات الفاشلة
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();

        die("البريد الإلكتروني أو كلمة المرور غير صحيحة.");
    }
}
