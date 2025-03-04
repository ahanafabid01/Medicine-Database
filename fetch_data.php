<?php
include 'db_connect.php';

// Check for specific requests
if (isset($_GET['medicine_id']) && isset($_GET['country'])) {
    $medicine_id = $_GET['medicine_id'];
    $country = $_GET['country'];

    $sql = "SELECT medicine_name, company_name, price, currency, uses, created_at FROM medicines WHERE medicine_id = ? AND country = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $medicine_id, $country);
} else {
    // General request to fetch all medicines
    $sql = "SELECT medicine_id, medicine_name, company_name, price, currency, uses, created_at, country FROM medicines";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
