<?php
require_once 'config.php';
$conn = getDBConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Check if word is in any collections
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM word_collections WHERE word_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        // Remove from collections first
        $stmt = $conn->prepare("DELETE FROM word_collections WHERE word_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    
    // Delete the word
    $stmt = $conn->prepare("DELETE FROM words WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        setFlashMessage("Word deleted successfully!");
    } else {
        setFlashMessage("Error deleting word.", "error");
    }
}

header("Location: index.php");
exit();
?>
