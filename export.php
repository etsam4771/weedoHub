<?php
require_once 'config.php';
$conn = getDBConnection();

$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'all';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="dictionary_export_' . date('Y-m-d_H-i-s') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel display of Unicode characters
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV header
fputcsv($output, ['Word Name', 'Hindi Meaning', 'English Meaning', 'Example', 'Created At']);

// Fetch words based on type
if ($type === 'collection' && $category_id > 0) {
    // Export words from specific collection
    $sql = "SELECT w.word_name, w.meaning_hindi, w.meaning_english, w.example, w.created_at 
            FROM words w
            INNER JOIN word_collections wc ON w.id = wc.word_id
            WHERE wc.category_id = ?
            ORDER BY w.word_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Export all words
    $sql = "SELECT word_name, meaning_hindi, meaning_english, example, created_at 
            FROM words 
            ORDER BY word_name ASC";
    $result = $conn->query($sql);
}

// Write data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['word_name'],
        $row['meaning_hindi'],
        $row['meaning_english'],
        $row['example'],
        $row['created_at']
    ]);
}

fclose($output);
$conn->close();
exit();
?>
