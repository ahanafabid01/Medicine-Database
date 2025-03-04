<?php
include 'db_connect.php';

if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicine_id   = $_POST['medicine_id'] ?: null;
    $medicine_name = $_POST['medicine_name'];
    $company_name  = $_POST['company_name'];
    $price         = $_POST['price'];
    $currency      = $_POST['currency'];
    $uses          = $_POST['uses'];
    $country       = $_POST['country'];
    $more_info     = $_POST['more_info'];
    $created_at    = date('Y-m-d H:i:s');

    if ($medicine_id === null) {
        $stmt = $conn->prepare("SELECT MAX(medicine_id) + 1 AS new_id FROM medicines");
        $stmt->execute();
        $stmt->bind_result($new_id);
        $stmt->fetch();
        $stmt->close();
        $medicine_id = $new_id ?? 1;
    }

    $sql = "INSERT INTO medicines (medicine_id, medicine_name, company_name, price, currency, uses, country, more_info, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $medicine_id, $medicine_name, $company_name, $price, $currency, $uses, $country, $more_info, $created_at);

    if ($stmt->execute()) {
        $success_message = "Medicine added successfully! Medicine ID: " . $medicine_id;

        if (!empty($_FILES['images']['name'][0])) {
            $uploadErrors = [];
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $error    = $_FILES['images']['error'][$key];
                $fileName = $_FILES['images']['name'][$key];
                
                if ($error === UPLOAD_ERR_OK) {
                    $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($fileExt, $allowedExts)) {
                        $newFileName = uniqid('img_', true) . '.' . $fileExt;
                        $uploadPath  = 'uploads/' . $newFileName;
                        
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $imgSql = "INSERT INTO medicine_images (medicine_id, image_path) VALUES (?, ?)";
                            $imgStmt = $conn->prepare($imgSql);
                            $imgStmt->bind_param("is", $medicine_id, $uploadPath);
                            $imgStmt->execute();
                            $imgStmt->close();
                        } else {
                            $uploadErrors[] = "Failed to upload $fileName";
                        }
                    } else {
                        $uploadErrors[] = "Invalid file type: $fileName";
                    }
                } else {
                    $uploadErrors[] = "Error uploading $fileName (Code: $error)";
                }
            }
            if (!empty($uploadErrors)) {
                $error_message = "Medicine added but with image errors: " . implode(', ', $uploadErrors);
            }
        }
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Add New Medicine - Cure Connectors</title>
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

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 999;
            display: none;
        }

        .main-content {
            margin-left: 0;
            padding: 2rem;
            transition: var(--transition);
            position: relative;
            min-height: 100vh;
        }

        .main-content.shifted {
            margin-left: 280px;
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

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            background: var(--secondary-bg);
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
        }

        .form-container h1 {
            margin-bottom: 2rem;
            color: var(--text-primary);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.875rem 1rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--accent);
            outline: none;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            padding: 0.5rem;
            background: transparent;
            border: none;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn:hover {
            background: var(--accent-hover);
        }

        .success-message {
            background: var(--success);
            color: white;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
        }

        .error-message {
            background: var(--danger);
            color: white;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
        }

        small.text-muted {
            color: var(--text-secondary);
            font-size: 0.85rem;
            display: block;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem;
            }
            .form-container {
                padding: 1.5rem;
            }
            .form-container h1 {
                font-size: 1.5rem;
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

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 769) {
                document.querySelector('.sidebar').classList.remove('visible');
                document.querySelector('.overlay').style.display = 'none';
            }
        });
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Cure Connectors</h2>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="products.php"><i class="fas fa-box"></i> Products</a>
        <a href="add_medicine.php" class="active add-product"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <a href="show_contacts.php"><i class="fas fa-envelope"></i> Messages</a>
        <a href="user.php"><i class="fas fa-user"></i> Users</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="overlay" onclick="toggleSidebar()"></div>

    <div class="main-content">
        <button class="hamburger-menu" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="form-container">
            <h1><i class="fas fa-pills"></i> Add New Medicine</h1>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="add_medicine.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="medicine_id"><i class="fas fa-id-badge"></i> Medicine ID:</label>
                    <input type="number" id="medicine_id" name="medicine_id" placeholder="Auto-generates if empty">
                </div>

                <div class="form-group">
                    <label for="medicine_name"><i class="fas fa-prescription-bottle"></i> Medicine Name:</label>
                    <input type="text" id="medicine_name" name="medicine_name" required>
                </div>

                <div class="form-group">
                    <label for="company_name"><i class="fas fa-building"></i> Company Name:</label>
                    <input type="text" id="company_name" name="company_name" required>
                </div>

                <div class="form-group">
                    <label for="price"><i class="fas fa-tag"></i> Price:</label>
                    <input type="text" id="price" name="price" required>
                </div>

                <div class="form-group">
                    <label for="currency"><i class="fas fa-coins"></i> Currency:</label>
                    <input type="text" id="currency" name="currency" value="BDT" required>
                </div>

                <div class="form-group">
                    <label for="uses"><i class="fas fa-file-medical"></i> Uses:</label>
                    <textarea id="uses" name="uses" required></textarea>
                </div>

                <div class="form-group">
                    <label for="more_info"><i class="fas fa-info-circle"></i> More Information:</label>
                    <textarea id="more_info" name="more_info" placeholder="Enter additional details..."></textarea>
                </div>

                <div class="form-group">
                    <label for="country"><i class="fas fa-globe"></i> Country:</label>
                    <select id="country" name="country" required>
                        <option value="Bangladesh">Bangladesh</option>
                        <option value="India">India</option>
                        <option value="Malaysia">Malaysia</option>
                        <option value="Singapore">Singapore</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="images"><i class="fas fa-image"></i> Upload Images:</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*">
                    <small class="text-muted">Supported formats: JPEG, PNG, GIF</small>
                </div>

                <button type="submit" class="btn"><i class="fas fa-plus-circle"></i> Add Medicine</button>
            </form>
        </div>
    </div>
</body>
</html>