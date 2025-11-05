<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "bunar_pharmacy";
$conn = new mysqli($host, $user, $pass, $db);

// Get drug by slug from URL
$drug_slug = $_GET['drug'] ?? '';

if (empty($drug_slug)) {
    header("Location: index.php");
    exit;
}

// Fetch drug details
$stmt = $conn->prepare("SELECT * FROM drugs WHERE slug = ?");
$stmt->bind_param("s", $drug_slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$drug = $result->fetch_assoc();

// Update page views
$update_views = $conn->prepare("UPDATE drugs SET page_views = page_views + 1, last_viewed = NOW() WHERE id = ?");
$update_views->bind_param("i", $drug['id']);
$update_views->execute();

// Get related drugs
$related_query = "SELECT * FROM drugs WHERE id != ? AND in_stock = TRUE ORDER BY RAND() LIMIT 4";
$related_stmt = $conn->prepare($related_query);
$related_stmt->bind_param("i", $drug['id']);
$related_stmt->execute();
$related_drugs = $related_stmt->get_result();

// Prepare meta information
$page_title = htmlspecialchars($drug['drug_name']) . " - Buy Online at Bumar Pharmacy Uganda";
$meta_description = $drug['meta_description'] ?: "Buy " . htmlspecialchars($drug['drug_name']) . " at Bumar Pharmacy. Fast delivery across Uganda. Licensed pharmacy with quality medications.";
$meta_keywords = $drug['meta_keywords'] ?: htmlspecialchars($drug['drug_name']) . ", buy " . htmlspecialchars($drug['drug_name']) . " Uganda, " . htmlspecialchars($drug['drug_name']) . " pharmacy, Bumar Pharmacy";
$canonical_url = "https://www.bumarpharmacy.com/drug/" . $drug_slug;
$drug_image = "https://www.bumarpharmacy.com/images/drugs/" . $drug_slug . ".jpg";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title><?php echo $page_title; ?></title>
    <meta name="title" content="<?php echo $page_title; ?>">
    <meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">
    <meta name="author" content="Bumar Pharmacy">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $meta_description; ?>">
    <meta property="og:image" content="<?php echo $drug_image; ?>">
    <meta property="og:site_name" content="Bumar Pharmacy">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $canonical_url; ?>">
    <meta property="twitter:title" content="<?php echo $page_title; ?>">
    <meta property="twitter:description" content="<?php echo $meta_description; ?>">
    <meta property="twitter:image" content="<?php echo $drug_image; ?>">
    
    <!-- Structured Data (Schema.org) for Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "<?php echo htmlspecialchars($drug['drug_name']); ?>",
        "image": "<?php echo $drug_image; ?>",
        "description": "<?php echo addslashes($meta_description); ?>",
        "brand": {
            "@type": "Brand",
            "name": "<?php echo htmlspecialchars($drug['manufacturer'] ?? 'Various Manufacturers'); ?>"
        },
        "offers": {
            "@type": "Offer",
            "url": "<?php echo $canonical_url; ?>",
            "priceCurrency": "UGX",
            "price": "<?php echo $drug['price'] ?? '0'; ?>",
            "availability": "<?php echo $drug['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>",
            "seller": {
                "@type": "Organization",
                "name": "Bumar Pharmacy",
                "telephone": "+256749059309",
                "address": {
                    "@type": "PostalAddress",
                    "streetAddress": "Kamuli Road",
                    "addressLocality": "Kireka",
                    "addressRegion": "Kampala",
                    "addressCountry": "UG"
                }
            }
        }
    }
    </script>
    
    <!-- Local Business Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Pharmacy",
        "name": "Bumar Pharmacy",
        "image": "https://www.bumarpharmacy.com/images/logo.png",
        "telephone": "+256749059309",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Kamuli Road",
            "addressLocality": "Kireka",
            "addressRegion": "Kampala",
            "postalCode": "",
            "addressCountry": "UG"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": 0.3485122,
            "longitude": 32.6467761
        },
        "url": "https://www.bumarpharmacy.com",
        "openingHoursSpecification": [
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                "opens": "07:30",
                "closes": "00:00"
            },
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": "Saturday",
                "opens": "07:30",
                "closes": "00:00"
            },
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": "Sunday",
                "opens": "07:30",
                "closes": "22:30"
            }
        ]
    }
    </script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #94061b;
        }
        body {
            padding-top: 130px;
        }
        .drug-header {
            background: linear-gradient(135deg, var(--primary-color), #7d0518);
            color: white;
            padding: 60px 0;
        }
        .breadcrumb {
            background: none;
            margin-bottom: 0;
        }
        .breadcrumb-item a {
            color: white;
            text-decoration: none;
        }
        .price-tag {
            font-size: 2rem;
            color: var(--primary-color);
            font-weight: bold;
        }
        .btn-order {
            background: var(--primary-color);
            color: white;
            padding: 15px 40px;
            font-size: 1.2rem;
        }
        .btn-order:hover {
            background: #7d0518;
            color: white;
        }
        .stock-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        .in-stock {
            background: #d4edda;
            color: #155724;
        }
        .out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Include your navigation here -->
    
    <div class="drug-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="drugs.php">Drugs</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($drug['drug_name']); ?></li>
                </ol>
            </nav>
            <h1><?php echo htmlspecialchars($drug['drug_name']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($drug['manufacturer'] ?? 'Quality medication from trusted manufacturers'); ?></p>
        </div>
    </div>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <span class="stock-badge <?php echo $drug['in_stock'] ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo $drug['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                </span>
                            </div>
                            <div class="price-tag">
                                UGX <?php echo number_format($drug['price'] ?? 0); ?>
                            </div>
                        </div>
                        
                        <?php if ($drug['drug_description']): ?>
                        <h3>About <?php echo htmlspecialchars($drug['drug_name']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($drug['drug_description'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($drug['usage_information']): ?>
                        <h3 class="mt-4">Usage Information</h3>
                        <p><?php echo nl2br(htmlspecialchars($drug['usage_information'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($drug['side_effects']): ?>
                        <h3 class="mt-4">Possible Side Effects</h3>
                        <p><?php echo nl2br(htmlspecialchars($drug['side_effects'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4>Order Now</h4>
                        <p>Get <?php echo htmlspecialchars($drug['drug_name']); ?> delivered to your doorstep</p>
                        <a href="tel:+256749059309" class="btn btn-order w-100 mb-3">
                            <i class="fas fa-phone me-2"></i>Call to Order
                        </a>
                        <a href="https://wa.me/256770990793?text=I%20want%20to%20order%20<?php echo urlencode($drug['drug_name']); ?>" class="btn btn-success w-100" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>Order via WhatsApp
                        </a>
                        
                        <hr class="my-4">
                        
                        <h5>Delivery Information</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Same-day delivery</li>
                            <li><i class="fas fa-check text-success me-2"></i>Free delivery over UGX 20,000</li>
                            <li><i class="fas fa-check text-success me-2"></i>Secure packaging</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($related_drugs->num_rows > 0): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3>Related Medications</h3>
                <hr>
            </div>
            <?php while ($related = $related_drugs->fetch_assoc()): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($related['drug_name']); ?></h5>
                        <p class="text-muted">UGX <?php echo number_format($related['price'] ?? 0); ?></p>
                        <a href="drug-detail.php?drug=<?php echo urlencode($related['slug']); ?>" class="btn btn-outline-primary btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Include your footer here -->
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>