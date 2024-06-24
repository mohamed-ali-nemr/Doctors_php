<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <title>Our Doctors</title>
    <style>
        .doctor-card {
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .doctor-card:hover {
            transform: scale(1.05);
        }
        .doctor-card img {
            height: 300px;
            object-fit: cover;
        }
        .doctor-card .card-body {
            padding: 15px;
        }
        .doctor-card .card-title {
            margin-bottom: 10px;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <script>
        function downloadAsImage(doctorId) {
            var table = document.getElementById('patients-table-' + doctorId);
            if (!table) {
                console.error('Table element not found.');
                return;
            }

            html2canvas(table, {
                backgroundColor: 'white',
                onrendered: function(canvas) {
                    var imgData = canvas.toDataURL('image/png');
                    var link = document.createElement('a');
                    link.download = 'patients.png';
                    link.href = imgData;
                    link.click();
                }
            });
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

<div class="container">
    <div class="jumbotron mt-4">
        <h1 class="display-4">Our Doctors</h1>
        <p class="lead">Meet the doctors in our team and their assigned patients.</p>
    </div>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "gege";
    $DB_DATABASE = "doctor_website";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$DB_DATABASE;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch all doctors and their patients along with doctor photos
        $stmt = $conn->prepare("SELECT doctors.id AS doctor_id, doctors.name AS doctor_name, doctors.photo AS doctor_photo, users.name AS patient_name 
                                FROM doctors 
                                LEFT JOIN users ON doctors.id = users.doctor AND users.department = 'patient' 
                                ORDER BY doctors.name, users.name");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $doctors = [];
        foreach ($result as $row) {
            if (!isset($doctors[$row['doctor_name']])) {
                $doctors[$row['doctor_name']] = [
                    'id' => $row['doctor_id'],
                    'photo' => $row['doctor_photo'],
                    'patients' => []
                ];
            }
            if ($row['patient_name']) {
                $doctors[$row['doctor_name']]['patients'][] = $row['patient_name'];
            }
        }

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
    ?>

    <div class="row">
        <?php foreach ($doctors as $doctor_name => $doctor_data): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card doctor-card">
                    <?php if (isset($doctor_data['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($doctor_data['photo']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($doctor_name); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title">Dr.<?php echo htmlspecialchars($doctor_name); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">Patients:</h6>
                        <div id="patients-table-<?php echo $doctor_data['id']; ?>">
                            <?php if (!empty($doctor_data['patients'])): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($doctor_data['patients'] as $patient_name): ?>
                                        <li><i class="fas fa-user"></i> <?php echo htmlspecialchars($patient_name); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No patients assigned.</p>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary mt-2" onclick="downloadAsImage(<?php echo $doctor_data['id']; ?>)">Download as Image</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
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
