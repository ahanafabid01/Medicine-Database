<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointment Request</title>
  
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <style>
    /* General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    /* Body Styling */
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f2f5;
      background-image: url('images/mail.jpg');
      background-repeat: no-repeat;
      background-size: cover;
      background-position: center;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }

    /* Container */
    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.9); /* Semi-transparent background */
      padding: 20px 30px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      text-align: center;
      max-width: 400px; /* Limit width for larger screens */
      width: 90%; /* Responsive width for smaller screens */
    }

    /* Success Animation */
    .success-animation {
      display: inline-block;
      margin-bottom: 20px;
    }

    /* Checkmark */
    .checkmark {
      width: 60px;
      height: 60px;
      stroke-width: 2;
      stroke: white;
      fill: none;
      animation: drawCircle 0.6s ease forwards;
    }

    .checkmark-circle {
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      stroke-width: 2;
      stroke-miterlimit: 10;
      stroke: #28a745; /* Green for success */
      fill: none;
      animation: strokeCircle 0.6s ease forwards;
    }

    .checkmark-check {
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      stroke: #28a745; /* Green for success */
      animation: drawCheck 0.6s ease forwards;
    }

    /* Success Message */
    .success-message {
      font-size: 18px;
      color: #333;
      margin-bottom: 20px;
    }

    /* Button Styling */
    .login-link {
      display: inline-block;
      padding: 12px 20px;
      background: linear-gradient(145deg, #e0e0e0, #c0c0c0);
      color: #333;
      text-decoration: none;
      font-size: 16px;
      border-radius: 25px;
      border: 1px solid #b0b0b0;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .login-link:hover {
      background: linear-gradient(145deg, #c8c8c8, #a8a8a8);
      transform: translateY(-2px);
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }

    .login-link i {
      margin-left: 8px;
      transition: transform 0.3s ease;
    }

    .login-link:hover i {
      transform: translateX(5px);
    }

    /* Animations */
    @keyframes drawCircle {
      0% {
        stroke-dashoffset: 166;
      }
      100% {
        stroke-dashoffset: 0;
      }
    }

    @keyframes strokeCircle {
      0% {
        stroke-dashoffset: 166;
      }
      100% {
        stroke-dashoffset: 0;
      }
    }

    @keyframes drawCheck {
      0% {
        stroke-dashoffset: 48;
      }
      100% {
        stroke-dashoffset: 0;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="success-animation">
      <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
      </svg>
    </div>
    <p class="success-message">Email Verification Process Completed</p>
    <a href="login_form.php" class="login-link">Click Here to login 
      <i class="fas fa-arrow-right"></i>
    </a>
  </div>
</body>
</html>
