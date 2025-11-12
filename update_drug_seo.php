<?php
// Run this once to generate slugs and SEO data for existing drugs

// Establishing the database connection
require_once('conn.php');

function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

$drugs_query = "SELECT id, drug_name FROM drugs WHERE slug IS NULL OR slug = ''";
$result = $conn->query($drugs_query);

$updated = 0;

while ($drug = $result->fetch_assoc()) {
    $slug = createSlug($drug['drug_name']);
    $meta_description = "Buy " . $drug['drug_name'] . " online at Bumar Pharmacy Uganda. Fast delivery, licensed pharmacy, quality medications. Order now for same-day delivery across Kampala.";
    $meta_keywords = $drug['drug_name'] . ", buy " . $drug['drug_name'] . " Uganda, " . $drug['drug_name'] . " Kampala, " . $drug['drug_name'] . " pharmacy, Bumar Pharmacy, online pharmacy Uganda";
    
    $update_stmt = $conn->prepare("UPDATE drugs SET slug = ?, meta_description = ?, meta_keywords = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $slug, $meta_description, $meta_keywords, $drug['id']);
    
    if ($update_stmt->execute()) {
        $updated++;
    }
}

echo "Updated $updated drugs with SEO data\n";

$conn->close();
?>