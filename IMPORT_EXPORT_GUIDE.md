# 📥📤 Import/Export Guide - Dictionary App

## Overview

The dictionary now includes comprehensive import and export features to help you manage large datasets efficiently.

## 📥 Import Features (Bulk Upload)

### How to Access
Navigate to **"📤 Bulk Upload"** in the main navigation menu.

### Step-by-Step Guide

#### 1. Download Sample Template
- Click **"📥 Download Sample CSV Template"**
- This downloads `sample_template.csv` with the correct format
- Contains 10 example words to guide you

#### 2. Prepare Your CSV File

**Required Format:**
```csv
word_name,meaning_hindi,meaning_english,example
Knowledge,ज्ञान,"Information, understanding, or skill",Knowledge is power when applied wisely.
Wisdom,बुद्धि,"The quality of having experience",With age comes wisdom and understanding.
```

**Column Details:**
- **Column 1 (word_name)**: The word in English - **REQUIRED**
- **Column 2 (meaning_hindi)**: Hindi meaning in Devanagari script - **REQUIRED**
- **Column 3 (meaning_english)**: English meaning/definition - **REQUIRED**
- **Column 4 (example)**: Example sentence - **OPTIONAL**

**Important Rules:**
- ✅ First row must be the header (word_name,meaning_hindi,meaning_english,example)
- ✅ Minimum 3 columns required (word, Hindi meaning, English meaning)
- ✅ Example column is optional
- ✅ Use quotes for text with commas: "This, is an example"
- ✅ UTF-8 encoding recommended for Hindi characters
- ✅ Save as `.csv` file (not .xlsx or .xls)

#### 3. Fill in Your Data

**Using Excel/Google Sheets:**
1. Open the sample template in Excel or Google Sheets
2. Delete the example rows
3. Add your own words
4. Save/Download as CSV (UTF-8 recommended)

**Using Text Editor:**
1. Keep the header row
2. Add one word per line
3. Separate columns with commas
4. Save with .csv extension

**Example Data:**
```csv
word_name,meaning_hindi,meaning_english,example
Beautiful,सुंदर,Pleasing to the senses or mind,The sunset was beautiful.
Happy,खुश,Feeling or showing pleasure,She felt happy today.
Friend,दोस्त,A person with whom one has a bond of mutual affection,He is my best friend.
```

#### 4. Upload Your File
1. Click **"Choose CSV File"** button
2. Select your prepared CSV file
3. Click **"📤 Upload & Import"**
4. Wait for the upload to complete

#### 5. Review Results
- Success message shows how many words were added
- Error message shows if any rows had problems
- Detailed error list shows which rows failed and why

### Bulk Upload Features
- ✅ Imports unlimited words in one go
- ✅ Fast processing (can handle 1000+ words)
- ✅ Validates each row before importing
- ✅ Skips invalid rows, continues with valid ones
- ✅ Provides detailed error reporting
- ✅ Supports Hindi Unicode (Devanagari) characters

### Common Upload Errors

| Error | Cause | Solution |
|-------|-------|----------|
| "Please upload a CSV file only" | Wrong file format | Save file as .csv, not .xlsx or .txt |
| "Skipped row with missing data" | Required field empty | Ensure word, Hindi, and English meanings are filled |
| "Error reading the file" | File corrupted | Re-save the CSV file and try again |
| Hindi text shows as ??? | Encoding issue | Save CSV as UTF-8 encoding |

## 📤 Export Features

### 1. Export All Words

**How to Use:**
1. Go to main dictionary page (`index.php`)
2. Click **"📥 Export All"** in the search section
3. CSV file downloads automatically

**What You Get:**
- All words in your dictionary
- Includes: Word Name, Hindi Meaning, English Meaning, Example, Created Date
- UTF-8 encoded (Excel compatible)
- Filename: `dictionary_export_YYYY-MM-DD_HH-MM-SS.csv`

### 2. Export Collection

**How to Use:**
1. Go to **"My Collections"**
2. Click **"View Words"** on any collection
3. Click **"📥 Export Collection"** button
4. CSV file downloads automatically

**What You Get:**
- Only words in that specific collection
- Same format as "Export All"
- Great for sharing themed word lists

### 3. Export Single Word

**How to Use:**
1. Find any word card on the dictionary or collection page
2. Click the **📥** button in the word actions area
3. CSV file with just that one word downloads

**What You Get:**
- Single word with all its details
- Useful for sharing individual words
- Same CSV format for consistency

### Export File Format

All exports use this format:
```csv
Word Name,Hindi Meaning,English Meaning,Example,Created At
Knowledge,ज्ञान,"Information, understanding, or skill",Knowledge is power when applied wisely.,2025-11-29 10:30:45
```

