<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استقبال البيانات والتحقق من القيم
    $fullname = trim($_POST['fullname']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    // تحقق من أن جميع الحقول ممتلئة
    if (!$fullname || !$email || !$password) {
        die("يرجى ملء جميع الحقول.");
    }

    // التحقق من قوة كلمة المرور
    if (strlen($password) < 8 || !preg_match("/[0-9]/", $password) || !preg_match("/[A-Z]/", $password)) {
        die("كلمة المرور يجب أن تكون 8 أحرف على الأقل وتحتوي على رقم وحرف كبير.");
    }

    // تشفير كلمة المرور
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // التحقق مما إذا كان البريد الإلكتروني مستخدمًا من قبل
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("هذا البريد الإلكتروني مستخدم بالفعل.");
    }

    // إدخال البيانات في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$fullname, $email, $hashed_password])) {
        echo "تم إنشاء الحساب بنجاح!";
        header("Location: Login.php"); // إعادة توجيه المستخدم لتسجيل الدخول
        exit();
    } else {
        echo "حدث خطأ أثناء إنشاء الحساب.";
    }
}
