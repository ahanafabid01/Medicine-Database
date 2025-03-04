<?php
include 'db_connect.php';

// Update visitor count
$today = date('Y-m-d');
$conn->query("
    INSERT INTO site_visitors (visit_date, hits)
    VALUES ('$today', 1)
    ON DUPLICATE KEY UPDATE hits = hits + 1
");

// Get total visitors
$result_visitors = $conn->query("SELECT SUM(hits) AS total_visitors FROM site_visitors");
$total_visitors = $result_visitors->fetch_assoc()['total_visitors'];

// Initialize variables
$limit = 10;
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 1;
$start_from = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = array();
$search_term = '';

// Build search condition
if (!empty($search)) {
    $search_term = "%$search%";
    $where = "WHERE medicine_name LIKE ? OR company_name LIKE ?";
    array_push($params, $search_term, $search_term);
}

// Base SQL queries
$base_sql = "SELECT * FROM medicines $where ORDER BY medicine_id, country";
$count_sql = "SELECT COUNT(*) AS total FROM medicines $where";

// Prepare and execute main query
$sql = "$base_sql LIMIT ?, ?";
array_push($params, $start_from, $limit);

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$types = str_repeat('s', count($params) - 2) . 'ii';
if (!empty($search)) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $start_from, $limit);
}

$stmt->execute();
$rs_result = $stmt->get_result();

