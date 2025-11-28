<?php
require_once 'config.php';
$conn = getDBConnection();

// Handle search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$searchQuery = '';
$params = [];

if (!empty($search)) {
    $searchQuery = "WHERE word_name LIKE ? OR meaning_hindi LIKE ? OR meaning_english LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total count
$countSql = "SELECT COUNT(*) as total FROM words $searchQuery";
if (!empty($params)) {
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param("sss", ...$params);
    $stmt->execute();
    $totalResult = $stmt->get_result();
} else {
    $totalResult = $conn->query($countSql);
}
$totalWords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalWords / $perPage);

// Get words
$sql = "SELECT * FROM words $searchQuery ORDER BY word_name ASC LIMIT ? OFFSET ?";
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $params[] = $perPage;
    $params[] = $offset;
    $stmt->bind_param("sssii", ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictionary - Learn Words</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📚 My Dictionary</h1>
            <nav>
                <a href="index.php" class="active">Dictionary</a>
                <a href="categories.php">My Collections</a>
                <a href="add_word.php" class="btn-primary">+ Add Word</a>
            </nav>
        </header>

        <main>
            <?php displayFlashMessage(); ?>

            <div class="search-section">
                <form method="GET" action="index.php" class="search-form">
                    <input type="text" 
                           name="search" 
                           placeholder="Search words, meanings..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="search-input">
                    <button type="submit" class="btn-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="index.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="stats">
                <p>Total Words: <strong><?php echo $totalWords; ?></strong></p>
            </div>

            <div class="words-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($word = $result->fetch_assoc()): ?>
                        <div class="word-card">
                            <div class="word-header">
                                <h2><?php echo htmlspecialchars($word['word_name']); ?></h2>
                                <div class="word-actions">
                                    <a href="edit_word.php?id=<?php echo $word['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="delete_word.php?id=<?php echo $word['id']; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this word?')">Delete</a>
                                </div>
                            </div>
                            
                            <div class="meanings">
                                <div class="meaning-section">
                                    <span class="label">Hindi:</span>
                                    <span class="meaning"><?php echo htmlspecialchars($word['meaning_hindi']); ?></span>
                                </div>
                                <div class="meaning-section">
                                    <span class="label">English:</span>
                                    <span class="meaning"><?php echo htmlspecialchars($word['meaning_english']); ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($word['example'])): ?>
                                <div class="example">
                                    <span class="label">Example:</span>
                                    <p><?php echo htmlspecialchars($word['example']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="word-footer">
                                <a href="add_to_collection.php?word_id=<?php echo $word['id']; ?>" class="btn-add-collection">
                                    + Add to Collection
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results">
                        <p>No words found. <?php echo !empty($search) ? 'Try a different search.' : '<a href="add_word.php">Add your first word!</a>'; ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn-secondary">← Previous</a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn-secondary">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> My Dictionary - Learn & Organize Words</p>
        </footer>
    </div>
</body>
</html>
<?php $conn->close(); ?>
