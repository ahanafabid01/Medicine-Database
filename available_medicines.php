<?php
include 'db_connect.php';

// Initialize variables
$limit = 12;
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 1;
$start_from = ($page - 1) * $limit;
$results = array();
$total_records = 0;

// Fetch all medicines
$stmt = $conn->prepare("SELECT * FROM medicines LIMIT ?, ?");
$stmt->bind_param("ii", $start_from, $limit);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $results[] = $row;
}

// Get total records for pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM medicines");
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$stmt->close();
$conn->close();

$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Available Medicines</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2A5C82;
            --secondary-color: #3F8AA6;
            --accent-color: #5EB1BF;
            --background-color: #F8F9FC;
            --header-bg: #1A2E40;
            --text-dark: #2D3436;
            --text-light: #F8F9FC;
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
            color: var(--text-dark);
            padding-top: 80px;
            line-height: 1.5;
        }

        .container {
            padding: 2rem;
            max-width: 1440px;
            margin: 0 auto;
        }

        .table-container {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 32, 63, 0.08);
            border: 1px solid var(--border-color);
            margin: 0 auto;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 1rem;
            font-weight: 600;
            position: sticky;
            left: 0;
            white-space: nowrap;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        }

        tr:hover td {
            background-color: rgba(94, 177, 191, 0.05);
        }

        .name a {
            text-decoration: none;
            color: inherit;
        }

        #medicineTable tbody tr {
            cursor: pointer;
            transition: var(--transition);
        }

        #medicineTable tbody tr:hover {
            transform: translateX(4px);
        }

        #medicineTable tbody tr td:last-child {
            cursor: default;
            pointer-events: auto;
        }

        .pagination {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .pagination a {
            color: var(--primary-color);
            padding: 0.75rem 1.25rem;
            text-decoration: none;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .pagination a.active {
            background-color: var(--primary-color);
            color: var(--text-light);
            border-color: var(--primary-color);
        }

        .pagination a:hover:not(.active) {
            background-color: var(--accent-color);
            color: var(--text-light);
            border-color: var(--accent-color);
        }

        /* Header Styles */
        .header {
            background: var(--header-bg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 5%;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 70px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header .logo img {
            height: 50px;
            width: auto;
            transition: var(--transition);
        }

        .navbar {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .navbar a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 1rem;
            transition: var(--transition);
            position: relative;
            padding: 0.5rem 0;
        }

        .navbar a::after {
            content: '';
            position: absolute;
            bottom: 0;
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
            gap: 1.25rem;
            align-items: center;
        }

        .fas {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            transition: var(--transition);
            cursor: pointer;
        }

        .fas:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        /* Search Form */
        .search-form {
            position: absolute;
            top: 115%;
            right: 5%;
            background: #fff;
            width: 90%;
            max-width: 500px;
            height: 3.5rem;
            display: flex;
            align-items: center;
            transform: scaleY(0);
            transform-origin: top;
            transition: var(--transition);
            border-radius: 8px;
            padding: 0 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1001;
        }

        .search-form.active {
            transform: scaleY(1);
        }

        .search-form input {
            flex: 1;
            height: 100%;
            border: none;
            outline: none;
            font-size: 1rem;
            padding: 0 1rem;
        }

        .suggestions-container {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #fff;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1002;
        }

        .suggestions-container a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }

        .suggestions-container a:hover {
            background-color: var(--background-color);
        }

        /* Dropdown Styles */
        .country-dropdown {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: #fff;
            font-size: 0.9rem;
            transition: var(--transition);
            max-width: 150px;
        }

        .country-dropdown:hover {
            border-color: var(--accent-color);
        }

        .country-dropdown:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(62, 177, 191, 0.25);
        }

        /* Hamburger Menu Button */
        #menu-btn {
            display: none; /* Hidden by default */
        }

        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .container {
                padding: 1.5rem;
            }
        }

        @media (max-width: 992px) {
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
                padding: 2rem 1.5rem;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.05);
                transition: var(--transition);
            }

            .navbar.active {
                right: 0;
            }

            .navbar a {
                color: var(--text-dark);
                padding: 1rem;
                font-size: 1.1rem;
            }

            .navbar a::after {
                background: var(--primary-color);
            }

            #menu-btn {
                display: inline-block; /* Show on mobile */
            }

            .table-container {
                padding: 1rem;
                margin: 0;
            }

            th, td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 60px;
            }

            .header {
                height: 60px;
            }

            .header .logo img {
                height: 40px;
            }

            .fas {
                font-size: 1.3rem;
            }

            .search-form {
                right: 2%;
                width: 96%;
            }

            .pagination a {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }

            .country-dropdown {
                max-width: 120px;
                padding: 0.4rem 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 1rem;
            }

            th, td {
                padding: 0.5rem;
                font-size: 0.85rem;
            }

            .pagination a {
                padding: 0.5rem 0.8rem;
            }

            .country-dropdown {
                font-size: 0.8rem;
                max-width: 100px;
            }

            .search-form {
                height: 3rem;
            }

            .search-form input {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                width: 100%;
                right: -100%;
            }

            .navbar.active {
                right: 0;
            }

            .table-container {
                border-radius: 8px;
            }

            .suggestions-container a {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="index.php" class="logo">
            <img src="med-01.png" alt="Pharmacy Logo" width="150" height="100">
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
        <div class="search-form">
            <input type="search" id="search-box" placeholder="Search medicines...">
            <label for="search-box" class="fas fa-search" onclick="performSearch()"></label>
            <div id="suggestions" class="suggestions-container"></div>
        </div>
    </header>

    <div class="container">
        <div class="table-container">
            <h1>Medicines</h1>
            <table id="medicineTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Price</th>
                        <th>Used In</th>        
                        <th>Country</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $row): ?>
                            <tr data-href="product_details.php?id=<?= $row['medicine_id'] ?>&country=<?= $row['country'] ?>">
                                <td class="name">
                                    <?= htmlspecialchars($row['medicine_name']) ?>
                                </td>
                                <td class="company"><?= htmlspecialchars($row['company_name']) ?></td>
                                <td class="price"><?= htmlspecialchars($row['price']) ?> <?= htmlspecialchars($row['currency']) ?></td>
                                <td class="uses"><?= htmlspecialchars($row['uses']) ?></td>
                                <td>
                                    <select class="country-dropdown" onchange="fetchRowData(<?= $row['medicine_id'] ?>, this, this.closest('tr'))">
                                        <option value="Bangladesh" <?= $row['country'] === 'Bangladesh' ? 'selected' : '' ?>>Bangladesh</option>
                                        <option value="India" <?= $row['country'] === 'India' ? 'selected' : '' ?>>India</option>
                                        <option value="Malaysia" <?= $row['country'] === 'Malaysia' ? 'selected' : '' ?>>Malaysia</option>
                                        <option value="Singapore" <?= $row['country'] === 'Singapore' ? 'selected' : '' ?>>Singapore</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No medicines found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navbar = document.querySelector('.navbar');
            const searchForm = document.querySelector('.search-form');
            const menuBtn = document.getElementById('menu-btn');
            const searchBtn = document.getElementById('search-btn');
            const searchBox = document.getElementById('search-box');
            const suggestionsContainer = document.getElementById('suggestions');

            // Toggle Mobile Menu
            menuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                navbar.classList.toggle('active');
                searchForm.classList.remove('active');
                menuBtn.classList.toggle('fa-times');
            });

            // Toggle Search Form
            searchBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                searchForm.classList.toggle('active');
                navbar.classList.remove('active');
                menuBtn.classList.remove('fa-times');
                searchBox.focus();
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

            // Row Click Handling
            document.querySelectorAll('#medicineTable tbody tr[data-href]').forEach(row => {
                row.addEventListener('click', (e) => {
                    if (!e.target.closest('.country-dropdown')) {
                        window.location.href = row.dataset.href;
                    }
                });
            });

            // Search Suggestions
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

            // Handle suggestion clicks
            suggestionsContainer.addEventListener('click', (e) => {
                if (e.target.tagName === 'A') {
                    e.preventDefault();
                    searchBox.value = e.target.textContent;
                    performSearch();
                }
            });

            // Handle Enter key in search
            searchBox.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });

            // Country Dropdown Update
            window.fetchRowData = (medicineId, dropdown, row) => {
                const country = dropdown.value;
                fetch(`fetch_data.php?country=${country}&medicine_id=${medicineId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            const medicine = data[0];
                            row.querySelector(".name").textContent = medicine.medicine_name;
                            row.querySelector(".company").textContent = medicine.company_name;
                            row.querySelector(".price").textContent = `${medicine.price} ${medicine.currency}`;
                            row.querySelector(".uses").textContent = medicine.uses;
                            row.dataset.href = `product_details.php?id=${medicineId}&country=${country}`;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            };

            // Perform Search
            window.performSearch = () => {
                const query = searchBox.value.trim();
                if (query) {
                    window.location.href = `searched_results.php?search=${encodeURIComponent(query)}`;
                }
                suggestionsContainer.style.display = 'none';
            };

            // Close suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchBox.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>