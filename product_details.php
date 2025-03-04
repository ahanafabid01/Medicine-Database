<?php
include 'db_connect.php';

$medicine_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$country = isset($_GET['country']) ? $_GET['country'] : 'Bangladesh';

$medicine = null;
$images = [];

if ($medicine_id) {
    $sql = "SELECT * FROM medicines WHERE medicine_id = ? AND country = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $medicine_id, $country);
    $stmt->execute();
    $result = $stmt->get_result();
    $medicine = $result->fetch_assoc();
    $stmt->close();

    $img_sql = "SELECT * FROM medicine_images WHERE medicine_id = ?";
    $img_stmt = $conn->prepare($img_sql);
    $img_stmt->bind_param("i", $medicine_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    while ($img_row = $img_result->fetch_assoc()) {
        $images[] = $img_row['image_path'];
    }
    $img_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2A5C82;
            --secondary-color: #3F8AA6;
            --accent-color: #5EB1BF;
            --background-color: #F8F9FC;
            --text-dark: #2D3436;
            --text-light: #FFFFFF;
            --border-color: #E0E4E9;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--background-color);
            padding-top: 80px;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Enhanced Header Styles */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--primary-color);
            padding: 1rem 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            height: 70px;
            transition: var(--transition);
        }

        .header .logo img {
            height: 50px;
            width: auto;
            transition: var(--transition);
        }

        /* Improved Responsive Navbar */
        .navbar {
            display: flex;
            gap: 2rem;
            position: relative;
        }

        .navbar a {
            text-decoration: none;
            color: var(--text-light);
            font-size: 1.1rem;
            transition: var(--transition);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            position: relative;
        }

        .navbar a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            transition: var(--transition);
        }

        .navbar a:hover::after {
            width: 100%;
        }

        .icons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .fas {
            font-size: 1.6rem;
            cursor: pointer;
            color: var(--text-light);
            transition: var(--transition);
        }

        .fas:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        #menu-btn {
            display: none;
        }

        /* Modern Search Form */
        .search-form {
            position: absolute;
            top: 115%;
            right: 5%;
            background: #fff;
            width: 90%;
            height: 3.8rem;
            display: flex;
            align-items: center;
            transform: scaleY(0);
            transform-origin: top;
            transition: var(--transition);
            border-radius: 8px;
            padding: 0 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1001;
        }

        .search-form.active {
            transform: scaleY(1);
        }

        .search-form input {
            width: 100%;
            height: 100%;
            font-size: 1.1rem;
            color: var(--text-dark);
            padding: 0 1rem;
            border: none;
            outline: none;
            background: transparent;
        }

        /* Enhanced Suggestions Dropdown */
        .suggestions-container {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #fff;
            border-radius: 0 0 8px 8px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1001;
            display: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .suggestions-container a {
            display: block;
            padding: 12px 20px;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 1rem;
            transition: var(--transition);
            border-bottom: 1px solid var(--border-color);
        }

        .suggestions-container a:hover {
            background: var(--background-color);
            padding-left: 25px;
        }

        /* Product Details Container */
        .product-details-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .image-gallery-container {
            margin-bottom: 2rem;
            border-radius: 12px;
            overflow: hidden;
        }

        .main-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            background: var(--background-color);
        }

        .main-image {
            width: 100%;
            height: 500px;
            object-fit: contain;
            transition: opacity 0.3s;
        }

        .navigation-arrows {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 1rem;
        }

        .arrow-btn {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .arrow-btn:hover {
            transform: scale(1.1) translateY(-1px);
            background: var(--primary-color);
            color: white;
        }

        .thumbnail-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
            padding: 0 1rem;
        }

        .thumbnail {
            width: 100%;
            height: 70px;
            object-fit: cover;
            border: 2px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            background: var(--background-color);
            padding: 3px;
        }

        .thumbnail.active {
            border-color: var(--primary-color);
            transform: scale(1.05);
        }

        /* Enhanced Product Info Styles */
        .product-info {
            padding: 2rem 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        .form-group label {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1rem;
            padding: 0.5rem 0;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 0.8rem 1.2rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            background: var(--background-color);
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(42, 92, 130, 0.1);
        }

        .price-highlight {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.4rem;
        }

        .error-message {
            padding: 1.5rem;
            background: #ffe3e6;
            color: #dc3545;
            border-radius: 8px;
            border: 1px solid #ffc9d0;
            margin: 2rem 0;
            font-size: 1.1rem;
        }

        /* Responsive Design Improvements */
        @media (max-width: 768px) {
            .header {
                padding: 1rem 5%;
            }

            .navbar {
                position: fixed;
                top: 70px;
                right: -100%;
                width: 280px;
                height: calc(100vh - 70px);
                background: #fff;
                flex-direction: column;
                padding: 2rem 1rem;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.05);
                transition: var(--transition);
            }

            .navbar.active {
                right: 0;
            }

            .navbar a {
                color: var(--text-dark);
                padding: 1rem 1.5rem;
                border-radius: 6px;
                margin: 0.25rem 0;
            }

            .navbar a:hover {
                background: var(--background-color);
                transform: none;
            }

            .navbar a::after {
                display: none;
            }

            #menu-btn {
                display: block;
            }

            .main-image {
                height: 350px;
            }

            .product-details-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .form-group {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .thumbnail-container {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 480px) {
            .header .logo img {
                height: 40px;
            }

            .main-image {
                height: 280px;
            }

            .arrow-btn {
                width: 35px;
                height: 35px;
            }

            .thumbnail {
                height: 60px;
            }

            .product-info {
                padding: 1rem 0;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 0.7rem 1rem;
            }
        }

        @media (min-width: 1600px) {
            .product-details-container {
                max-width: 1400px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">
            <img src="med-01.png" alt="Pharmacy Logo">
        </a>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="available_medicines.php">Medicines</a>
            <a href="about.php">About</a>
            <a href="contact_us.php">Contact us</a>
        </nav>
        <div class="icons">
            <div class="fas fa-search" id="search-btn"></div>
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
        <form class="search-form">
            <input type="search" id="search-box" placeholder="Search medicines...">
            <label for="search-box" class="fas fa-search"></label>
            <div class="suggestions-container" id="suggestions"></div>
        </form>
    </header>

    <div class="product-details-container">
        <?php if ($medicine): ?>
            <div class="image-gallery-container">
                <?php if (!empty($images)): ?>
                    <div class="main-image-container">
                        <img src="<?= htmlspecialchars($images[0]) ?>" class="main-image" alt="Medicine Image">
                        <?php if (count($images) > 1): ?>
                            <div class="navigation-arrows">
                                <button class="arrow-btn left-arrow"><i class="fas fa-chevron-left"></i></button>
                                <button class="arrow-btn right-arrow"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="thumbnail-container">
                            <?php foreach ($images as $index => $image): ?>
                                <img src="<?= htmlspecialchars($image) ?>" class="thumbnail <?= $index === 0 ? 'active' : '' ?>" alt="Thumbnail">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-image">No images available</div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <div class="form-group">
                    <label>Country:</label>
                    <select onchange="switchCountry(this)">
                        <?php
                        $countries = ['Bangladesh', 'India', 'Malaysia', 'Singapore'];
                        foreach ($countries as $c) {
                            $selected = ($c == $country) ? 'selected' : '';
                            echo "<option value='$c' $selected>$c</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Medicine Name:</label>
                    <input type="text" value="<?= htmlspecialchars($medicine['medicine_name']) ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>Company Name:</label>
                    <input type="text" value="<?= htmlspecialchars($medicine['company_name']) ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>Price:</label>
                    <input type="text" class="price-highlight" value="<?= htmlspecialchars($medicine['price']) ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>Uses:</label>
                    <textarea readonly><?= htmlspecialchars($medicine['uses']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>More Information:</label>
                    <textarea readonly><?= htmlspecialchars($medicine['more_info']) ?></textarea>
                </div>
            </div>
        <?php else: ?>
            <div class="error-message">No medicine found for selected country!</div>
        <?php endif; ?>
    </div>

    <script>
        // Enhanced JavaScript Interactions
        const navbar = document.querySelector('.navbar');
        const searchForm = document.querySelector('.search-form');
        const menuBtn = document.getElementById('menu-btn');
        const searchBtn = document.getElementById('search-btn');

        // Hamburger Menu Toggle
        menuBtn.addEventListener('click', () => {
            navbar.classList.toggle('active');
            menuBtn.classList.toggle('fa-times');
            searchForm.classList.remove('active');
        });

        // Search Form Toggle
        searchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            searchForm.classList.toggle('active');
            navbar.classList.remove('active');
            menuBtn.classList.remove('fa-times');
        });

        // Close Menus on Click Outside
        document.addEventListener('click', (e) => {
            if (!navbar.contains(e.target) && !menuBtn.contains(e.target)) {
                navbar.classList.remove('active');
                menuBtn.classList.remove('fa-times');
            }
            if (!searchForm.contains(e.target) && !searchBtn.contains(e.target)) {
                searchForm.classList.remove('active');
            }
        });

        // Image Gallery Functionality
        document.addEventListener('DOMContentLoaded', () => {
            const mainImage = document.querySelector('.main-image');
            const thumbnails = document.querySelectorAll('.thumbnail');
            const arrowBtns = document.querySelectorAll('.arrow-btn');
            let currentIndex = 0;

            function updateImage(index) {
                const images = <?php echo json_encode($images); ?>;
                if (images.length > 0) {
                    mainImage.src = images[index];
                    thumbnails.forEach((thumb, i) => {
                        thumb.classList.toggle('active', i === index);
                    });
                }
            }

            arrowBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const images = <?php echo json_encode($images); ?>;
                    currentIndex = btn.classList.contains('left-arrow') 
                        ? (currentIndex - 1 + images.length) % images.length
                        : (currentIndex + 1) % images.length;
                    updateImage(currentIndex);
                });
            });

            thumbnails.forEach((thumb, index) => {
                thumb.addEventListener('click', () => {
                    currentIndex = index;
                    updateImage(currentIndex);
                });
            });
        });

        // Country Switcher
        function switchCountry(select) {
            const newCountry = select.value;
            const medicineId = <?= isset($_GET['id']) ? $_GET['id'] : 'null' ?>;
            if (medicineId && newCountry) {
                window.location.href = `product_details.php?id=${medicineId}&country=${newCountry}`;
            }
        }

        // Window Resize Handler
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                navbar.classList.remove('active');
                menuBtn.classList.remove('fa-times');
            }
        });

        // Search Suggestions
        const searchBox = document.getElementById('search-box');
        const suggestionsContainer = document.getElementById('suggestions');

        searchBox.addEventListener('input', async () => {
            const query = searchBox.value.trim();
            suggestionsContainer.style.display = query ? 'block' : 'none';
            
            if (query.length > 0) {
                try {
                    const response = await fetch(`suggestions.php?q=${encodeURIComponent(query)}`);
                    const data = await response.text();
                    suggestionsContainer.innerHTML = data;
                } catch (error) {
                    console.error('Error fetching suggestions:', error);
                }
            }
        });

        // Handle Suggestion Clicks
        suggestionsContainer.addEventListener('click', (e) => {
            if (e.target.tagName === 'A') {
                e.preventDefault();
                searchBox.value = e.target.textContent;
                performSearch();
            }
        });

        // Perform Search
        function performSearch() {
            const query = searchBox.value.trim();
            if (query) {
                window.location.href = `searched_results.php?search=${encodeURIComponent(query)}`;
            }
        }
    </script>
</body>
</html>