// Prepare and execute count query
$count_stmt = $conn->prepare($count_sql);
if (!empty($search)) {
    $count_stmt->bind_param("ss", $search_term, $search_term);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch total products
$sql_products = "SELECT COUNT(*) AS total_products FROM medicines";
$result_products = $conn->query($sql_products);
$row_products = $result_products->fetch_assoc();
$total_products = $row_products['total_products'];

// Fetch total users
$sql_users = "SELECT COUNT(*) AS total_users FROM user_form";
$result_users = $conn->query($sql_users);
$row_users = $result_users->fetch_assoc();
$total_users = $row_users['total_users'];

// Fetch contact messages
$sql_messages = "SELECT * FROM contact_messages ORDER BY id DESC LIMIT 10";
$result_messages = $conn->query($sql_messages);
$messages = [];
while ($row = $result_messages->fetch_assoc()) {
    $messages[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Cure Connectors Admin</title>
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
            --success: #10b981;
            --warning: #f59e0b;
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
        }

        /* Sidebar */
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
            font-size: 1.5rem;
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
            font-size: 0.95rem;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text-primary);
        }

        .sidebar .add-product {
            background: var(--accent);
            color: white !important;
            margin-top: 0.5rem;
        }

        .sidebar .add-product:hover {
            background: var(--accent-hover);
        }

        .sidebar i {
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 0;
            padding: 2rem;
            transition: var(--transition);
            position: relative;
        }

        .main-content.shifted {
            margin-left: 280px;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 999;
            display: none;
            transition: var(--transition);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem 0;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        /* Hamburger Menu */
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
        }

        @media (min-width: 769px) {
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

        /* Search Container */
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
            font-size: 0.95rem;
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

        /* Search Dropdown */
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
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-dropdown a:hover {
            background: var(--surface);
        }

        /* Metrics Grid */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .card {
            background: var(--secondary-bg);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
        }

        .card h3 {
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .card p {
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        /* Data Table */
        table {
            width: 100%;
            background: var(--secondary-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            margin: 2rem 0;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
        }

        th {
            background: var(--surface);
            font-weight: 600;
        }

        /* Action Buttons */
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
            font-size: 0.9rem;
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

        /* Pagination */
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
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .pagination a.active {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }

        /* Country Dropdown */
        .country-dropdown {
            background: var(--surface);
            color: var(--text-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem;
            }

            .hamburger-menu {
                top: 0.25rem;
                left: 0.25rem;
            }

            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            th, td {
                min-width: 150px;
            }
        }
    </style>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');
            sidebar.classList.toggle('visible');
            overlay.style.display = sidebar.classList.contains('visible') ? 'block' : 'none';
        }

        // Enhanced touch handling
        let touchStartX = 0;
        const touchThreshold = 100;

        document.addEventListener('touchstart', e => {
            touchStartX = e.touches[0].clientX;
        });

        document.addEventListener('touchend', e => {
            const touchEndX = e.changedTouches[0].clientX;
            const deltaX = touchEndX - touchStartX;
            const sidebar = document.querySelector('.sidebar');

            if (Math.abs(deltaX) > touchThreshold) {
                if (deltaX > 0 && touchStartX < 50) {
                    sidebar.classList.add('visible');
                    document.querySelector('.overlay').style.display = 'block';
                } else if (deltaX < 0) {
                    sidebar.classList.remove('visible');
                    document.querySelector('.overlay').style.display = 'none';
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 769) {
                document.querySelector('.sidebar').classList.remove('visible');
                document.querySelector('.overlay').style.display = 'none';
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
                        row.querySelector(".name").innerText = medicine.medicine_name;
                        row.querySelector(".company").innerText = medicine.company_name;
                        row.querySelector(".price").innerText = medicine.price + " " + medicine.currency;
                        row.querySelector(".uses").innerText = medicine.uses;
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
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Cure Connectors</h2>
        <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="products.php"><i class="fas fa-box"></i> Products</a>
        <a href="add_medicine.php">
            <i class="fas fa-plus-circle"></i> Add New Product
        </a>
        <a href="show_contacts.php"><i class="fas fa-envelope"></i> Messages</a>
        <a href="user.php"><i class="fas fa-user"></i> Users</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="overlay" onclick="toggleSidebar()"></div>

    <div class="main-content">
        <button class="hamburger-menu" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="header">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search medicines..." 
                       value="<?= htmlspecialchars($search) ?>" 
                       onkeyup="showSuggestions(this.value)"
                       autocomplete="off"
                       aria-label="Search medicines">
                <button type="submit"><i class="fas fa-search"></i></button>
                <div id="suggestions" class="search-dropdown"></div>
            </form>
        </div>

        <div class="card-container">
            <div class="card">
                <h3>Total Products</h3>
                <p><?= $total_products ?></p>
            </div>
            <div class="card">
                <h3>Total Visitors</h3>
                <p><?= $total_visitors ?></p>
            </div>
            <div class="card">
                <h3>Total Users</h3>
                <p><?= $total_users ?></p>
            </div>
        </div>

        <?php if (!empty($search)): ?>
        <div class="search-results">
            <h2>Search Results for "<?= htmlspecialchars($search) ?>"</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Price</th>
                        <th>Uses</th>
                        <th>Added On</th>
                        <th>Country</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rs_result->num_rows > 0): ?>
                        <?php while ($row = $rs_result->fetch_assoc()): ?>
                            <tr>
                                <td class="name"><?= htmlspecialchars($row['medicine_name']) ?></td>
                                <td class="company"><?= htmlspecialchars($row['company_name']) ?></td>
                                <td class="price"><?= htmlspecialchars($row['price']) ?> <?= htmlspecialchars($row['currency']) ?></td>
                                <td class="uses"><?= htmlspecialchars($row['uses']) ?></td>
                                <td class="added"><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <select class="country-dropdown" onchange="fetchRowData(<?= $row['medicine_id'] ?>, this, this.closest('tr'))">
                                        <option value="Bangladesh" <?= $row['country'] === 'Bangladesh' ? 'selected' : '' ?>>Bangladesh</option>
                                        <option value="India" <?= $row['country'] === 'India' ? 'selected' : '' ?>>India</option>
                                        <option value="Malaysia" <?= $row['country'] === 'Malaysia' ? 'selected' : '' ?>>Malaysia</option>
                                        <option value="Singapore" <?= $row['country'] === 'Singapore' ? 'selected' : '' ?>>Singapore</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button class="btn btn-primary" onclick="openUpdateForm(<?= $row['medicine_id'] ?>, '<?= $row['country'] ?>')"
                                                aria-label="Edit medicine">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="confirmDelete(<?= $row['medicine_id'] ?>, '<?= $row['country'] ?>')"
                                                aria-label="Delete medicine">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No medicines found<?= !empty($search) ? ' matching your search' : '' ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                           class="<?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>