<?php
require_once 'config.php';
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
                <a href="add_word.php">+ Add Word</a>
                <a href="bulk_upload.php">📤 Bulk Upload</a>
                <a href="collection_bulk_upload.php">📦 Collection Upload</a>
            </nav>
        </header>

        <main>
            <?php displayFlashMessage(); ?>

            <div class="search-section">
                <form id="searchForm" class="search-form">
                    <input type="text" 
                           id="searchInput"
                           name="search" 
                           placeholder="Search words, meanings..." 
                           class="search-input">
                    <button type="submit" class="btn-primary">Search</button>
                    <button type="button" id="clearSearch" class="btn-secondary" style="display:none;">Clear</button>
                    <a href="export.php?type=all" class="btn-export" title="Export all words to CSV">📥 Export All</a>
                </form>
            </div>

            <div class="stats">
                <p>Total Words: <strong id="totalWords">0</strong></p>
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

    <script src="script.js"></script>
</body>
</html><?php // No need to close connection as we're using AJAX ?>
