<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare - Modern Medical Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== Base Reset ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ===== Theme Variables ===== */
        :root {
            --primary: #2EBAC1;
            --secondary: #FF6B6B;
            --dark: #0A1929;
            --medium: #1A365D;
            --light: #F0F4F8;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark);
            color: var(--light);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ===== Header ===== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            background: var(--medium);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .logo img {
            width: 160px;
            height: auto;
            filter: brightness(0) invert(1);
        }

        .navbar {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .navbar a {
            color: var(--light);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .navbar a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .icons {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .icons .fas {
            font-size: 1.5rem;
            color: var(--light);
            cursor: pointer;
            transition: var(--transition);
        }

        .icons .fas:hover {
            color: var(--primary);
        }

        /* ===== Search Form ===== */
        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-form {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--medium);
            padding: 1rem;
            border-radius: 10px;
            width: 300px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .search-form.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .search-form input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--primary);
            border-radius: 6px;
            background: transparent;
            color: var(--light);
            font-size: 1rem;
        }

        .suggestions-container {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            width: 100%;
            background-color: var(--medium);
            border: 1px solid var(--primary);
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .suggestions-container.active {
            display: block;
        }

        .suggestions-container a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: var(--light);
            transition: background-color 0.3s ease;
        }

        .suggestions-container a:hover {
            background-color: var(--dark);
            color: var(--primary);
        }

        /* ===== Welcome Section ===== */
        .welcome-message {
            padding: 12rem 5% 4rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .welcome-message h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-message p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            color: rgba(240, 244, 248, 0.9);
        }

        /* ===== Video Section ===== */
        .video-container {
            position: relative;
            max-width: 1200px;
            margin: 2rem auto;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        .video-container iframe {
            width: 100%;
            height: 600px;
            border: none;
        }

        /* ===== Footer ===== */
        .footer {
            background: var(--medium);
            padding: 3rem 5%;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h4 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .footer-links a {
            color: var(--light);
            text-decoration: none;
            display: block;
            margin-bottom: 0.8rem;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--secondary);
            transform: translateX(5px);
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 1024px) {
            .navbar {
                position: fixed;
                top: 70px;
                right: -100%;
                width: 80%;
                height: calc(100vh - 70px);
                background: var(--medium);
                flex-direction: column;
                padding: 2rem;
                transition: var(--transition);
            }

            .navbar.active {
                right: 0;
            }

            .icons .fa-bars {
                display: block;
            }
        }

        @media (min-width: 1025px) {
            .icons .fa-bars {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .welcome-message h1 {
                font-size: 2.5rem;
            }

            .video-container iframe {
                height: 400px;
            }
        }

        @media (max-width: 480px) {
            .logo img {
                width: 120px;
            }

            .welcome-message {
                padding: 8rem 5% 2rem;
            }

            .welcome-message h1 {
                font-size: 2rem;
            }

            .search-form {
                width: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="#" class="logo">
            <img src="med-01.png" alt="MediCare Logo">
        </a>
        
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="available_medicines.php">Medicines</a>
            <a href="about.php">About</a>
            <a href="contact_us.php">Contact</a>
        </nav>

        <div class="icons">
            <div class="search-container">
                <div class="fas fa-search" id="search-btn"></div>
                <div class="search-form">
                    <input type="search" id="search-box" placeholder="Search medicines...">
                    <label for="search-box" class="fas fa-search" onclick="performSearch()"></label>
                    <div id="suggestions" class="suggestions-container"></div>
                </div>
            </div>
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
    </header>

    <!-- Welcome Section -->
    <section class="welcome-message">
        <h1>Advanced Healthcare Solutions</h1>
        <p>Trusted medical resources and pharmaceutical services at your fingertips</p>
    </section>

    <!-- Video Section -->
    <div class="video-container">
        <iframe 
            src="https://www.youtube.com/embed/Nx0N-DrPf7M?autoplay=1&mute=1&loop=1&playlist=Nx0N-DrPf7M&controls=0&modestbranding=1&showinfo=0" 
            title="Healthcare Video" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Quick Links</h4>
                <div class="footer-links">
                    <a href="#home">Home</a>
                    <a href="#medicines">Medicines</a>
                    <a href="#about">About Us</a>
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
                    <a href="tel:+1234567890">+1 (234) 567-890</a>
                    <a href="mailto:info@medicare.com">info@medicare.com</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navigation and Search Toggle
        const menuBtn = document.querySelector('#menu-btn');
        const navbar = document.querySelector('.navbar');
        const searchBtn = document.querySelector('#search-btn');
        const searchForm = document.querySelector('.search-form');
        const searchBox = document.querySelector('#search-box');
        const suggestionsContainer = document.querySelector('#suggestions');

        menuBtn.addEventListener('click', () => {
            navbar.classList.toggle('active');
            searchForm.classList.remove('active');
        });

        searchBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            searchForm.classList.toggle('active');
            navbar.classList.remove('active');
            if (searchForm.classList.contains('active')) {
                searchBox.focus();
            }
        });

        // Close search when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchForm.contains(e.target) && !searchBtn.contains(e.target)) {
                searchForm.classList.remove('active');
                suggestionsContainer.style.display = 'none';
            }
        });

        // Search functionality
        function performSearch() {
            const query = searchBox.value.trim();
            if (query.length > 0) {
                window.location.href = `searched_results.php?search=${encodeURIComponent(query)}`;
            }
        }

        // Handle Enter key in search box
        searchBox.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });

        // Search Suggestions
        searchBox.addEventListener('input', debounce(function() {
            const query = this.value.trim();
            if (!query) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
                return;
            }

            fetch(`get_suggestion.php?q=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(data => {
                    suggestionsContainer.innerHTML = data;
                    suggestionsContainer.style.display = 'block';
                })
                .catch(error => console.error('Error fetching suggestions:', error));
        }, 300));

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Handle suggestion clicks
        suggestionsContainer.addEventListener('click', (e) => {
            if (e.target.tagName === 'A') {
                e.preventDefault();
                searchBox.value = e.target.textContent;
                suggestionsContainer.style.display = 'none';
                performSearch();
            }
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>