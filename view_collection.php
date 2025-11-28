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

$conn->close();
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
                <p>Words in this collection: <strong id="totalWords">0</strong></p>
            </div>

            <div id="wordsContainer" class="words-grid">
                <!-- Words will be loaded here via AJAX -->
            </div>

            <div id="loading" class="loading" style="display:none;">
                <div class="spinner"></div>
                <p>Loading more words...</p>
            </div>

            <div id="noMoreResults" class="no-results" style="display:none;">
                <p>No more words to load.</p>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> My Dictionary - Learn & Organize Words</p>
        </footer>
    </div>

    <script>
        const categoryId = <?php echo $category_id; ?>;
    </script>
    <script src="collection_script.js"></script>
</body>
</html>
