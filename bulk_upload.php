<?php
require_once 'config.php';
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csv_file']['tmp_name'];
        $fileName = $_FILES['csv_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if ($fileExtension !== 'csv') {
            setFlashMessage("Please upload a CSV file only.", "error");
            header("Location: bulk_upload.php");
            exit();
        }
        
        $handle = fopen($fileTmpPath, 'r');
        if ($handle === false) {
            setFlashMessage("Error reading the file.", "error");
            header("Location: bulk_upload.php");
            exit();
        }
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        $stmt = $conn->prepare("INSERT INTO words (word_name, meaning_hindi, meaning_english, example) VALUES (?, ?, ?, ?)");
        
        while (($data = fgetcsv($handle)) !== false) {
            // Expecting: word_name, meaning_hindi, meaning_english, example
            if (count($data) >= 3) {
                $word_name = trim($data[0]);
                $meaning_hindi = trim($data[1]);
                $meaning_english = trim($data[2]);
                $example = isset($data[3]) ? trim($data[3]) : '';
                
                if (!empty($word_name) && !empty($meaning_hindi) && !empty($meaning_english)) {
                    $stmt->bind_param("ssss", $word_name, $meaning_hindi, $meaning_english, $example);
                    
                    if ($stmt->execute()) {
                        $successCount++;
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
        
        $message = "Upload complete! Added $successCount words.";
        if ($errorCount > 0) {
            $message .= " $errorCount rows had errors.";
        }
        
        setFlashMessage($message, $errorCount > 0 ? "error" : "success");
        $_SESSION['upload_errors'] = $errors;
        header("Location: bulk_upload.php");
        exit();
    } else {
        setFlashMessage("No file uploaded or upload error occurred.", "error");
        header("Location: bulk_upload.php");
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
    <title>Bulk Upload - Dictionary</title>
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
                <a href="bulk_upload.php" class="active">📤 Bulk Upload</a>
                <a href="collection_bulk_upload.php">📦 Collection Upload</a>
            </nav>
        </header>

        <main>
            <?php displayFlashMessage(); ?>

            <div class="page-header">
                <h2>Bulk Upload Words</h2>
            </div>

            <div class="upload-section">
                <div class="upload-instructions">
                    <h3>📋 Instructions</h3>
                    <ol>
                        <li>Download the sample CSV file below</li>
                        <li>Fill in your words following the format</li>
                        <li>Upload the completed CSV file</li>
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
                        <a href="export.php?type=all" class="btn-secondary">📤 Export All Words</a>
                    </div>
                </div>

                <div class="upload-form-container">
                    <h3>Upload Your CSV File</h3>
                    <form method="POST" action="bulk_upload.php" enctype="multipart/form-data" class="upload-form">
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
                            <button type="submit" class="btn-primary">📤 Upload & Import</button>
                            <a href="index.php" class="btn-secondary">Cancel</a>
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
