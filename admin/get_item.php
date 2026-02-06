<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "shooping");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed"]));
}

if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    $stmt = $conn->prepare("SELECT item_name FROM item WHERE item_id = ?");
    $stmt->bind_param("s", $item_id);
    $stmt->execute();
    $stmt->bind_result($item_name);

    if ($stmt->fetch()) {
        echo json_encode(["success" => true, "item_name" => $item_name]);
    } else {
        echo json_encode(["success" => false, "message" => "Item not found"]);
    }

    $stmt->close();
}

$conn->close();
?>
