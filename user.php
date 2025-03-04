<?php
include 'db_connect.php';

// Pagination setup
$limit = 17;
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 1;
$start_from = ($page - 1) * $limit;

// Fetch paginated data
$stmt = $conn->prepare("SELECT * FROM user_form ORDER BY id LIMIT ?, ?");
$stmt->bind_param("ii", $start_from, $limit);
$stmt->execute();
$rs_result = $stmt->get_result();

// Get total records
$count_sql = "SELECT COUNT(*) AS total FROM user_form";
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
    <title>User Information</title>
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

        .sidebar a:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text-primary);
        }

        .main-content {
            margin-left: 0;
            padding: 2rem;
            transition: var(--transition);
            position: relative;
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

        .table-container {
            background: var(--secondary-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            overflow-x: auto;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            letter-spacing: -0.5px;
            margin-bottom: 2rem;
            color: var(--text-primary);
            position: relative;
            padding-bottom: 0.5rem;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
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

        tr:hover {
            background: rgba(255,255,255,0.02);
        }

        .delete-btn {
            padding: 0.5rem 1rem;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
        }

        .delete-btn:hover {
            opacity: 0.9;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            background: var(--secondary-bg);
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .pagination a.active {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
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

        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            table {
                min-width: 600px;
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

        function confirmDelete(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                fetch(`delete_user.php`, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${userId}`
                })
                .then(response => {
                    if (response.ok) {
                        alert("User deleted successfully.");
                        window.location.reload();
                    } else {
                        alert("Failed to delete the user.");
                    }
                })
                .catch(error => console.error("Error deleting user:", error));
            }
        }
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

    <div class="overlay" onclick="toggleSidebar()"></div>

    <div class="main-content">
        <button class="hamburger-menu" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="header">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search" 
                       onkeyup="showSuggestions(this.value)"
                       autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
                <div id="suggestions" class="search-dropdown"></div>
            </form>
            <div class="icons">
                <i class="fas fa-comment"></i>
                <i class="fas fa-bell"></i>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>

        <div class="table-container">
            <h1>User Information</h1>
            <table id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $rs_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["id"]) ?></td>
                        <td><?= htmlspecialchars($row["name"]) ?></td>
                        <td><?= htmlspecialchars($row["email"]) ?></td>
                        <td><?= htmlspecialchars($row["user_type"]) ?></td>
                        <td><?= htmlspecialchars($row["created_at"]) ?></td>
                        <td>
                            <button class="delete-btn" onclick="confirmDelete(<?= $row['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
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