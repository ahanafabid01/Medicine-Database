<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['id'];

    // Delete query
    $sql = "DELETE FROM user_form WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Failed to delete the user.";
    }

    $stmt->close();
}

$conn->close();
?>