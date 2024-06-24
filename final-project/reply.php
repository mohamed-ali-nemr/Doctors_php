<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: sign-in.php");
    exit();
}

$user = $_SESSION['user'];
$message_id = $_GET['message_id'];
$sender_id = $_GET['sender_id'];

$servername = "localhost";
$username = "root";
$password = "gege";
$DB_DATABASE = "doctor_website";

$message = [];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$DB_DATABASE;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT message FROM messages WHERE id = :message_id");
    $stmt->execute([':message_id' => $message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $reply_message = $_POST['message'];

        $stmt = $conn->prepare("INSERT INTO messages (sender_id, sender_department, receiver_id, receiver_department, message) 
                                VALUES (:sender_id, :sender_department, :receiver_id, :receiver_department, :message)");
        $stmt->execute([
            ':sender_id' => $user['id'],
            ':sender_department' => 'doctor',
            ':receiver_id' => $sender_id,
            ':receiver_department' => 'patient',
            ':message' => $reply_message
        ]);

        echo "Reply sent successfully!";
    }
} catch (PDOException $e) {
    echo "Error: " . "<br>" . $e->getMessage();
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

     <!-- bootstrap -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    
    <title>Reply Message</title>
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

        .containerB textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .containerB button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .containerB button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .containerB button:active {
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
        <h1>Reply to Message</h1>
        <p><strong>Original Message:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
        <form method="post">
            <textarea name="message" required placeholder="Type your reply here..."></textarea>
            <button type="submit">Send Reply</button>
            <button type="button" onclick="history.back()">Go Back</button>
        </form>
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


</body>
</html>
