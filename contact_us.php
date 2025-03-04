<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES ('$name', '$email', '$phone', '$subject', '$message')";

    if ($conn->query($sql) === TRUE) {
        $successMessage = "Message sent successfully!";
    } else {
        $errorMessage = "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us - Medical Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" /> 
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700&display=swap');

        :root {
            --main-color: #00ffaa;
            --black: #13131a;
            --bg: #010103;
            --border: .1rem solid rgba(255, 255, 255, .3);
        }

        * {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none; 
            border: none;
            text-decoration: none;
            text-transform: capitalize;
            transition: .2s linear;
        }

        html {
            font-size: 62.5%;
            overflow-x: hidden;
            scroll-padding-top: 9rem;
            scroll-behavior: smooth;
        }

        body {
            background: var(--bg);
            color: #fff;
            padding-top: 9.5rem;
        }

        .header {
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 7%;
            border-bottom: var(--border);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
        }

        .header .logo img {
            height: 6rem; 
        }

        .header .navbar {
            display: flex;
        }

        .header .navbar a {
            margin: 0 1rem;
            font-size: 1.6rem;
            color: #fff;
        }

        .header .navbar a:hover {
            color: var(--main-color);
            border-bottom: .1rem solid var(--main-color);
            padding-bottom: .5rem;
        }   

        .header .icons div {
            color: #fff;
            font-size: 2.5rem;
            margin-left: 2rem;
        }

        .header .icons div:hover {
            color: var(--main-color);
        }

        #menu-btn {
            display: none;
        }

        .header .search-form {
            position: absolute;
            top: 115%; right: 7%;
            background: #fff;
            width: 50%;
            height: 5rem;
            display: flex;
            align-items: center;
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.3s ease;
            border-radius: 5px;
            padding: 5px 10px;
        }   

        .header .search-form.active {
            transform: scaleY(1);
        }

        .header .search-form input {
            width: 100%;
            height: 100%;
            font-size: 1.6rem;
            color: var(--black);
            padding: 1rem;
            text-transform: none;
        }   

        .header .search-form label {
            cursor: pointer;
            font-size: 2.2rem;
            margin-right: 1.5rem;
            color: var(--black);
        }

        .header .search-form label:hover {
            color: #00cc88;
        }

        .contact {
            padding: 2rem 7%;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .contact .row {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            width: 100%;
        }

        .contact .row .left-side {
            flex: 1 1 60%;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .contact .row .left-side .map {
            height: 40rem;
            border: var(--border);
            border-radius: .5rem;
            overflow: hidden;
        }

        .contact .row .left-side .map iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }

        .contact .row .left-side .contact-info {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .contact .row .left-side .contact-info .box {
            flex: 1 1 45%;
            padding: 2rem;
            border: var(--border);
            border-radius: .5rem;
            background: rgba(255,255,255,0.05);
        }

        .contact .row .left-side .contact-info .box i {
            font-size: 3rem;
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .contact .row .left-side .contact-info .box h3 {
            font-size: 2rem;
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .contact .row .left-side .contact-info .box p {
            font-size: 1.6rem;
            line-height: 1.8;
        }

        .contact .row .right-side {
            flex: 1 1 35%;
            padding: 2rem;
            border: var(--border);
            border-radius: .5rem;
            background: rgba(255,255,255,0.05);
        }

        .contact .row .right-side h3 {
            font-size: 3rem;
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .contact .row .right-side .inputBox {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .contact .row .right-side .inputBox input,
        .contact .row .right-side textarea {
            width: 100%;
            padding: 1.2rem;
            margin: 1rem 0;
            font-size: 1.6rem;
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-radius: .5rem;
            border: var(--border);
        }

        .contact .row .right-side .inputBox input {
            width: 49%;
        }

        .contact .row .right-side textarea {
            height: 20rem;
            resize: none;
        }

        .contact .row .right-side .btn {
            background: var(--main-color);
            color: var(--black);
            padding: 1rem 3rem;
            font-size: 1.7rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
            border-radius: .5rem;
            transition: .3s ease;
        }

        .contact .row .right-side .btn:hover {
            background: #00cc88;
            transform: scale(1.05);
        }

        @media (max-width: 991px) {
            html {
                font-size: 55%;
            }

            .contact .row {
                flex-direction: column;
            }

            .contact .row .left-side .map {
                height: 30rem;
            }

            .contact .row .right-side .inputBox input {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            #menu-btn {
                display: inline-block;
            }

            .header .navbar {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--bg);
                border-top: var(--border);
                display: none;
            }

            .header .navbar.active {
                display: block;
            }

            .header .navbar a {
                display: block;
                margin: 2rem;
                font-size: 2rem;
                text-align: center;
            }

            .contact .row .left-side .map {
                height: 25rem;
            }

            .contact .row .left-side .contact-info .box {
                flex: 1 1 100%;
            }
        }

        @media (max-width: 450px) {
            html {
                font-size: 50%;
            }

            .contact .row .left-side .map {
                height: 20rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">
            <img src="med-01.png" alt="" width="150" height="100" />
        </a>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="available_medicines.php">Medicines</a>
            <a href="about.php">About</a>
            <a href="contact_us.php">Contact us</a>
        </nav>

        <div class="icons">
            <div class="fas fa-bars" id="menu-btn"></div>                
        </div>
    </header>

    <section class="contact" id="contact">
        <div class="row">
            <div class="left-side">
                <div class="map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.902041973008!2d90.39945201538577!3d23.75087678458901!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b85a288d6e73%3A0x3443a632b23a7f17!2sDhaka%2C%20Bangladesh!5e0!3m2!1sen!2sbd!4v1708099834567!5m2!1sen!2sbd"  
                        allowfullscreen="" loading="lazy"></iframe>
                </div>

                <div class="contact-info">
                    <div class="box">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Our Address</h3>
                        <p>123 Medical Street, Health City, HC 12345</p>
                    </div>

                    <div class="box">
                        <i class="fas fa-phone"></i>
                        <h3>Call Us</h3>
                        <p>+123 456 7890<br>+111 222 3333</p>
                    </div>

                    <div class="box">
                        <i class="fas fa-envelope"></i>
                        <h3>Email Us</h3>
                        <p>info@medicalhub.com<br>support@medicalhub.com</p>
                    </div>

                    <div class="box">
                        <i class="fas fa-clock"></i>
                        <h3>Opening Hours</h3>
                        <p>Mon-Fri: 9am - 6pm<br>Sat: 10am - 4pm</p>
                    </div>
                </div>
            </div>

            <div class="right-side">
                <form id="contactForm" action="contact_us.php" method="POST">
                    <h3>Get in Touch</h3>
                    <?php if (isset($successMessage)): ?>
                        <div style="color: var(--main-color); font-size: 1.8rem; margin-bottom: 1rem;">
                            <?php echo $successMessage; ?>
                        </div>
                    <?php elseif (isset($errorMessage)): ?>
                        <div style="color: red; font-size: 1.8rem; margin-bottom: 1rem;">
                            <?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>
                    <div class="inputBox">
                        <input type="text" name="name" placeholder="Your Name" required>
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="inputBox">
                        <input type="number" name="phone" placeholder="Your Phone">
                        <input type="text" name="subject" placeholder="Subject">
                    </div>
                    <textarea name="message" placeholder="Your Message" required></textarea>
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <script>
        // Hamburger menu functionality
        let navbar = document.querySelector('.navbar');
        document.querySelector('#menu-btn').onclick = () => {
            navbar.classList.toggle('active');
        }

        // Close navbar when clicking outside
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.header') && navbar.classList.contains('active')) {
                navbar.classList.remove('active');
            }
        });

        // Close navbar on scroll
        window.onscroll = () => {
            navbar.classList.remove('active');
        }
    </script>
</body>
</html>