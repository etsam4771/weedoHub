# 📚 Dictionary Website - PHP Learning Platform

A feature-rich dictionary web application built with PHP and MySQL that allows you to manage words with Hindi and English meanings, examples, and organize them into custom collections.

## ✨ Features

### Core Functionality
- **Multi-language Dictionary**: Store words with both Hindi (हिंदी) and English meanings
- **Examples**: Add example sentences to illustrate word usage
- **Real-time Search**: AJAX-powered search with instant results and debouncing
- **CRUD Operations**: Add, edit, view, and delete dictionary words
- **Text-to-Speech**: Listen to pronunciations of words, meanings, and examples in both English and Hindi
- **Bulk Upload**: Import multiple words at once using CSV files
- **Collection Bulk Upload**: Upload CSV directly into specific collections (NEW!)
- **CSV Export**: Export all words or specific collections to CSV
- **PDF Export**: Export dictionary or collections as professional PDFs (NEW!)

### Performance Features
- **Infinite Scroll**: Automatically load more words as you scroll (perfect for 1000+ words)
- **AJAX Loading**: Fast, asynchronous content loading without page refreshes
- **Smart Debouncing**: Search automatically 500ms after you stop typing
- **Optimized Queries**: Loads 20 words at a time for optimal performance

### Collections System
- **Create Custom Collections**: Organize words into sub-dictionaries (e.g., "Important Words", "Daily Use", "Academic")
- **Flexible Organization**: Add any word to multiple collections
- **Collection Management**: View, manage, and remove words from collections
- **Word Tracking**: See how many words are in each collection

### User Experience
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Beautiful UI**: Modern gradient design with smooth animations
- **Auto-loading**: No pagination buttons needed - content loads as you scroll
- **Flash Messages**: Clear feedback for all user actions
- **Audio Pronunciation**: Click 🔊 buttons to hear words spoken aloud

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- phpMyAdmin (optional, for database management)

### Setup Instructions

1. **Clone or Download** this project to your web server directory:
   ```bash
   cd /var/www/myproject/etsamDicnoary
   ```

2. **Create the Database**:
   - Open phpMyAdmin or MySQL command line
   - Import the `database.sql` file:
   ```bash
   mysql -u root -p < database.sql
   ```
   Or manually run the SQL file in phpMyAdmin

3. **Configure Database Connection**:
   - Open `config.php`
   - Update the database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');          // Your MySQL username
   define('DB_PASS', '');              // Your MySQL password
   define('DB_NAME', 'dictionary_db');
   ```

4. **Set Permissions** (if on Linux):
   ```bash
   chmod -R 755 /var/www/myproject/etsamDicnoary
   chown -R www-data:www-data /var/www/myproject/etsamDicnoary
   ```

5. **Access the Application**:
   - Open your web browser
   - Navigate to: `http://localhost/myproject/etsamDicnoary/`
   - Or: `http://yourdomain.com/etsamDicnoary/`

## 📁 File Structure

```
etsamDicnoary/
├── api/
│   ├── get_words.php           # AJAX endpoint for loading words
│   └── get_collection_words.php # AJAX endpoint for collection words
├── index.php                    # Main dictionary listing page (AJAX-powered)
├── script.js                    # AJAX, infinite scroll, and TTS functionality
├── collection_script.js         # Collection page AJAX functionality
├── add_word.php                 # Add new words
├── edit_word.php                # Edit existing words
├── delete_word.php              # Delete words
├── bulk_upload.php              # Bulk upload words from CSV
├── collection_bulk_upload.php   # Upload CSV to specific collection (NEW!)
├── export.php                   # Export words to CSV
├── export_pdf.php               # Export words to PDF (NEW!)
├── fpdf_lib/                    # FPDF library for PDF generation
├── sample_template.csv          # Sample CSV template for bulk upload
├── categories.php               # Manage collections
├── view_collection.php          # View words in a collection (AJAX-powered)
├── add_to_collection.php        # Add word to collection
├── config.php                   # Database configuration
├── style.css                    # Styling and responsive design
├── database.sql                 # Database schema and sample data
└── README.md                    # This file
```

## 🎯 How to Use

### Adding Words
1. Click **"+ Add Word"** in the navigation
2. Fill in:
   - Word Name (required)
   - Hindi Meaning (required)
   - English Meaning (required)
   - Example (optional)
3. Click **"Add Word"**

### Bulk Uploading Words
1. Click **"📤 Bulk Upload"** in the navigation (for general upload)
   OR
   Click **"📦 Collection Upload"** to upload directly to a collection
2. Download the sample CSV template
3. Fill in your words following the format:
   - Column 1: Word Name
   - Column 2: Hindi Meaning
   - Column 3: English Meaning
   - Column 4: Example (optional)
