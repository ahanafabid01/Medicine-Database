<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['country'])) {
    $medicine_id = $_POST['id'];
    $country = $_POST['country'];

    $sql = "DELETE FROM medicines WHERE medicine_id = ? AND country = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $medicine_id, $country);

    if ($stmt->execute()) {
        echo "Medicine deleted successfully.";
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?><?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['country'])) {
    $medicine_id = $_POST['id'];
    $country = $_POST['country'];

    $sql = "DELETE FROM medicines WHERE medicine_id = ? AND country = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $medicine_id, $country);

    if ($stmt->execute()) {
        echo "Medicine deleted successfully.";
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>