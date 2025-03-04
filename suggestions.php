<?php
include 'db_connect.php';

// Get search query with proper sanitization
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = array();

if (!empty($q)) {
    // Use prepared statement for security
    $search_term = "%" . $q . "%";
    $stmt = $conn->prepare("SELECT DISTINCT medicine_name FROM medicines WHERE medicine_name LIKE ? ORDER BY medicine_name ASC LIMIT 10");
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row['medicine_name'];
    }
    
    $stmt->close();
}

// Return proper HTML response
header('Content-Type: text/html; charset=utf-8');

if (!empty($results)) {
    foreach ($results as $medicine) {
        echo "<a href=\"searched_results.php?search=" . urlencode($medicine) . "\">" 
             . htmlspecialchars($medicine, ENT_QUOTES, 'UTF-8') . "</a>";
    }
} else {
    echo "<div class='no-results'>No suggestions found</div>";
}

$conn->close();
?>