4. Save your CSV file
5. For collection upload: Select the target collection from dropdown
6. Upload the file using the upload form
7. Words will be imported automatically

### Creating Collections
1. Go to **"My Collections"**
2. Click **"+ Create Collection"**
3. Enter collection name and optional description
4. Click **"Create Collection"**

### Adding Words to Collections
1. From the dictionary page, find the word you want to add
2. Click **"+ Add to Collection"** on the word card
3. Select the collection
4. Click **"Add to Collection"**

### Searching Words
1. Use the search box on the main dictionary page
2. Type any word, Hindi meaning, or English meaning
3. Results appear automatically as you type (500ms debounce)
4. Click "Clear" to reset the search

### Using Text-to-Speech
1. Click the 🔊 button next to any word to hear it pronounced
2. Click 🔊 next to Hindi meanings to hear in Hindi
3. Click 🔊 next to English meanings to hear in English
4. Click 🔊 next to examples to hear the example sentence
5. Text-to-speech works in modern browsers (Chrome, Edge, Safari, Firefox)

### Infinite Scroll
- Scroll down to automatically load more words
- No need to click "Next" or page numbers
- Loading indicator appears while fetching data
- Optimized for lists with 1000+ words

### Viewing Collections
1. Go to **"My Collections"**
2. Click **"View Words"** on any collection
3. See all words in that collection
4. Remove words from collection as needed

### Exporting Data
1. **Export All Words to CSV**: Click "📥 Export CSV" on the main dictionary page
2. **Export All Words to PDF**: Click "📄 Export PDF" on the main dictionary page
3. **Export Collection to CSV**: Click "📥 Export CSV" when viewing a collection
4. **Export Collection to PDF**: Click "📄 Export PDF" when viewing a collection
5. CSV files have UTF-8 encoding (Excel compatible)
6. PDF files are beautifully formatted with word cards

## 🗄️ Database Structure

### Tables

#### `words`
- `id` - Primary key
- `word_name` - The word itself
- `meaning_hindi` - Hindi meaning (हिंदी अर्थ)
- `meaning_english` - English meaning
- `example` - Example sentence
- `created_at` - Timestamp
- `updated_at` - Timestamp

#### `categories`
- `id` - Primary key
- `category_name` - Collection name
- `description` - Collection description
- `created_at` - Timestamp

#### `word_collections`
- `id` - Primary key
- `word_id` - Foreign key to words
- `category_id` - Foreign key to categories
- `added_at` - Timestamp

## 🎨 Customization

### Changing Colors
Edit `style.css` and modify the gradient colors:
```css
/* Main gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Change to your preferred colors */
background: linear-gradient(135deg, #YOUR_COLOR1 0%, #YOUR_COLOR2 100%);
```

### Adding More Languages
1. Add new columns to the `words` table:
   ```sql
   ALTER TABLE words ADD COLUMN meaning_spanish TEXT;
   ```
2. Update the forms in `add_word.php` and `edit_word.php`
3. Update the display in `index.php` and `view_collection.php`

## 🔒 Security Features

- SQL Injection Protection: Uses prepared statements
- XSS Prevention: All output is escaped with `htmlspecialchars()`
- Input Sanitization: User input is sanitized before processing
- Session Management: Secure flash message handling

## 📱 Responsive Design

The website is fully responsive and works on:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (320px - 767px)

## 🐛 Troubleshooting

### Database Connection Error
- Check MySQL service is running
- Verify credentials in `config.php`
- Ensure database exists

### Page Not Found (404)
- Check file permissions
- Verify web server configuration
- Ensure mod_rewrite is enabled (Apache)

### Words Not Displaying
- Check if database has data
- Verify database connection
- Check browser console for JavaScript errors

## 🚀 Future Enhancements

Potential features to add:
- [x] AJAX-based infinite scroll
- [x] Text-to-speech for pronunciations
- [x] Real-time search with debouncing
- [ ] User authentication and multi-user support
- [ ] Export collections to PDF/CSV
- [ ] Offline audio files for pronunciation
- [ ] Word of the day
- [ ] Quiz/Practice mode
- [ ] API for mobile apps
- [ ] Advanced filtering by categories
- [ ] Word etymology and origin
- [ ] Bookmarking/Favorites system
- [ ] Speech recognition for search

## 📄 License

This project is open source and available for educational purposes.

## 👨‍💻 Support

For issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Verify database setup

## 🙏 Acknowledgments

- Built with PHP and MySQL
- Responsive design using CSS Grid and Flexbox
- UTF-8 support for Hindi (Devanagari) characters

---

**Happy Learning! 📚**
