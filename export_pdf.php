<?php
require_once 'config.php';
require_once 'fpdf_lib/fpdf.php';

$conn = getDBConnection();

// Custom PDF class for UTF-8 support
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Dictionary Export', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 8, 'Generated on ' . date('F d, Y'), 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
    
    function WordCard($word_name, $meaning_hindi, $meaning_english, $example) {
        // Word name in bold
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(230, 230, 250);
        $this->Cell(0, 8, $this->convertText($word_name), 0, 1, 'L', true);
        
        // Hindi meaning
        $this->SetFont('Arial', '', 11);
        $this->Cell(45, 7, 'Hindi:', 0, 0, 'L');
        $this->SetFont('Arial', 'I', 11);
        $this->MultiCell(0, 7, $this->convertText($meaning_hindi), 0, 'L');
        
        // English meaning
        $this->SetFont('Arial', '', 11);
        $this->Cell(45, 7, 'English:', 0, 0, 'L');
        $this->SetFont('Arial', 'I', 11);
        $this->MultiCell(0, 7, $this->convertText($meaning_english), 0, 'L');
        
        // Example if available
        if (!empty($example)) {
            $this->SetFont('Arial', '', 11);
            $this->Cell(45, 7, 'Example:', 0, 0, 'L');
            $this->SetFont('Arial', 'I', 10);
            $this->MultiCell(0, 7, $this->convertText($example), 0, 'L');
        }
        
        $this->Ln(3);
    }
    
    function convertText($text) {
        // Convert UTF-8 to ISO-8859-1 for FPDF compatibility
        // Note: Hindi characters will be transliterated
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
    }
}

$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$title = 'All Words';
$filename = 'dictionary_export';

if ($type === 'collection' && $category_id > 0) {
    // Get collection name
    $cat_stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    
    if ($cat_result->num_rows > 0) {
        $category = $cat_result->fetch_assoc();
        $title = 'Collection: ' . $category['category_name'];
        $filename = 'collection_' . preg_replace('/[^a-z0-9]+/i', '_', $category['category_name']);
        
        // Get words in collection
        $stmt = $conn->prepare("
            SELECT w.* 
            FROM words w
            INNER JOIN word_collections wc ON w.id = wc.word_id
            WHERE wc.category_id = ?
            ORDER BY w.word_name ASC
        ");
        $stmt->bind_param("i", $category_id);
    } else {
        die("Collection not found");
    }
} else {
    // Get all words
    $stmt = $conn->prepare("SELECT * FROM words ORDER BY word_name ASC");
}

$stmt->execute();
$result = $stmt->get_result();

// Add title
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, $title, 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, 'Total Words: ' . $result->num_rows, 0, 1, 'L');
$pdf->Ln(5);

// Add words
if ($result->num_rows > 0) {
    while ($word = $result->fetch_assoc()) {
        // Check if we need a new page
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
        }
        
        $pdf->WordCard(
            $word['word_name'],
            $word['meaning_hindi'],
            $word['meaning_english'],
            $word['example']
        );
    }
} else {
    $pdf->Cell(0, 10, 'No words found.', 0, 1, 'C');
}

$conn->close();

// Output PDF
$pdf->Output('D', $filename . '_' . date('Y-m-d_H-i-s') . '.pdf');
exit();
