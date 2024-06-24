<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "gege";
$DB_DATABASE = "doctor_website";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$DB_DATABASE;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $photo = $_FILES['photo']['tmp_name'];
        $photoName = $_FILES['photo']['name'];
        $department = $_POST['department'];
        $doctor_id = isset($_POST['doctor']) ? intval($_POST['doctor']) : null;
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            echo "Passwords do not match!";
            exit();
        }

        if (!is_dir('uploads')) {
            mkdir('uploads');
        }

        $targetPath = 'uploads/' . $photoName;
        if (!move_uploaded_file($photo, $targetPath)) {
            throw new Exception("Failed to move uploaded file to 'uploads/' directory.");
        }

        if ($department == 'doctor') {
            $specialty = $_POST['specialty'];
            $email = $_POST['email'];

            $stmt = $conn->prepare("INSERT INTO doctors (name, email, mobile, department, specialty, photo, password, confirm_password) VALUES (:name, :email, :mobile, :department, :specialty, :photo, :password, :confirm_password)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':mobile' => $phone,
                ':department' => $department,
                ':specialty' => $specialty,
                ':photo' => $targetPath,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':confirm_password' => password_hash($confirm_password, PASSWORD_DEFAULT)
            ]);
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, phone, photo, department, doctor, password, confirm_password) VALUES (:name, :phone, :photo, :department, :doctor, :password, :confirm_password)");
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':photo' => $targetPath,
                ':department' => $department,
                ':doctor' => $doctor_id,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':confirm_password' => password_hash($confirm_password, PASSWORD_DEFAULT)
            ]);
        }

        echo "<script>alert('Register Successfully!')</script>";
        header("Location: sign-in.php");
        exit();
    }

    $stmt = $conn->query("SELECT id, name FROM doctors");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . "<br>" . $e->getMessage();
}

$conn = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .form-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
            color: #333;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
        }

        .form-actions input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .form-actions input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        function toggleDoctorFields() {
            const department = document.getElementById('department').value;
            const doctorFields = document.getElementById('doctor-fields');
            const patientFields = document.getElementById('patient-fields');
            if (department === 'doctor') {
                doctorFields.style.display = 'block';
                patientFields.style.display = 'none';
            } else {
                doctorFields.style.display = 'none';
                patientFields.style.display = 'block';
            }
        }
    </script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-user-md"></i> Doctor Website
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="sign-in.php">Sign-In</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="form-container">
    <h2>Register</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>
        <div class="form-group">
            <label for="photo">Photo:</label>
            <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*" required>
        </div>
        <div class="form-group">
            <label for="department">Department:</label>
            <select class="form-control" id="department" name="department" onchange="toggleDoctorFields()" required>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
            </select>
        </div>
        <div id="doctor-fields" style="display: none;">
            <div class="form-group">
                <label for="specialty">Specialty:</label>
                <input type="text" class="form-control" id="specialty" name="specialty">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
        </div>
        <div class="form-group" id="patient-fields">
            <label for="doctor">Doctor (if patient):</label>
            <select class="form-control" id="doctor" name="doctor">
                <?php foreach ($doctors as $doctor): ?>
                    <option value="<?php echo htmlspecialchars($doctor['id']); ?>"><?php echo htmlspecialchars($doctor['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="Register">
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