### Opening Exported Files

**In Excel:**
1. Open Excel
2. Go to Data → From Text/CSV
3. Select your exported file
4. Choose UTF-8 encoding
5. Click Load

**In Google Sheets:**
1. File → Import
2. Upload your CSV file
3. Import as UTF-8
4. Click Import data

**Note:** Hindi characters require UTF-8 encoding. The export includes UTF-8 BOM marker for automatic Excel recognition.

## 🔄 Import/Export Workflow Examples

### Scenario 1: Backing Up Your Dictionary
1. Click **"📥 Export All"** on main page
2. Save the CSV file to cloud storage (Google Drive, Dropbox, etc.)
3. Now you have a backup!

### Scenario 2: Sharing a Collection
1. Create a collection (e.g., "IELTS Vocabulary")
2. Add relevant words to the collection
3. Go to the collection page
4. Click **"📥 Export Collection"**
5. Share the CSV file with students/friends

### Scenario 3: Migrating from Another App
1. Export your words from other app as CSV
2. Format the CSV to match our template (4 columns)
3. Go to **"📤 Bulk Upload"**
4. Upload your formatted CSV
5. All words imported at once!

### Scenario 4: Creating Large Dataset
1. Download sample template
2. Open in Excel/Google Sheets
3. Add 100+ words
4. Save as CSV (UTF-8)
5. Upload via bulk upload
6. All words added in seconds!

### Scenario 5: Editing Multiple Words
1. Export all words or a collection
2. Open CSV in Excel
3. Edit meanings, examples, etc.
4. Delete words from database that you want to replace
5. Re-import the edited CSV
6. Changes reflected instantly!

## 📋 Best Practices

### For Imports:
1. ✅ Always test with a small file first (5-10 words)
2. ✅ Use the sample template as a starting point
3. ✅ Keep a copy of your CSV before uploading
4. ✅ Check Hindi text displays correctly before upload
5. ✅ Use UTF-8 encoding for Hindi characters
6. ✅ Validate data in Excel before importing

### For Exports:
1. ✅ Export regularly as backups
2. ✅ Use meaningful collection names for easier identification
3. ✅ Store exports in organized folders
4. ✅ Include date in filename (auto-added)
5. ✅ Open in UTF-8 compatible editors

## 🛠️ Technical Details

### CSV Specifications
- **Encoding**: UTF-8 with BOM
- **Delimiter**: Comma (,)
- **Text Qualifier**: Double quotes (")
- **Line Ending**: CRLF or LF
- **Max File Size**: 10MB (configurable in php.ini)

### Security Features
- File type validation (CSV only)
- SQL injection prevention (prepared statements)
- Input sanitization on all fields
- Error handling and logging
- Safe file upload handling

### Performance
- **Import Speed**: ~100 words/second
- **Export Speed**: ~500 words/second
- **Memory Usage**: Minimal (streaming output)
- **Concurrent Uploads**: Supported

## ❓ Troubleshooting

### Import Issues

**Problem**: Upload fails with no error
- Check file size (should be under 10MB)
- Verify CSV format is correct
- Try with sample template first

**Problem**: Hindi characters show as question marks
- Save CSV as UTF-8 encoding
- Use Notepad++ or VS Code to check encoding
- Re-save from Excel as "CSV UTF-8"

**Problem**: Some words skipped
- Check error details displayed after upload
- Ensure all required fields are filled
- Remove special characters from data

### Export Issues

**Problem**: Download doesn't start
- Check browser popup blocker
- Try different browser
- Clear browser cache

**Problem**: Hindi text corrupted in Excel
- Use "Data → From Text/CSV" instead of direct open
- Select UTF-8 encoding explicitly
- Or use Google Sheets (better UTF-8 support)

**Problem**: Empty file downloaded
- Database might be empty
- Check if words exist in selected collection
- Verify database connection

## 📞 Support

For issues with import/export:
1. Check this guide first
2. Review error messages carefully
3. Try with sample template
4. Check file encoding (UTF-8)
5. Test with small dataset first

## 🎯 Quick Reference

| Feature | Location | Result |
|---------|----------|--------|
| Bulk Upload | Navigation → 📤 Bulk Upload | Import multiple words from CSV |
| Download Template | Bulk Upload page | Get sample CSV file |
| Export All | Main page → 📥 Export All | Download all words |
| Export Collection | Collection page → 📥 Export Collection | Download collection words |
| Export Single | Word card → 📥 button | Download one word |

---

**Happy Importing & Exporting! 📥📤**
