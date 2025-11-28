<?php
require_once '../config.php';
$conn = getDBConnection();

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

if ($category_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid category']);
    exit();
}

// Get words in this collection
$sql = "SELECT w.* FROM words w 
        INNER JOIN word_collections wc ON w.id = wc.word_id 
        WHERE wc.category_id = ? 
        ORDER BY w.word_name ASC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $category_id, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get total count
$countSql = "SELECT COUNT(*) as total FROM word_collections WHERE category_id = ?";
$stmt = $conn->prepare($countSql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$totalResult = $stmt->get_result();
$total = $totalResult->fetch_assoc()['total'];

$words = [];
while ($word = $result->fetch_assoc()) {
    $words[] = $word;
}

$response = [
    'success' => true,
    'words' => $words,
    'total' => $total,
    'page' => $page,
    'hasMore' => ($offset + $perPage) < $total
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
