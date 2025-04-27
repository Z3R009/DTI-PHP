<?php
include 'DBConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on user type
            if ($_SESSION['role'] == 'Admin') {
                header('Location: admin/dashboard.php');
            } elseif ($_SESSION['role'] == 'Bookkeeper') {
                header('Location: book_keeper/dashboard.php');
            } elseif ($_SESSION['role'] == 'Budget Officer') {
                header('Location: budget_officer/dashboard.php');
            } elseif ($_SESSION['role'] == 'Chief Accountant') {
                header('Location: chief_accountant/dashboard.php');
            } elseif ($_SESSION['role'] == 'Guest') {
                header('Location: guest/dashboard.php');
            } elseif ($_SESSION['role'] == 'Cashier') {
                header('Location: cashier/dashboard.php');
            } {
                echo "<script>alert('Invalid user type value');</script>";
            }
            exit();
        } else {
            echo "<script>alert('Incorrect Password');</script>";
        }
    } else {
        echo "<script>alert('Incorrect Username');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- icon -->
    <link href="img/dti_logo.png" rel="icon">
    <link rel="stylesheet" href="css/index.css">
</head>
<style>
      .logo-container {
            perspective: 1000px;
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
        }

        .logo-carousel {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 1s;
        }

        .logo-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-face img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-back {
            transform: rotateY(180deg);
        }

</style>

<body>
<div class="login-container">
        <div class="logo-container" style="margin-bottom: 10px;">
            <div class="logo-carousel" id="logo-carousel">
                <div class="logo-face logo-front">
                    <img src="img/dti_logo.png" alt="DTI Logo">
                </div>
                <div class="logo-face logo-back">
                    <img src="img/Bagong-Pilipinas-Logo-e1717212149320-1920x1488.png" alt="Secondary Logo">
                </div>
            </div>
        </div>


        <p id="error-message" style="color: red; display: none;"></p>
        <form method="post" >
            <div class="form-group">
                <label for="username">Username</label>
                <ion-icon name="mail-outline"></ion-icon>
                <input type="text" id="username" name="username" placeholder="Enter your Username" autocomplete="off"
                    required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" id="password" name="password" placeholder="Enter your Password"
                    autocomplete="off" required>
            </div>

            <button type="submit">Login</button>
        </form>
</body>
<script>
        const carousel = document.getElementById('logo-carousel');
        const toggleBtn = document.getElementById('toggle-btn');
        const speedBtn = document.getElementById('speed-btn');
        
        let currentRotation = 0;
        let animationRunning = true;
        let animationSpeed = 2000;
        let animationInterval;

        function rotateLogo() {
            currentRotation += 180;
            carousel.style.transform = `rotateY(${currentRotation}deg)`;
        }
        function startAnimation() {
            animationInterval = setInterval(rotateLogo, animationSpeed);
        }
        startAnimation();
        toggleBtn.addEventListener('click', () => {
            if (animationRunning) {
                clearInterval(animationInterval);
                toggleBtn.textContent = 'Resume Animation';
                animationRunning = false;
            } else {
                startAnimation();
                toggleBtn.textContent = 'Pause Animation';
                animationRunning = true;
            }
        });
        speedBtn.addEventListener('click', () => {
            clearInterval(animationInterval);
            
            if (animationSpeed === 2000) {
                animationSpeed = 1000;
                speedBtn.textContent = 'Speed: Fast';
            } else if (animationSpeed === 1000) {
                animationSpeed = 500;
                speedBtn.textContent = 'Speed: Very Fast';
            } else {
                animationSpeed = 2000;
                speedBtn.textContent = 'Speed: Normal';
            }
            
            if (animationRunning) {
                startAnimation();
            }
        });
        const secondaryLogo = document.querySelector('.logo-back img');
        secondaryLogo.onerror = function() {
            this.src = 'img/dti_logo.png'; 
            this.style.filter = 'hue-rotate(180deg)'; 
        };
    </script>


</html>