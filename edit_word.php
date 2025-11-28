<?php
require_once 'config.php';
$conn = getDBConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Get word data
$stmt = $conn->prepare("SELECT * FROM words WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$word = $result->fetch_assoc();

if (!$word) {
    setFlashMessage("Word not found.", "error");
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word_name = sanitizeInput($_POST['word_name']);
    $meaning_hindi = sanitizeInput($_POST['meaning_hindi']);
    $meaning_english = sanitizeInput($_POST['meaning_english']);
    $example = sanitizeInput($_POST['example']);
    
    if (!empty($word_name) && !empty($meaning_hindi) && !empty($meaning_english)) {
        $stmt = $conn->prepare("UPDATE words SET word_name = ?, meaning_hindi = ?, meaning_english = ?, example = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $word_name, $meaning_hindi, $meaning_english, $example, $id);
        
        if ($stmt->execute()) {
            setFlashMessage("Word updated successfully!");
            header("Location: index.php");
            exit();
        } else {
            $error = "Error updating word. Please try again.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Word - Dictionary</title>
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
            <div class="form-container">
                <h2>Edit Word</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="edit_word.php?id=<?php echo $id; ?>" class="word-form">
                    <div class="form-group">
                        <label for="word_name">Word Name *</label>
                        <input type="text" 
                               id="word_name" 
                               name="word_name" 
                               required 
                               placeholder="Enter word"
                               value="<?php echo htmlspecialchars($word['word_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="meaning_hindi">Hindi Meaning *</label>
                        <textarea id="meaning_hindi" 
                                  name="meaning_hindi" 
                                  required 
                                  rows="3"
                                  placeholder="हिंदी में अर्थ"><?php echo htmlspecialchars($word['meaning_hindi']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="meaning_english">English Meaning *</label>
                        <textarea id="meaning_english" 
                                  name="meaning_english" 
                                  required 
                                  rows="3"
                                  placeholder="Enter English meaning"><?php echo htmlspecialchars($word['meaning_english']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="example">Example (Optional)</label>
                        <textarea id="example" 
                                  name="example" 
                                  rows="3"
                                  placeholder="Enter an example sentence"><?php echo htmlspecialchars($word['example']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Update Word</button>
                        <a href="index.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> My Dictionary - Learn & Organize Words</p>
        </footer>
    </div>
</body>
</html>
<?php $conn->close(); ?>
