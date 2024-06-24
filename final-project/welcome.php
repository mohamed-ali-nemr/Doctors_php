<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: sign-in.php");
    exit();
}

$user = $_SESSION['user'];
$messages = [];

$servername = "localhost";
$username = "root";
$password = "gege";
$DB_DATABASE = "doctor_website";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$DB_DATABASE;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch messages based on the user's department
    if ($user['department'] == 'patient') {
        // Fetch messages sent to the patient (replies from doctors)
        $stmt = $conn->prepare("SELECT messages.id, messages.message, doctors.name AS sender_name, doctors.id AS sender_id 
                                FROM messages 
                                JOIN doctors ON messages.sender_id = doctors.id 
                                WHERE messages.receiver_id = :user_id AND messages.receiver_department = 'patient'");
        $stmt->execute([':user_id' => $user['id']]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch the doctor's name for the patient
        $doctor_stmt = $conn->prepare("SELECT name FROM doctors WHERE id = :doctor_id");
        $doctor_stmt->execute([':doctor_id' => $user['doctor']]);
        $doctor = $doctor_stmt->fetch(PDO::FETCH_ASSOC);
    } else if ($user['department'] == 'doctor') {
        // Fetch messages sent to the doctor (from patients)
        $stmt = $conn->prepare("SELECT messages.id, messages.message, users.name AS sender_name, users.id AS sender_id 
                                FROM messages 
                                JOIN users ON messages.sender_id = users.id 
                                WHERE messages.receiver_id = :user_id AND messages.receiver_department = 'doctor'");
        $stmt->execute([':user_id' => $user['id']]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch the doctor's patients
        $patients_stmt = $conn->prepare("SELECT id, name, phone FROM users WHERE doctor = :doctor_id AND department = 'patient'");
        $patients_stmt->execute([':doctor_id' => $user['id']]);
        $patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Handling form submission for adding/updating/deleting patients
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_patient'])) {
            $patient_name = $_POST['patient_name'];
            $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : ''; // Assuming phone_number is sent from the form
            $add_stmt = $conn->prepare("INSERT INTO users (name, doctor, department, phone) VALUES (:name, :doctor, 'patient', :phone)");
            $add_stmt->execute([':name' => $patient_name, ':doctor' => $user['id'], ':phone' => $phone_number]);

        } elseif (isset($_POST['update_patient'])) {
            $patient_id = $_POST['patient_id'];
            $patient_name = $_POST['patient_name'];
            $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : ''; // Assuming phone_number is sent from the form
            $update_stmt = $conn->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id AND doctor = :doctor");
            $update_stmt->execute([':name' => $patient_name, ':phone' => $phone_number, ':id' => $patient_id, ':doctor' => $user['id']]);
        } elseif (isset($_POST['delete_patient'])) {
            $patient_id = $_POST['patient_id'];
            $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = :id AND doctor = :doctor");
            $delete_stmt->execute([':id' => $patient_id, ':doctor' => $user['id']]);
        }
        // Redirect to avoid resubmission on page refresh
        header("Location: welcome.php");
        exit();
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .containerB {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .containerB h1 {
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
        }

        .containerB p {
            margin-bottom: 15px;
            font-size: 16px;
            color: #555;
        }

        .containerB img {
            display: block;
            margin: auto;
            width: 50%;
            border-radius: 10px;
        }

        .containerB a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .containerB a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .containerB a:active {
            transform: translateY(1px);
        }

        .containerB ul {
            list-style: none;
            padding: 0;
        }

        .containerB ul li {
            background: #f8f9fa;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: left;
        }

        .reply-button {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .reply-button:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .reply-button:active {
            transform: translateY(1px);
        }
    </style>
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
                       <a class="nav-link" href="home.php">Home</a>
                   </li>
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

    <div class="containerB">
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>'s Photo">
        <p>Department: <?php echo htmlspecialchars($user['department']); ?></p>
        <?php if (isset($user['specialty'])): ?>
            <p>Specialty: <?php echo htmlspecialchars($user['specialty']); ?></p>
        <?php endif; ?>
        <p>Phone: <?php echo htmlspecialchars($user['mobile'] ?? $user['phone']); ?></p>

        <?php if ($user['department'] == 'patient' && isset($doctor)): ?>
            <p>Doctor: <?php echo htmlspecialchars($doctor['name']); ?></p>
        <?php endif; ?>

        <?php if (!empty($messages)): ?>
            <h3>Messages</h3>
            <ul>
                <?php foreach ($messages as $message): ?>
                    <li><strong><?php echo htmlspecialchars($message['sender_name']); ?>:</strong> <?php echo htmlspecialchars($message['message']); ?>
                        <?php if ($user['department'] == 'doctor'): ?>
                            <a href="reply.php?message_id=<?php echo $message['id']; ?>&sender_id=<?php echo $message['sender_id']; ?>" class="reply-button">Reply</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($user['department'] == 'patient'): ?>
            <p><a href="message.php" class="register-button">Send Message to Doctor</a></p>
        <?php endif; ?>

        <?php if ($user['department'] == 'doctor'): ?>
            <h3>Manage Patients</h3>
            <form method="post" action="">
                <div class="form-group">
                    <label for="patient_name">Patient Name</label>
                    <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number">
                </div>
                <button type="submit" class="btn btn-primary" name="add_patient">Add Patient</button>
            </form>

            <?php if (!empty($patients)): ?>
                <h4 class="mt-4">Update/Delete Patients</h4>
                <ul>
                    <?php foreach ($patients as $patient): ?>
                        <li>
                            <?php echo htmlspecialchars($patient['name']); ?>
                            <form method="post" action="">
                                <input type="hidden" name="patient_id" value="<?php echo $patient['id']; ?>">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="patient_name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($patient['phone']); ?>">
                                </div>
                                <button type="submit" class="btn btn-sm btn-warning mt-1" name="update_patient">Update</button>
                                <button type="submit" class="btn btn-sm btn-danger mt-1" name="delete_patient">Delete</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>

        <a href="logout.php">Logout</a>
    </div>

    <footer class="footer mt-auto py-3 bg-light shadow-sm">
     <div class="container text-center">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <h5>About Us</h5>
                <p class="text-muted">Providing quality healthcare with a team of experienced doctors.</p>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <h5>Contact</h5>
                <p class="text-muted">Email: info@doctorwebsite.com</p>
                <p class="text-muted">Phone: +1 (123) 456-7890</p>
            </div>
            <div class="col-md-4">
                <h5>Follow Us</h5>
                <a href="#" class="text-muted mr-3"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-muted mr-3"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-muted mr-3"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-muted"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
        <hr>
        <span class="text-muted">&copy; 2024 Doctor Website. All rights reserved.</span>
     </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>
</body>
</html>
