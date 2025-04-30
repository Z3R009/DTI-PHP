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
            } else {
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
    body {
        font-family: 'Inter', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-image: url('img/bg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    .login-container {
        width: 400px;
        padding: 40px;
        background-color: rgb(255, 255, 255);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
  
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

    .loader-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        display: none;
    }

    .absolute {
        position: absolute;
    }
    .inline-block {
        display: inline-block;
    }
    .loader {
        display: flex;
        margin: 0.25em 0;
    }
    .w-2 {
        width: 0.5em;
    }
    .dash {
        animation: dashArray 2s ease-in-out infinite,
            dashOffset 2s linear infinite;
    }
    .spin {
        animation: spinDashArray 2s ease-in-out infinite,
            spin 8s ease-in-out infinite,
            dashOffset 2s linear infinite;
        transform-origin: center;
    }
    @keyframes dashArray {
        0% {
            stroke-dasharray: 0 1 359 0;
        }
        50% {
            stroke-dasharray: 0 359 1 0;
        }
        100% {
            stroke-dasharray: 359 1 0 0;
        }
    }
    @keyframes spinDashArray {
        0% {
            stroke-dasharray: 270 90;
        }
        50% {
            stroke-dasharray: 0 360;
        }
        100% {
            stroke-dasharray: 270 90;
        }
    }
    @keyframes dashOffset {
        0% {
            stroke-dashoffset: 365;
        }
        100% {
            stroke-dashoffset: 5;
        }
    }
    @keyframes spin {
        0% {
            rotate: 0deg;
        }
        12.5%,
        25% {
            rotate: 270deg;
        }
        37.5%,
        50% {
            rotate: 540deg;
        }
        62.5%,
        75% {
            rotate: 810deg;
        }
        87.5%,
        100% {
            rotate: 1080deg;
        }
    }
    button:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .login-text {
        display: inline-block;
    }

    .login-text.hidden {
        display: none;
    }
</style>

<body>
    <div class="loader-container" id="loader-container">
        <div class="loader">
            <svg height="0" width="0" viewBox="0 0 64 64" class="absolute">
                <defs xmlns="http://www.w3.org/2000/svg">
                    <!-- Blue gradient for D -->
                    <linearGradient gradientUnits="userSpaceOnUse" y2="2" x2="0" y1="62" x1="0" id="blue-grad">
                        <stop stop-color="#0033CC" offset="0"></stop>
                        <stop stop-color="#66CCFF" offset="1"></stop>
                        <animateTransform repeatCount="indefinite" keySplines=".42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1" keyTimes="0; 0.125; 0.25; 0.375; 0.5; 0.625; 0.75; 0.875; 1" dur="8s" values="0 32 32;-270 32 32;-270 32 32;-540 32 32;-540 32 32;-810 32 32;-810 32 32;-1080 32 32;-1080 32 32" type="rotate" attributeName="gradientTransform"></animateTransform>
                    </linearGradient>
                    
                    <!-- Red gradient for T -->
                    <linearGradient gradientUnits="userSpaceOnUse" y2="0" x2="0" y1="64" x1="0" id="red-grad">
                        <stop stop-color="#FF0000" offset="0"></stop>
                        <stop stop-color="#FF9999" offset="1"></stop>
                        <animateTransform repeatCount="indefinite" keySplines=".42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1" keyTimes="0; 0.125; 0.25; 0.375; 0.5; 0.625; 0.75; 0.875; 1" dur="8s" values="0 32 32;-270 32 32;-270 32 32;-540 32 32;-540 32 32;-810 32 32;-810 32 32;-1080 32 32;-1080 32 32" type="rotate" attributeName="gradientTransform"></animateTransform>
                    </linearGradient>
                    
                    <!-- Yellow gradient for I -->
                    <linearGradient gradientUnits="userSpaceOnUse" y2="2" x2="0" y1="62" x1="0" id="yellow-grad">
                    <stop stop-color="#0033CC" offset="0"></stop>
                    <stop stop-color="#66CCFF" offset="1"></stop>
                        <animateTransform repeatCount="indefinite" keySplines=".42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1" keyTimes="0; 0.125; 0.25; 0.375; 0.5; 0.625; 0.75; 0.875; 1" dur="8s" values="0 32 32;-270 32 32;-270 32 32;-540 32 32;-540 32 32;-810 32 32;-810 32 32;-1080 32 32;-1080 32 32" type="rotate" attributeName="gradientTransform"></animateTransform>
                    </linearGradient>
                </defs>
            </svg>
            
            <!-- D Letter (Blue Gradient) -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 64 64" height="64" width="64" class="inline-block">
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="8" stroke="url(#blue-grad)" 
                    d="M 10,4 h 18 c 12,0 22,10 22,22 v 12 c 0,12 -10,22 -22,22 h -18 z" 
                    class="dash" id="d" pathLength="360"></path>
            </svg>
            
            <!-- T Letter (Red Gradient) -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" style="--rotation-duration:0ms; --rotation-direction:normal;" viewBox="0 0 64 64" height="64" width="64" class="inline-block">
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="10" stroke="url(#red-grad)" 
                    d="M 16,10 h 32 M 32,10 v 50" 
                    class="spin" id="t" pathLength="360"></path>
            </svg>
            
            <div class="w-2"></div>
            
            <!-- I Letter (Yellow Gradient) -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" style="--rotation-duration:0ms; --rotation-direction:normal;" viewBox="0 0 64 64" height="64" width="64" class="inline-block">
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="8" stroke="url(#yellow-grad)" 
                    d="M 32,4 v 56" 
                    class="dash" id="i" pathLength="360"></path>
            </svg>
        </div>
    </div>

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
        <form method="post" id="login-form">
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

            <button type="submit" id="login-button">
                <span class="login-text">Login</span>
            </button>
        </form>
    </div>
</body>
<script>
    const carousel = document.getElementById('logo-carousel');
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
    
    const secondaryLogo = document.querySelector('.logo-back img');
    secondaryLogo.onerror = function() {
        this.src = 'img/dti_logo.png'; 
        this.style.filter = 'hue-rotate(180deg)'; 
    };
    const loginForm = document.getElementById('login-form');
    const loginButton = document.getElementById('login-button');
    const loaderContainer = document.getElementById('loader-container');

    loginForm.addEventListener('submit', function(event) {
        loaderContainer.style.display = 'flex';
        loginButton.disabled = true;
    });
    window.addEventListener('load', function() {
        loaderContainer.style.display = 'none';
        loginButton.disabled = false;
    });
</script>
</html>