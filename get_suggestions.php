
<?php
include 'db_connect.php';

$q = $_GET['q'] ?? '';
if (empty($q)) exit;

$searchTerm = "%$q%";
$stmt = $conn->prepare("SELECT DISTINCT medicine_name FROM medicines WHERE medicine_name LIKE ? ORDER BY medicine_name LIMIT 10");
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $medicineName = htmlspecialchars($row['medicine_name']);
    echo "<a href=\"search_results.php?search=" . urlencode($row['medicine_name']) . "\">$medicineName</a>";
}

$stmt->close();
$conn->close();
?>