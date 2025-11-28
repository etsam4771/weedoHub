<?php
require_once 'config.php';
$conn = getDBConnection();

$word_id = isset($_GET['word_id']) ? (int)$_GET['word_id'] : 0;

if ($word_id <= 0) {
    header("Location: index.php");
    exit();
}

// Get word info
$stmt = $conn->prepare("SELECT word_name FROM words WHERE id = ?");
$stmt->bind_param("i", $word_id);
$stmt->execute();
$result = $stmt->get_result();
$word = $result->fetch_assoc();

if (!$word) {
    setFlashMessage("Word not found.", "error");
    header("Location: index.php");
    exit();
}

// Handle adding to collection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
    $category_id = (int)$_POST['category_id'];
    
    // Check if already exists
    $stmt = $conn->prepare("SELECT id FROM word_collections WHERE word_id = ? AND category_id = ?");
    $stmt->bind_param("ii", $word_id, $category_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    
    if ($exists) {
        setFlashMessage("Word is already in this collection!", "error");
    } else {
        $stmt = $conn->prepare("INSERT INTO word_collections (word_id, category_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $word_id, $category_id);
        
        if ($stmt->execute()) {
            setFlashMessage("Word added to collection successfully!");
        } else {
            setFlashMessage("Error adding word to collection.", "error");
        }
    }
    
    header("Location: index.php");
    exit();
}

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

// Get categories this word is already in
$stmt = $conn->prepare("SELECT category_id FROM word_collections WHERE word_id = ?");
$stmt->bind_param("i", $word_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_categories = [];
while ($row = $result->fetch_assoc()) {
    $existing_categories[] = $row['category_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to Collection - Dictionary</title>
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
                <a href="bulk_upload.php">📤 Bulk Upload</a>
                <a href="collection_bulk_upload.php">📦 Collection Upload</a>
            </nav>
        </header>

        <main>
            <div class="form-container">
                <h2>Add "<?php echo htmlspecialchars($word['word_name']); ?>" to Collection</h2>
                
                <?php if ($categories->num_rows > 0): ?>
                    <form method="POST" action="add_to_collection.php?word_id=<?php echo $word_id; ?>" class="collection-form">
                        <div class="form-group">
                            <label>Select Collection:</label>
                            <div class="collection-list">
                                <?php while($category = $categories->fetch_assoc()): ?>
                                    <label class="collection-item">
                                        <input type="radio" 
                                               name="category_id" 
                                               value="<?php echo $category['id']; ?>"
                                               <?php echo in_array($category['id'], $existing_categories) ? 'disabled' : ''; ?>
                                               required>
                                        <span><?php echo htmlspecialchars($category['category_name']); ?>
                                            <?php if (in_array($category['id'], $existing_categories)): ?>
                                                <em>(Already added)</em>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Add to Collection</button>
                            <a href="index.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="no-results">
                        <p>No collections found. <a href="categories.php">Create a collection first!</a></p>
                    </div>
                    <div class="form-actions">
                        <a href="index.php" class="btn-secondary">Back to Dictionary</a>
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
