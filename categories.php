<?php
require_once 'config.php';
$conn = getDBConnection();

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_category') {
    $category_name = sanitizeInput($_POST['category_name']);
    $description = sanitizeInput($_POST['description']);
    
    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $category_name, $description);
        
        if ($stmt->execute()) {
            setFlashMessage("Collection created successfully!");
        } else {
            setFlashMessage("Error creating collection. Name might already exist.", "error");
        }
        header("Location: categories.php");
        exit();
    }
}

// Handle category deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        setFlashMessage("Collection deleted successfully!");
    } else {
        setFlashMessage("Error deleting collection.", "error");
    }
    header("Location: categories.php");
    exit();
}

// Get all categories with word counts
$sql = "SELECT c.*, COUNT(wc.word_id) as word_count 
        FROM categories c 
        LEFT JOIN word_collections wc ON c.id = wc.category_id 
        GROUP BY c.id 
        ORDER BY c.category_name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Collections - Dictionary</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📚 My Dictionary</h1>
            <nav>
                <a href="index.php">Dictionary</a>
                <a href="categories.php" class="active">My Collections</a>
                <a href="add_word.php">+ Add Word</a>
                <a href="bulk_upload.php">📤 Bulk Upload</a>
                <a href="collection_bulk_upload.php">📦 Collection Upload</a>
            </nav>
        </header>

        <main>
            <?php displayFlashMessage(); ?>

            <div class="page-header">
                <h2>My Word Collections</h2>
                <button onclick="document.getElementById('createModal').style.display='block'" class="btn-primary">
                    + Create Collection
                </button>
            </div>

            <div class="categories-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($category = $result->fetch_assoc()): ?>
                        <div class="category-card">
                            <div class="category-header">
                                <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                                <span class="word-count"><?php echo $category['word_count']; ?> words</span>
                            </div>
                            
                            <?php if (!empty($category['description'])): ?>
                                <p class="category-description">
                                    <?php echo htmlspecialchars($category['description']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="category-actions">
                                <a href="view_collection.php?id=<?php echo $category['id']; ?>" class="btn-primary">View Words</a>
                                <a href="categories.php?delete_id=<?php echo $category['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure? This will remove all words from this collection.')">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results">
                        <p>No collections yet. Create your first collection to organize your words!</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Create Category Modal -->
        <div id="createModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Create New Collection</h3>
                    <span class="close" onclick="document.getElementById('createModal').style.display='none'">&times;</span>
                </div>
                <form method="POST" action="categories.php">
                    <input type="hidden" name="action" value="create_category">
                    
                    <div class="form-group">
                        <label for="category_name">Collection Name *</label>
                        <input type="text" 
                               id="category_name" 
                               name="category_name" 
                               required 
                               placeholder="e.g., Important Words, Daily Use, Academic">
                    </div>

                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Describe what kind of words this collection will contain"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Create Collection</button>
                        <button type="button" class="btn-secondary" onclick="document.getElementById('createModal').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> My Dictionary - Learn & Organize Words</p>
        </footer>
    </div>

    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            let modal = document.getElementById('createModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
