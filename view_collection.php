<?php
require_once 'config.php';
$conn = getDBConnection();

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    header("Location: categories.php");
    exit();
}

// Get category info
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    setFlashMessage("Collection not found.", "error");
    header("Location: categories.php");
    exit();
}

// Handle removing word from collection
if (isset($_GET['remove_word'])) {
    $word_id = (int)$_GET['remove_word'];
    $stmt = $conn->prepare("DELETE FROM word_collections WHERE word_id = ? AND category_id = ?");
    $stmt->bind_param("ii", $word_id, $category_id);
    
    if ($stmt->execute()) {
        setFlashMessage("Word removed from collection!");
    }
    header("Location: view_collection.php?id=" . $category_id);
    exit();
}

// Get words in this collection
$sql = "SELECT w.* FROM words w 
        INNER JOIN word_collections wc ON w.id = wc.word_id 
        WHERE wc.category_id = ? 
        ORDER BY w.word_name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['category_name']); ?> - Dictionary</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📚 My Dictionary</h1>
            <nav>
                <a href="index.php">Dictionary</a>
                <a href="categories.php">My Collections</a>
                <a href="add_word.php">+ Add Word</a>
            </nav>
        </header>

        <main>
            <?php displayFlashMessage(); ?>

            <div class="page-header">
                <div>
                    <a href="categories.php" class="back-link">← Back to Collections</a>
                    <h2><?php echo htmlspecialchars($category['category_name']); ?></h2>
                    <?php if (!empty($category['description'])): ?>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stats">
                <p>Words in this collection: <strong><?php echo $result->num_rows; ?></strong></p>
            </div>

            <div class="words-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($word = $result->fetch_assoc()): ?>
                        <div class="word-card">
                            <div class="word-header">
                                <h2><?php echo htmlspecialchars($word['word_name']); ?></h2>
                                <div class="word-actions">
                                    <a href="edit_word.php?id=<?php echo $word['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="view_collection.php?id=<?php echo $category_id; ?>&remove_word=<?php echo $word['id']; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Remove this word from the collection?')">Remove</a>
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
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results">
                        <p>No words in this collection yet. <a href="index.php">Browse the dictionary</a> and add words to this collection!</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> My Dictionary - Learn & Organize Words</p>
        </footer>
    </div>
</body>
</html>
<?php $conn->close(); ?>
