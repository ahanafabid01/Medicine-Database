<?php
include 'db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

    // Update query
    $sql = "UPDATE user_form SET name=?, email=?, user_type=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $user_type, $user_id);

    $response = ['status' => 'error', 'message' => 'Unknown error'];
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Get updated record
            $get_sql = "SELECT * FROM user_form WHERE id=?";
            $get_stmt = $conn->prepare($get_sql);
            $get_stmt->bind_param("i", $user_id);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            $user = $result->fetch_assoc();
            $get_stmt->close();

            $response = [
                'status' => 'success',
                'message' => 'User updated successfully!',
                'data' => $user
            ];
        } else {
            $response = ['status' => 'info', 'message' => 'No changes made'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();

    // Handle AJAX response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        $conn->close();
        exit;
    }
}

// Fetch user details
$user = null;
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql = "SELECT * FROM user_form WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f5fa;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background-color: #fff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar h2 {
            margin-bottom: 30px;
            color: #5a67d8;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px 10px;
            color: #333;
            text-decoration: none;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #5a67d8;
            color: #fff;
        }

        .sidebar i {
            margin-right: 15px;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .main-content.full-width {
            margin-left: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
            padding-left: 5px;
        }

        .header form {
            display: flex;
            align-items: center;
        }

        .header input[type="text"] {
            padding: 10px;
            width: 300px;
            border-radius: 8px 0 0 8px;
            border: 1px solid #ccc;
            border-right: none;
        }

        .header button {
            background: none;
            border: 1px solid #ccc;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            padding: 10px;
        }

        .header button i {
            font-size: 20px;
            color: #333;
        }

        .header .icons i {
            margin-left: 20px;
            font-size: 20px;
            cursor: pointer;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container form .form-group {
            margin-bottom: 15px;
        }

        .form-container form label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-container form input,
        .form-container form select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 100%;
            box-sizing: border-box;
        }

        .form-container form button {
            padding: 10px;
            border-radius: 8px;
            border: none;
            background-color: #5a67d8;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-container form button:hover {
            background-color: #434190;
        }

        .success-message,
        .error-message,
        .info-message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #fff;
        }

        .success-message {
            background-color: #48bb78;
        }

        .error-message {
            background-color: #e53e3e;
        }

        .info-message {
            background-color: #4299e1;
        }

        .toggle-sidebar {
            display: none;
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.hidden {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 20px;
            }

            .main-content.full-width {
                margin-left: 20px;
            }

            .toggle-sidebar {
                display: block;
            }
        }
    </style>
    <script>
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

        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            var mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('full-width');
        }

        // AJAX Form Submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const xhr = new XMLHttpRequest();
                    
                    xhr.open('POST', 'update_user.php', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                
                                // Clear existing messages
                                document.querySelectorAll('.success-message, .error-message, .info-message').forEach(el => el.remove());
                                
                                // Create message element
                                const msgDiv = document.createElement('div');
                                msgDiv.className = `${response.status}-message`;
                                msgDiv.textContent = response.message;
                                
                                // Insert message
                                const formContainer = document.querySelector('.form-container');
                                if (formContainer.querySelector('h1')) {
                                    formContainer.insertBefore(msgDiv, formContainer.querySelector('h1').nextSibling);
                                } else {
                                    formContainer.prepend(msgDiv);
                                }
                                
                                // Update form fields if success
                                if (response.status === 'success' && response.data) {
                                    document.getElementById('name').value = response.data.name;
                                    document.getElementById('email').value = response.data.email;
                                    document.getElementById('user_type').value = response.data.user_type;
                                }
                            } catch (error) {
                                console.error('Error parsing response:', error);
                            }
                        }
                    };
                    
                    xhr.send(formData);
                });
            }
        });
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Cure Connectors</h2>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="products.php"><i class="fas fa-box"></i> Products</a>
        <a href="add_medicine.php"><i class="fas fa-shopping-cart"></i>Add New product</a>
        <a href="user.php" class="active"><i class="fas fa-user"></i> Users</a>
        <a href="logout.php"><i class="fas fa-user"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="toggle-sidebar" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="header">
            <form method="GET" action="search_results.php">
                <input type="text" name="search" placeholder="Search" onkeyup="showSuggestions(this.value)">
                <button type="submit"><i class="fas fa-search"></i></button>
                <div id="suggestions" class="search-dropdown"></div>
            </form>
            <div class="icons">
                <i class="fas fa-comment"></i>
                <i class="fas fa-bell"></i>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>

        <div class="form-container">
            <h1>Update User</h1>

            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?= $success_message ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?= $error_message ?></div>
            <?php endif; ?>

            <?php if ($user): ?>
                <form method="POST" action="update_user.php">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
                    
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_type">User Type:</label>
                        <select id="user_type" name="user_type" required>
                            <option value="user" <?= $user['user_type'] == 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['user_type'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Update User</button>
                </form>
            <?php else: ?>
                <div class="error-message">No user found!</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>