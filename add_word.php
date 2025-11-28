<?php
require_once 'config.php';
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word_name = sanitizeInput($_POST['word_name']);
    $meaning_hindi = sanitizeInput($_POST['meaning_hindi']);
    $meaning_english = sanitizeInput($_POST['meaning_english']);
    $example = sanitizeInput($_POST['example']);
    
    if (!empty($word_name) && !empty($meaning_hindi) && !empty($meaning_english)) {
        $stmt = $conn->prepare("INSERT INTO words (word_name, meaning_hindi, meaning_english, example) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $word_name, $meaning_hindi, $meaning_english, $example);
        
        if ($stmt->execute()) {
            setFlashMessage("Word added successfully!");
            header("Location: index.php");
            exit();
        } else {
            $error = "Error adding word. Please try again.";
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
    <title>Add Word - Dictionary</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📚 My Dictionary</h1>
            <nav>
                <a href="index.php">Dictionary</a>
                <a href="categories.php">My Collections</a>
                <a href="add_word.php" class="active">+ Add Word</a>
            </nav>
        </header>

        <main>
            <div class="form-container">
                <h2>Add New Word</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="add_word.php" class="word-form">
                    <div class="form-group">
                        <label for="word_name">Word Name *</label>
                        <input type="text" 
                               id="word_name" 
                               name="word_name" 
                               required 
                               placeholder="Enter word"
                               value="<?php echo isset($_POST['word_name']) ? htmlspecialchars($_POST['word_name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="meaning_hindi">Hindi Meaning *</label>
                        <textarea id="meaning_hindi" 
                                  name="meaning_hindi" 
                                  required 
                                  rows="3"
                                  placeholder="हिंदी में अर्थ"><?php echo isset($_POST['meaning_hindi']) ? htmlspecialchars($_POST['meaning_hindi']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="meaning_english">English Meaning *</label>
                        <textarea id="meaning_english" 
                                  name="meaning_english" 
                                  required 
                                  rows="3"
                                  placeholder="Enter English meaning"><?php echo isset($_POST['meaning_english']) ? htmlspecialchars($_POST['meaning_english']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="example">Example (Optional)</label>
                        <textarea id="example" 
                                  name="example" 
                                  rows="3"
                                  placeholder="Enter an example sentence"><?php echo isset($_POST['example']) ? htmlspecialchars($_POST['example']) : ''; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Add Word</button>
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
