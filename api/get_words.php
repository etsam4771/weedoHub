<?php
require_once '../config.php';
$conn = getDBConnection();

// Get parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20; // Load 20 items at a time for infinite scroll
$offset = ($page - 1) * $perPage;

// Build query
$searchQuery = '';
$params = [];
$types = '';

if (!empty($search)) {
    $searchQuery = "WHERE word_name LIKE ? OR meaning_hindi LIKE ? OR meaning_english LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = 'sss';
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM words $searchQuery";
if (!empty($params)) {
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $totalResult = $stmt->get_result();
} else {
    $totalResult = $conn->query($countSql);
}
$totalWords = $totalResult->fetch_assoc()['total'];

// Get words
$sql = "SELECT * FROM words $searchQuery ORDER BY word_name ASC LIMIT ? OFFSET ?";
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Prepare response
$words = [];
while ($word = $result->fetch_assoc()) {
    $words[] = $word;
}

$response = [
    'success' => true,
    'words' => $words,
    'total' => $totalWords,
    'page' => $page,
    'hasMore' => ($offset + $perPage) < $totalWords
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
