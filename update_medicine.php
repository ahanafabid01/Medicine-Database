<?php
include 'db_connect.php';

// Create uploads directory if it does not exist
if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}

// Initialize default response
$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicine_id   = (int) $_POST['medicine_id'];
    $medicine_name = $_POST['medicine_name'];
    $company_name  = $_POST['company_name'];
    $price         = $_POST['price'];
    $currency      = $_POST['currency'];
    $uses          = $_POST['uses'];
    $country       = $_POST['country'];
    $more_info     = $_POST['more_info'];

    // Update the medicine record
    $sql = "UPDATE medicines 
            SET medicine_name = ?, company_name = ?, price = ?, currency = ?, uses = ?, more_info = ? 
            WHERE medicine_id = ? AND country = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssis", $medicine_name, $company_name, $price, $currency, $uses, $more_info, $medicine_id, $country);

    if ($stmt->execute()) {
        $updateAffectedRows = $stmt->affected_rows;
        $stmt->close();

        $imagesUploaded = false;
        $uploadErrors = [];

        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $error    = $_FILES['images']['error'][$key];
                $fileName = $_FILES['images']['name'][$key];

                if ($error === UPLOAD_ERR_OK) {
                    $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (in_array($fileExt, $allowedExts)) {
                        $newFileName = uniqid('img_', true) . '.' . $fileExt;
                        $uploadPath  = 'uploads/' . $newFileName;

                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $imgSql = "INSERT INTO medicine_images (medicine_id, image_path) VALUES (?, ?)";
                            $imgStmt = $conn->prepare($imgSql);
                            $imgStmt->bind_param("is", $medicine_id, $uploadPath);
                            $imgStmt->execute();
                            if ($imgStmt->affected_rows > 0) {
                                $imagesUploaded = true;
                            }
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
        }

        if (!empty($uploadErrors)) {
            $response = ['status' => 'error', 'message' => implode(', ', $uploadErrors)];
        }
        elseif ($updateAffectedRows > 0 || $imagesUploaded) {
            $get_sql = "SELECT * FROM medicines WHERE medicine_id = ? AND country = ?";
            $get_stmt = $conn->prepare($get_sql);
            $get_stmt->bind_param("is", $medicine_id, $country);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            $medicine = $result->fetch_assoc();
            $get_stmt->close();

            $response = [
                'status' => 'success',
                'message' => 'Medicine updated successfully!',
                'data' => $medicine
            ];
        } else {
            $response = ['status' => 'info', 'message' => 'No changes made'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Error: ' . $stmt->error];
        $stmt->close();
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        $conn->close();
        exit;
    }
}

$medicine = null;
if (isset($_GET['id']) && isset($_GET['country'])) {
    $medicine_id = (int) $_GET['id'];
    $country = $_GET['country'];

    $sql = "SELECT * FROM medicines WHERE medicine_id = ? AND country = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $medicine_id, $country);
    $stmt->execute();
    $result = $stmt->get_result();
    $medicine = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Update Medicine</title>
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
      --heading-gradient: linear-gradient(45deg,rgb(70, 49, 146), #c7d2fe);
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

    .form-container {
      max-width: 800px;
      margin: 0 auto;
      background: var(--secondary-bg);
      padding: 2rem;
      border-radius: var(--radius-lg);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-lg);
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--text-secondary);
      font-size: 0.95rem;
    }

    input, textarea, select {
      width: 100%;
      padding: 0.875rem;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      color: var(--text-primary);
      font-size: 0.95rem;
      transition: var(--transition);
    }

    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }

    textarea {
      min-height: 120px;
      resize: vertical;
    }

    .btn {
      padding: 0.875rem 1.5rem;
      background: var(--accent);
      color: white;
      border: none;
      border-radius: var(--radius-md);
      cursor: pointer;
      transition: var(--transition);
      font-size: 1rem;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn:hover {
      background: var(--accent-hover);
    }

    .country-switcher {
      margin-bottom: 2rem;
      padding: 1rem;
      background: var(--surface);
      border-radius: var(--radius-md);
    }

    .country-switcher select {
      width: auto;
      margin-left: 1rem;
      padding: 0.5rem 1rem;
      background: var(--secondary-bg);
      color: var(--text-primary);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
    }

    .success-message, .error-message, .info-message {
      padding: 1rem;
      margin-bottom: 1.5rem;
      border-radius: var(--radius-md);
    }

    .success-message {
      background: #059669;
      color: white;
    }

    .error-message {
      background: var(--danger);
      color: white;
    }

    .info-message {
      background: #3b82f6;
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
      
      .form-container {
        padding: 1.5rem;
      }
      
      .country-switcher {
        flex-direction: column;
        gap: 1rem;
      }
      
      .country-switcher select {
        margin-left: 0;
        width: 100%;
      }
      
      .header {
        flex-direction: column;
      }
      
      .header .icons {
        order: 3;
        margin-top: 1rem;
      }
    }
    h1 {
      font-size: 2.5rem;
      font-weight: 600;
      letter-spacing: -0.5px;
      margin-bottom: 2rem;
      position: relative;
      color: var(--heading-gradient);
      display: inline-block;
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
      transition: width 0.3s ease;
    }

    h1:hover::after {
      width: 100px;
    }

    @media (max-width: 768px) {
      h1 {
        font-size: 2rem;
        letter-spacing: -0.25px;
      }
      
      h1::after {
        width: 40px;
        height: 2px;
      }
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
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-dropdown a:hover {
            background: var(--surface);
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

    function switchCountry(select) {
      const newCountry = select.value;
      const medicineId = <?= isset($_GET['id']) ? $_GET['id'] : 'null' ?>;
      if (medicineId && newCountry) {
        window.location.href = `update_medicine.php?id=${medicineId}&country=${newCountry}`;
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form[method="POST"]');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          const xhr = new XMLHttpRequest();
          
          xhr.open('POST', 'update_medicine.php', true);
          xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          
          xhr.onload = function() {
            if (xhr.status === 200) {
              try {
                const response = JSON.parse(xhr.responseText);
                
                document.querySelectorAll('.success-message, .error-message, .info-message').forEach(el => el.remove());
                
                const msgDiv = document.createElement('div');
                msgDiv.className = `${response.status}-message`;
                msgDiv.textContent = response.message;
                
                const formContainer = document.querySelector('.form-container');
                if (formContainer.querySelector('h1')) {
                  formContainer.insertBefore(msgDiv, formContainer.querySelector('h1').nextSibling);
                } else {
                  formContainer.prepend(msgDiv);
                }
                
                if (response.status === 'success' && response.data) {
                  document.getElementById('medicine_name').value = response.data.medicine_name;
                  document.getElementById('company_name').value = response.data.company_name;
                  document.getElementById('price').value = response.data.price;
                  document.getElementById('currency').value = response.data.currency;
                  document.getElementById('uses').value = response.data.uses;
                  document.getElementById('more_info').value = response.data.more_info;
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
      <form method="GET" action="search_results.php">
        <input type="text" name="search" placeholder="Search" 
               onkeyup="showSuggestions(this.value)"
               autocomplete="off">
        <button type="submit"><i class="fas fa-search"></i></button>
        <div id="suggestions" class="search-dropdown"></div>
      </form>
    </div>

    <div class="form-container">
      <h1>Update Medicine</h1>
      
      <div class="country-switcher">
        <label>Select Country: </label>
        <select onchange="switchCountry(this)">
          <?php
          $countries = ['Bangladesh', 'India', 'Malaysia', 'Singapore'];
          foreach ($countries as $c) {
              $selected = (isset($_GET['country']) && $c == $_GET['country']) ? 'selected' : '';
              echo "<option value='$c' $selected>$c</option>";
          }
          ?>
        </select>
      </div>

      <?php if ($medicine): ?>
        <form method="POST" action="update_medicine.php" enctype="multipart/form-data">
          <input type="hidden" name="medicine_id" value="<?= htmlspecialchars($medicine['medicine_id']) ?>">
          <input type="hidden" name="country" value="<?= htmlspecialchars($country) ?>">
          
          <div class="form-group">
            <label>Country:</label>
            <span class="current-country"><?= htmlspecialchars($country) ?></span>
          </div>
          
          <div class="form-group">
            <label for="medicine_name">Medicine Name:</label>
            <input type="text" id="medicine_name" name="medicine_name" 
                   value="<?= htmlspecialchars($medicine['medicine_name']) ?>" required>
          </div>
          
          <div class="form-group">
            <label for="company_name">Company Name:</label>
            <input type="text" id="company_name" name="company_name" 
                   value="<?= htmlspecialchars($medicine['company_name']) ?>">
          </div>
          
          <div class="form-group">
            <label for="price">Price:</label>
            <input type="text" id="price" name="price" 
                   value="<?= htmlspecialchars($medicine['price']) ?>" required>
          </div>
          
          <div class="form-group">
            <label for="currency">Currency:</label>
            <input type="text" id="currency" name="currency" 
                   value="<?= htmlspecialchars($medicine['currency']) ?>" required>
          </div>
          
          <div class="form-group">
            <label for="uses">Uses:</label>
            <textarea id="uses" name="uses" required><?= htmlspecialchars($medicine['uses']) ?></textarea>
          </div>
          
          <div class="form-group">
            <label for="more_info">More Information:</label>
            <textarea id="more_info" name="more_info"><?= htmlspecialchars($medicine['more_info']) ?></textarea>
          </div>
          
          <div class="form-group">
            <label for="images">Upload Images:</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
            <small>Select multiple images (JPEG, PNG, GIF)</small>
          </div>
          
          <button type="submit" class="btn">
            <i class="fas fa-sync-alt"></i> Update Medicine
          </button>
        </form>
      <?php else: ?>
        <div class="error-message">No medicine found for selected country!</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
