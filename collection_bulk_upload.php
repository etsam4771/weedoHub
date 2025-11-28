<?php
require_once 'config.php';
$conn = getDBConnection();

// Get all categories for dropdown
$categories_result = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    if ($category_id <= 0) {
        setFlashMessage("Please select a collection.", "error");
        header("Location: collection_bulk_upload.php");
        exit();
    }
    
    // Verify collection exists
    $check_stmt = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $check_stmt->bind_param("i", $category_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        setFlashMessage("Selected collection does not exist.", "error");
        header("Location: collection_bulk_upload.php");
        exit();
    }
    
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csv_file']['tmp_name'];
        $fileName = $_FILES['csv_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if ($fileExtension !== 'csv') {
            setFlashMessage("Please upload a CSV file only.", "error");
            header("Location: collection_bulk_upload.php");
            exit();
        }
        
        $handle = fopen($fileTmpPath, 'r');
        if ($handle === false) {
            setFlashMessage("Error reading the file.", "error");
            header("Location: collection_bulk_upload.php");
            exit();
        }
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        $word_stmt = $conn->prepare("INSERT INTO words (word_name, meaning_hindi, meaning_english, example) VALUES (?, ?, ?, ?)");
        $collection_stmt = $conn->prepare("INSERT INTO word_collections (word_id, category_id) VALUES (?, ?)");
        
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 3) {
                $word_name = trim($data[0]);
                $meaning_hindi = trim($data[1]);
                $meaning_english = trim($data[2]);
                $example = isset($data[3]) ? trim($data[3]) : '';
                
                if (!empty($word_name) && !empty($meaning_hindi) && !empty($meaning_english)) {
                    $word_stmt->bind_param("ssss", $word_name, $meaning_hindi, $meaning_english, $example);
                    
                    if ($word_stmt->execute()) {
                        $word_id = $conn->insert_id;
                        
                        // Add word to collection
                        $collection_stmt->bind_param("ii", $word_id, $category_id);
                        if ($collection_stmt->execute()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Added word '$word_name' but failed to add to collection";
                        }
                    } else {
                        $errorCount++;
                        $errors[] = "Error adding word: $word_name";
                    }
                } else {
                    $errorCount++;
                    $errors[] = "Skipped row with missing data: " . implode(', ', $data);
                }
            }
        }
        
        fclose($handle);
        
        $message = "Upload complete! Added $successCount words to collection.";
        if ($errorCount > 0) {
            $message .= " $errorCount rows had errors.";
        }
        
        setFlashMessage($message, $errorCount > 0 ? "error" : "success");
        $_SESSION['upload_errors'] = $errors;
        header("Location: collection_bulk_upload.php");
        exit();
    } else {
        setFlashMessage("No file uploaded or upload error occurred.", "error");
        header("Location: collection_bulk_upload.php");
        exit();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Bulk Upload - Dictionary</title>
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
                <a href="collection_bulk_upload.php" class="active">📦 Collection Upload</a>
            </nav>
        </header>

        <main>
            <?php displayFlashMessage(); ?>

            <div class="page-header">
                <h2>Bulk Upload to Collection</h2>
            </div>

            <div class="upload-section">
                <div class="upload-instructions">
                    <h3>📋 Instructions</h3>
                    <ol>
                        <li>Select a collection from the dropdown</li>
                        <li>Download the sample CSV file (optional)</li>
                        <li>Fill in your words following the format</li>
                        <li>Upload the CSV - words will be added to the selected collection</li>
                    </ol>
                    
                    <h4>CSV Format:</h4>
                    <ul>
                        <li><strong>Column 1:</strong> Word Name (Required)</li>
                        <li><strong>Column 2:</strong> Hindi Meaning (Required)</li>
                        <li><strong>Column 3:</strong> English Meaning (Required)</li>
                        <li><strong>Column 4:</strong> Example (Optional)</li>
                    </ul>

                    <div class="download-section">
                        <a href="sample_template.csv" download class="btn-primary">📥 Download Sample CSV Template</a>
                    </div>
                </div>

                <div class="upload-form-container">
                    <h3>Upload to Collection</h3>
                    <form method="POST" action="collection_bulk_upload.php" enctype="multipart/form-data" class="upload-form">
                        <div class="form-group">
                            <label for="category_id">Select Collection *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">-- Choose a Collection --</option>
                                <?php 
                                $categories_result->data_seek(0);
                                while($cat = $categories_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="file-input-wrapper">
                            <label for="csv_file" class="file-label">
                                <span class="file-icon">📁</span>
                                <span id="fileName">Choose CSV File</span>
                            </label>
                            <input type="file" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv" 
                                   required
                                   onchange="updateFileName(this)">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">📤 Upload to Collection</button>
                            <a href="categories.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>

                <?php if (isset($_SESSION['upload_errors']) && count($_SESSION['upload_errors']) > 0): ?>
                    <div class="error-details">
                        <h3>⚠️ Upload Errors:</h3>
                        <ul>
                            <?php foreach ($_SESSION['upload_errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['upload_errors']); ?>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> My Dictionary - Learn & Organize Words</p>
        </footer>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0]?.name || 'Choose CSV File';
            document.getElementById('fileName').textContent = fileName;
        }
    </script>
</body>
</html>
