<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - MediCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Add this media query to hide menu button on big screens */
        @media (min-width: 769px) {
            #menu-btn {
                display: none !important;
            }
        }

        /* Added mission paragraph styling */
        .content-section p {
            max-width: 800px;
            margin: 0 auto 40px;
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-muted);
            text-align: center;
        }

        /* Existing CSS below - no other changes made */
        :root {
            --primary-color: #2A5C82;
            --secondary-color: #3F8AA6;
            --accent-color: #5EB1BF;
            --dark-bg: #0A192F;
            --dark-secondary: #172A45;
            --text-light: #F8F9FC;
            --text-muted: #8892B0;
            --border-color: #303C55;
        }

        body {
            margin: 0;
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }

        .header {
            background: var(--dark-secondary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 7%;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            height: 70px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .header .logo img {
            height: 50px;
        }

        .header .navbar a {
            margin: 0 1rem;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .header .navbar a:hover {
            color: var(--accent-color);
        }

        .header .icons div {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.8rem;
            margin-left: 1.5rem;
            cursor: pointer;
        }

        .search-form {
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

        .search-form.active {
            transform: scaleY(1);
        }

        .search-form input {
            width: 100%;
            height: 100%;
            font-size: 1.6rem;
            color: var(--dark-bg);
            padding: 1rem;
            border: none;
            outline: none;
        }

        .suggestions-container {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .suggestions-container a {
            display: block;
            padding: 10px;
            color: var(--dark-bg);
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .suggestions-container a:hover {
            background: #f4f4f4;
        }

        .about-hero {
            padding: 120px 20px 60px;
            text-align: center;
            background: var(--dark-secondary);
            margin-top: 70px;
        }

        .about-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .content-section {
            padding: 80px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
            font-size: 2.5rem;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .team-member {
            background: var(--dark-secondary);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-5px);
        }

        .member-photo {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            margin-bottom: 20px;
        }

        .member-name {
            font-size: 1.4rem;
            margin: 10px 0;
        }

        .member-role {
            color: var(--accent-color);
            font-weight: 500;
            margin-bottom: 15px;
        }

        .medicine-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--dark-secondary);
            border-radius: 12px;
            overflow: hidden;
        }

        .medicine-table th,
        .medicine-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .medicine-table th {
            background: var(--primary-color);
            font-weight: 600;
        }

        .medicine-table tr:hover td {
            background: rgba(255, 255, 255, 0.03);
        }

        .footer {
            background: var(--dark-secondary);
            padding: 50px 20px;
            margin-top: 80px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-section h4 {
            margin-bottom: 20px;
            font-size: 1.2rem;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .header .navbar {
                position: absolute;
                top: 100%;
                right: -100%;
                background: var(--dark-secondary);
                width: 100%;
                height: calc(100vh - 70px);
                transition: 0.3s ease;
            }

            .header .navbar.active {
                right: 0;
            }

            .header .navbar a {
                display: block;
                margin: 2rem;
                font-size: 1.5rem;
            }

            .search-form {
                width: 90%;
                right: 5%;
            }

            .about-hero h1 {
                font-size: 2.2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }

            .medicine-table {
                display: block;
                overflow-x: auto;
            }

            /* Added mobile paragraph styling */
            .content-section p {
                font-size: 1rem;
                padding: 0 10px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">
            <img src="med-01.png" alt="MediCare Logo" width="150" height="100">
        </a>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="available_medicines.php">Medicines</a>
            <a href="about.php">About</a>
            <a href="contact_us.php">Contact</a>
        </nav>
        <div class="icons">
            <!-- <div class="fas fa-search" id="search-btn"></div> -->
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
        <div class="search-form">
            <input type="search" id="search-box" placeholder="Search medicines...">
            <label for="search-box" class="fas fa-search" onclick="performSearch()"></label>
            <div id="suggestions" class="suggestions-container"></div>
        </div>
    </header>

    <main>
        <section class="about-hero">
            <h1>Transforming Pharmaceutical Access</h1>
            <p>Committed to fair pricing and global medicine accessibility through innovative solutions.</p>
        </section>

        <section class="content-section">
            <h2 class="section-title">Our Mission</h2>
            <p>MediCare is a global pharmaceutical company dedicated to improving healthcare access and affordability for all. We believe that everyone should have access to essential medicines without financial barriers. Our mission is to provide innovative solutions that reduce the cost of healthcare and improve the quality of life for patients worldwide</p>
            <div class="team-grid">
                <div class="team-member">
                    <img src="member1.jpeg" alt="CEO" class="member-photo">
                    <h3 class="member-name">Dr. Emily Carter</h3>
                    <p class="member-role">Chief Executive Officer</p>
                    <p>20+ years in pharmaceutical policy reform</p>
                </div>
                <div class="team-member">
                    <img src="member2.jpg" alt="CTO" class="member-photo">
                    <h3 class="member-name">Ahanaf Abid Sazid</h3>
                    <p class="member-role">Technology Director</p>
                    <p>Healthcare data systems expert</p>
                </div>
                <div class="team-member">
                    <img src="member3.jpg" alt="Researcher" class="member-photo">
                    <h3 class="member-name">Maria Gonzalez</h3>
                    <p class="member-role">Global Research Lead</p>
                    <p>International pharmaceutical analyst</p>
                </div>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">Global Price Analysis</h2>
            <table class="medicine-table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Manufacturer</th>
                        <th>Country</th>
                        <th>Average Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cardiocor</td>
                        <td>HealthPlus</td>
                        <td>United States</td>
                        <td>$245</td>
                    </tr>
                    <tr>
                        <td>Cardiocor</td>
                        <td>MediGlobal</td>
                        <td>India</td>
                        <td>$58</td>
                    </tr>
                    <tr>
                        <td>Neurozan</td>
                        <td>EuroPharma</td>
                        <td>Germany</td>
                        <td>€189</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Quick Links</h4>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="available_medicines.php">Medicines</a>
                    <a href="aboutus.php">About Us</a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <div class="footer-links">
                    <a href="#privacy">Privacy Policy</a>
                    <a href="#terms">Terms of Service</a>
                    <a href="#security">Security</a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <div class="footer-links">
                    <a href="tel:+1123456789">+1 (123) 456-789</a>
                    <a href="mailto:contact@medicare.com">contact@medicare.com</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Header interactions
        const navbar = document.querySelector('.navbar');
        const searchForm = document.querySelector('.search-form');
        const menuBtn = document.querySelector('#menu-btn');
        const searchBtn = document.querySelector('#search-btn');

        menuBtn.onclick = () => {
            navbar.classList.toggle('active');
            searchForm.classList.remove('active');
        }

        searchBtn.onclick = () => {
            searchForm.classList.toggle('active');
            navbar.classList.remove('active');
        }

        window.onscroll = () => {
            navbar.classList.remove('active');
            searchForm.classList.remove('active');
        }

        // Search functionality
        function performSearch() {
            const query = document.getElementById('search-box').value.trim();
            if(query) window.location.href = `searched_results.php?search=${encodeURIComponent(query)}`;
        }

        // Suggestions handling
        document.addEventListener('DOMContentLoaded', () => {
            const searchBox = document.getElementById('search-box');
            const suggestions = document.getElementById('suggestions');

            searchBox.addEventListener('input', async () => {
                const query = searchBox.value.trim();
                if (!query) {
                    suggestions.style.display = 'none';
                    return;
                }

                try {
                    const response = await fetch(`suggestions.php?q=${encodeURIComponent(query)}`);
                    suggestions.innerHTML = await response.text();
                    suggestions.style.display = 'block';
                } catch(error) {
                    console.error('Error fetching suggestions:', error);
                }
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('.search-form')) {
                    suggestions.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>