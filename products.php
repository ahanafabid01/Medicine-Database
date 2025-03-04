<?php
include 'db_connect.php';

// Pagination setup
$limit = 17;
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 1;
$start_from = ($page - 1) * $limit;

// Fetch paginated data
$stmt = $conn->prepare("SELECT * FROM medicines ORDER BY medicine_id, country LIMIT ?, ?");
$stmt->bind_param("ii", $start_from, $limit);
$stmt->execute();
$rs_result = $stmt->get_result();

// Get total records
$count_sql = "SELECT COUNT(*) AS total FROM medicines";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Medicine Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-bg: #0a0f1d;
            --secondary-bg: #1a1f2d;
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --surface: #2d3344;
            --border: #3f4555;
            --danger: #ef4444;
            --radius-lg: 12px;
            --radius-md: 8px;
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.3);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--primary-bg);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-tap-highlight-color: transparent;
            touch-action: pan-y;
        }

        .sidebar {
            background: var(--secondary-bg);
            width: 280px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: -280px;
            padding: 1.5rem;
            border-right: 1px solid var(--border);
            z-index: 1000;
            transition: transform var(--transition);
            transform: translateX(-100%);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            box-shadow: 2px 0 15px rgba(0,0,0,0.2);
        }

        .sidebar.visible {
            transform: translateX(0);
            left: 0;
        }

        .sidebar h2 {
            color: var(--text-primary);
            margin-bottom: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 1.25rem;
            letter-spacing: -0.5px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-secondary);
            padding: 0.875rem 1rem;
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: var(--transition);
            font-size: 0.875rem;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text-primary);
        }

        .main-content {
            margin-left: 0;
            padding: 2rem;
            transition: var(--transition);
            position: relative;
            min-height: 100vh;
        }

        .hamburger-menu {
            display: block;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.75rem;
            position: fixed;
            top: 0.5rem;
            left: 0.5rem;
            z-index: 1001;
            touch-action: manipulation;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .header form {
            position: relative;
            flex-grow: 1;
            max-width: 600px;
        }

        .header input[type="text"] {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: var(--secondary-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            color: var(--text-primary);
            padding-right: 3rem;
            font-size: 0.875rem;
        }

        .header button[type="submit"] {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .search-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            left: 0;
            width: 100%;
            background: var(--secondary-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }

        .search-dropdown a {
            display: block;
            padding: 0.875rem 1.5rem;
            text-decoration: none;
            color: var(--text-primary);
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .search-dropdown a:hover {
            background: var(--surface);
        }

        table {
            width: 100%;
            background: var(--secondary-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            border-collapse: collapse;
            margin: 2rem 0;
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: 0.875rem;
        }

        th {
            background: var(--surface);
            font-weight: 600;
        }

        .action-group {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            touch-action: manipulation;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 0.5rem 0.875rem;
            background: var(--secondary-bg);
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .pagination a.active {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }

        .country-dropdown {
            background: var(--surface);
            color: var(--text-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.25rem 0.5rem;
            font-size: 0.85rem;
        }

        @media (min-width: 1025px) {
            .sidebar {
                left: 0;
                transform: translateX(0);
            }
            .main-content {
                margin-left: 280px;
            }
            .hamburger-menu {
                display: none;
            }
        }

        @media (max-width: 1024px) {
            body {
                font-size: 14px;
            }
            
            .main-content {
                padding: 1.5rem;
            }

            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            th, td {
                min-width: 120px;
                padding: 0.75rem 1rem;
            }

            .hamburger-menu {
                display: block;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 13px;
            }
            
            .main-content {
                padding: 1rem;
            }

            th, td {
                min-width: 100px;
                padding: 0.5rem 0.75rem;
            }

            .btn {
                padding: 0.4rem 0.8rem;
            }

            .sidebar {
                width: 260px;
            }
        }

        /* Touch improvements */
        .touch-optimized {
            -webkit-touch-callout: none;
            user-select: none;
        }

        .action-group button {
            padding: 0.6rem;
            min-width: 40px;
            justify-content: center;
        }
    </style>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('visible');
        }

        // Enhanced touch handling
        let touchStartX = 0;
        let touchStartTime = 0;
        const touchThreshold = 60;
        const maxSwipeTime = 500;

        document.addEventListener('touchstart', e => {
            touchStartX = e.touches[0].clientX;
            touchStartTime = Date.now();
            if (e.touches[0].clientX < 100) {
                e.preventDefault();
            }
        });

        document.addEventListener('touchend', e => {
            const touchEndX = e.changedTouches[0].clientX;
            const deltaX = touchEndX - touchStartX;
            const elapsedTime = Date.now() - touchStartTime;
            const sidebar = document.querySelector('.sidebar');

            if (Math.abs(deltaX) > touchThreshold && elapsedTime < maxSwipeTime) {
                if (deltaX > 0 && touchStartX < 100) { // Increased trigger area
                    sidebar.classList.add('visible');
                } else if (deltaX < 0 && sidebar.classList.contains('visible')) {
                    sidebar.classList.remove('visible');
                }
            }
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            const sidebar = document.querySelector('.sidebar');
            const hamburger = document.querySelector('.hamburger-menu');
            
            if (sidebar.classList.contains('visible') && 
                !sidebar.contains(e.target) && 
                !hamburger.contains(e.target)) {
                sidebar.classList.remove('visible');
            }
        });

        function showSuggestions(str) {
            if (str.length == 0) {
                document.getElementById("suggestions").innerHTML = "";
                document.getElementById("suggestions").style.display = "none";
                return;
            }
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("suggestions").innerHTML = this.responseText;
                    document.getElementById("suggestions").style.display = "block";
                }
            };
            xmlhttp.open("GET", "get_suggestions.php?q=" + str, true);
            xmlhttp.send();
        }

        function fetchRowData(medicineId, dropdown, row) {
            const country = dropdown.value;
            fetch(`fetch_data.php?country=${country}&medicine_id=${medicineId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const medicine = data[0];
                        row.querySelector(".id").innerText = medicine.medicine_id;
                        row.querySelector(".name").innerText = medicine.medicine_name;
                        row.querySelector(".company").innerText = medicine.company_name;
                        row.querySelector(".price").innerText = medicine.price + " " + medicine.currency;
                        row.querySelector(".uses").innerText = medicine.uses;
                        row.querySelector(".more_info").innerText = medicine.more_info;
                        row.querySelector(".added").innerText = medicine.created_at;
                    }
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        function confirmDelete(medicineId, country) {
            if (confirm("Are you sure you want to delete this medicine?")) {
                fetch(`delete_medicine.php`, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${medicineId}&country=${encodeURIComponent(country)}`
                })
                .then(response => {
                    if (response.ok) {
                        alert("Medicine deleted successfully.");
                        window.location.reload();
                    } else {
                        alert("Failed to delete the medicine.");
                    }
                })
                .catch(error => console.error("Error deleting medicine:", error));
            }
        }

        function openUpdateForm(medicineId, country) {
            window.location.href = `update_medicine.php?id=${medicineId}&country=${encodeURIComponent(country)}`;
        }

        // Prevent body scroll when sidebar is open
        document.querySelector('.sidebar').addEventListener('transitionend', () => {
            document.body.style.overflow = document.querySelector('.sidebar').classList.contains('visible') 
                ? 'hidden' 
                : '';
        });
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Cure Connectors</h2>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="products.php"><i class="fas fa-box"></i> Products</a>
        <a href="add_medicine.php"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="show_contacts.php"><i class="fas fa-envelope"></i> Messages</a>
        <a href="user.php"><i class="fas fa-user"></i> Users</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <button class="hamburger-menu" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="header">
            <form method="GET" action="search_results.php">
                <input type="text" name="search" placeholder="Search" 
                       onkeyup="showSuggestions(this.value)"
                       autocomplete="off"
                       aria-label="Search medicines">
                <button type="submit"><i class="fas fa-search"></i></button>
                <div id="suggestions" class="search-dropdown"></div>
            </form>
        </div>

        <div class="table-container">
            <h1>Medicine Information</h1>
            <table>
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Price</th>
                        <th>Uses</th>
                        <th>Info</th>
                        <th>Added On</th>
                        <th>Country</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $rs_result->fetch_assoc()): ?>
                    <tr>
                        <td class="id"><?= htmlspecialchars($row["medicine_id"]) ?></td>
                        <td class="name"><?= htmlspecialchars($row["medicine_name"]) ?></td>
                        <td class="company"><?= htmlspecialchars($row["company_name"]) ?></td>
                        <td class="price"><?= htmlspecialchars($row["price"]) ?> <?= htmlspecialchars($row["currency"]) ?></td>
                        <td class="uses"><?= htmlspecialchars($row["uses"]) ?></td>
                        <td class="more_info"><?= htmlspecialchars($row["more_info"]) ?></td>
                        <td class="added"><?= htmlspecialchars($row["created_at"]) ?></td>
                        <td>
                            <select class="country-dropdown" onchange="fetchRowData(<?= $row['medicine_id'] ?>, this, this.closest('tr'))">
                                <option value="Bangladesh" <?= $row["country"] === "Bangladesh" ? "selected" : "" ?>>Bangladesh</option>
                                <option value="India" <?= $row["country"] === "India" ? "selected" : "" ?>>India</option>
                                <option value="Malaysia" <?= $row["country"] === "Malaysia" ? "selected" : "" ?>>Malaysia</option>
                                <option value="Singapore" <?= $row["country"] === "Singapore" ? "selected" : "" ?>>Singapore</option>
                            </select>
                        </td>
                        <td>
                            <div class="action-group">
                                <button class="btn btn-primary" onclick="openUpdateForm(<?= $row['medicine_id'] ?>, '<?= $row['country'] ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" onclick="confirmDelete(<?= $row['medicine_id'] ?>, '<?= $row['country'] ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</body>
</html>