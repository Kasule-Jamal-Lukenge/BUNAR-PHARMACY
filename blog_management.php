<?php
    session_start();

    // Checking if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=" . urlencode("Please log in to access this page"));
        exit;
    }

    // Database connection
    require_once('./conn.php');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Getting statistics
    $total_blogs_query = "SELECT COUNT(*) as total FROM blogs";
    $total_result = $conn->query($total_blogs_query);
    $total_blogs = $total_result->fetch_assoc()['total'];

    $published_blogs_query = "SELECT COUNT(*) as published FROM blogs WHERE status = 'published'";
    $published_result = $conn->query($published_blogs_query);
    $published_blogs = $published_result->fetch_assoc()['published'];

    $recent_blogs_query = "SELECT COUNT(*) as recent FROM blogs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $recent_result = $conn->query($recent_blogs_query);
    $recent_blogs = $recent_result->fetch_assoc()['recent'];

    // Getting user info
    $username = $_SESSION['username'] ?? 'User';
    $role = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Bumar Pharmacy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #94061b;
            --secondary-color: #f8f9fa;
            --accent-color: #e9ecef;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            padding-top: 80px;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
        }
        
        .navbar-nav .nav-link:hover {
            color: #f8f9fa !important;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            color: white;
            margin-right: 20px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .btn-logout {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid white;
            padding: 8px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background-color: white;
            color: var(--primary-color);
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #7d0518);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(148, 6, 27, 0.2);
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stats-section {
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(148, 6, 27, 0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), #7d0518);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .action-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 30px;
        }
        
        .btn-primary:hover {
            background-color: #7d0518;
            border-color: #7d0518;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f0f0f0;
        }
        
        .table tbody tr:hover {
            background-color: rgba(148, 6, 27, 0.02);
        }
        
        .blog-thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .badge-published {
            background-color: var(--success-color);
        }
        
        .badge-draft {
            background-color: var(--warning-color);
        }
        
        .modal-content {
            border-radius: 15px;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
        
        .search-filter {
            margin-bottom: 20px;
        }
        
        .search-input {
            border-radius: 25px;
            padding: 10px 20px;
            border: 1px solid #ddd;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-data i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        /* Pagination Styles */
        .pagination {
            margin: 0;
        }
        
        .pagination .page-link {
            color: var(--primary-color);
            border: 1px solid #dee2e6;
            padding: 8px 15px;
            margin: 0 3px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .pagination .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        
        .pagination-info {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .form-select {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(148, 6, 27, 0.25);
            outline: none;
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.html"><i class="fas fa-pills me-2"></i>Bumar Pharmacy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="data-management.php">Drug Inventory</a></li>
                    <li class="nav-item"><a class="nav-link active" href="blog-management.php">Blog Management</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-size: 0.9rem; font-weight: 500;"><?php echo htmlspecialchars($username); ?></div>
                            <small style="opacity: 0.8;"><?php echo ucfirst($role); ?></small>
                        </div>
                    </div>
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-blog me-3"></i>Blog Management</h1>
            <p class="mb-0">Create, manage, and publish health articles for your pharmacy</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="stat-value" id="totalBlogs"><?php echo $total_blogs; ?></div>
                        <div class="stat-label">Total Blog Posts</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-value" id="publishedBlogs"><?php echo $published_blogs; ?></div>
                        <div class="stat-label">Published Posts</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="stat-value" id="recentBlogs"><?php echo $recent_blogs; ?></div>
                        <div class="stat-label">Posts This Month</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Section -->
        <div class="action-section">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-plus-circle me-2"></i>Create New Blog Post</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBlogModal">
                    <i class="fas fa-plus me-2"></i>New Blog Post
                </button>
            </div>
        </div>

        <!-- Blog Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h4><i class="fas fa-list me-2"></i>All Blog Posts</h4>
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="search-filter">
                        <input type="text" id="searchInput" class="form-control search-input" placeholder="Search blog posts...">
                    </div>
                    <div class="d-flex align-items-center">
                        <label for="entriesPerPage" class="me-2 mb-0" style="white-space: nowrap;">Show:</label>
                        <select id="entriesPerPage" class="form-select" style="width: auto;" onchange="changeEntriesPerPage()">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                        </select>
                        <span class="ms-2 mb-0" style="white-space: nowrap;">entries</span>
                    </div>
                </div>
            </div>

            <?php
            // Pagination settings
            $entries_per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
            $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($current_page - 1) * $entries_per_page;
            
            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM blogs";
            $count_result = $conn->query($count_query);
            $total_blogs = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_blogs / $entries_per_page);
            
            // Get paginated blogs
            $blogs_query = "SELECT * FROM blogs ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($blogs_query);
            $stmt->bind_param("ii", $entries_per_page, $offset);
            $stmt->execute();
            $blogs_result = $stmt->get_result();
            
            if ($blogs_result && $blogs_result->num_rows > 0) {
                echo '<div class="table-responsive">
                        <table class="table table-hover" id="blogsTable">
                            <thead>
                                <tr>
                                    <th width="80px">ID</th>
                                    <th width="100px">Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th width="120px">Status</th>
                                    <th width="150px">Date</th>
                                    <th width="150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>';
                
                while ($blog = $blogs_result->fetch_assoc()) {
                    $status_badge = $blog['status'] === 'published' ? 'badge-published' : 'badge-draft';
                    $date_created = date('M j, Y', strtotime($blog['created_at']));
                    $image_path = !empty($blog['image_url']) ? htmlspecialchars($blog['image_url']) : 'placeholder.jpg';
                    
                    echo "<tr data-id='{$blog['id']}'>
                            <td>{$blog['id']}</td>
                            <td><img src='{$image_path}' class='blog-thumbnail' alt='Blog thumbnail'></td>
                            <td>" . htmlspecialchars($blog['title']) . "</td>
                            <td>" . htmlspecialchars($blog['category']) . "</td>
                            <td><span class='badge {$status_badge}'>" . ucfirst($blog['status']) . "</span></td>
                            <td>{$date_created}</td>
                            <td>
                                <button class='btn btn-sm btn-outline-primary' onclick='editBlog({$blog['id']})'>
                                    <i class='fas fa-edit'></i>
                                </button>
                                <button class='btn btn-sm btn-outline-danger' onclick='deleteBlog({$blog['id']})'>
                                    <i class='fas fa-trash'></i>
                                </button>
                            </td>
                          </tr>";
                }
                
                echo '</tbody></table></div>';
                
                // Pagination info and controls
                $start_entry = $offset + 1;
                $end_entry = min($offset + $entries_per_page, $total_blogs);
                
                echo '<div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                        <div class="pagination-info">
                            Showing ' . $start_entry . ' to ' . $end_entry . ' of ' . $total_blogs . ' entries
                        </div>
                        <nav aria-label="Blog pagination">
                            <ul class="pagination mb-0">';
                
                // Previous button
                if ($current_page > 1) {
                    echo '<li class="page-item">
                            <a class="page-link" href="?page=' . ($current_page - 1) . '&per_page=' . $entries_per_page . '">Previous</a>
                          </li>';
                } else {
                    echo '<li class="page-item disabled">
                            <span class="page-link">Previous</span>
                          </li>';
                }
                
                // Page numbers
                $range = 2; // Number of pages to show on each side of current page
                $start_page = max(1, $current_page - $range);
                $end_page = min($total_pages, $current_page + $range);
                
                // First page
                if ($start_page > 1) {
                    echo '<li class="page-item">
                            <a class="page-link" href="?page=1&per_page=' . $entries_per_page . '">1</a>
                          </li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                // Page numbers in range
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $current_page) {
                        echo '<li class="page-item active">
                                <span class="page-link">' . $i . '</span>
                              </li>';
                    } else {
                        echo '<li class="page-item">
                                <a class="page-link" href="?page=' . $i . '&per_page=' . $entries_per_page . '">' . $i . '</a>
                              </li>';
                    }
                }
                
                // Last page
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item">
                            <a class="page-link" href="?page=' . $total_pages . '&per_page=' . $entries_per_page . '">' . $total_pages . '</a>
                          </li>';
                }
                
                // Next button
                if ($current_page < $total_pages) {
                    echo '<li class="page-item">
                            <a class="page-link" href="?page=' . ($current_page + 1) . '&per_page=' . $entries_per_page . '">Next</a>
                          </li>';
                } else {
                    echo '<li class="page-item disabled">
                            <span class="page-link">Next</span>
                          </li>';
                }
                
                echo '</ul>
                      </nav>
                    </div>';
                
            } else {
                echo '<div class="no-data">
                        <i class="fas fa-blog"></i>
                        <h5>No blog posts yet</h5>
                        <p>Click "New Blog Post" to create your first article</p>
                      </div>';
            }
            
            $stmt->close();
            $conn->close();
            ?>
        </div>
        </div>
    </div>

    <!-- Add Blog Modal -->
    <div class="modal fade" id="addBlogModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Blog Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addBlogForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="blogTitle" class="form-label">Blog Title *</label>
                                <input type="text" class="form-control" id="blogTitle" name="title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="blogCategory" class="form-label">Category *</label>
                                <select class="form-control" id="blogCategory" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Health Tips">Health Tips</option>
                                    <option value="Medication Guide">Medication Guide</option>
                                    <option value="Disease Prevention">Disease Prevention</option>
                                    <option value="Wellness">Wellness</option>
                                    <option value="News">News</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="blogExcerpt" class="form-label">Short Description *</label>
                            <textarea class="form-control" id="blogExcerpt" name="excerpt" rows="2" required></textarea>
                            <small class="text-muted">Brief summary shown on the homepage</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="blogContent" class="form-label">Blog Content *</label>
                            <textarea class="form-control" id="blogContent" name="content" rows="8" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="blogImage" class="form-label">Featured Image</label>
                                <input type="file" class="form-control" id="blogImage" name="image" accept="image/*" onchange="previewImage(this, 'addImagePreview')">
                                <img id="addImagePreview" class="image-preview" alt="Preview">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="blogStatus" class="form-label">Status *</label>
                                <select class="form-control" id="blogStatus" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="blogAuthor" class="form-label">Author Name</label>
                            <input type="text" class="form-control" id="blogAuthor" name="author" value="<?php echo htmlspecialchars($username); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Blog Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Blog Modal -->
    <div class="modal fade" id="editBlogModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Blog Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editBlogForm" enctype="multipart/form-data">
                    <input type="hidden" id="editBlogId" name="blog_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="editBlogTitle" class="form-label">Blog Title *</label>
                                <input type="text" class="form-control" id="editBlogTitle" name="title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="editBlogCategory" class="form-label">Category *</label>
                                <select class="form-control" id="editBlogCategory" name="category" required>
                                    <option value="Health Tips">Health Tips</option>
                                    <option value="Medication Guide">Medication Guide</option>
                                    <option value="Disease Prevention">Disease Prevention</option>
                                    <option value="Wellness">Wellness</option>
                                    <option value="News">News</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editBlogExcerpt" class="form-label">Short Description *</label>
                            <textarea class="form-control" id="editBlogExcerpt" name="excerpt" rows="2" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editBlogContent" class="form-label">Blog Content *</label>
                            <textarea class="form-control" id="editBlogContent" name="content" rows="8" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editBlogImage" class="form-label">Featured Image</label>
                                <input type="file" class="form-control" id="editBlogImage" name="image" accept="image/*" onchange="previewImage(this, 'editImagePreview')">
                                <img id="editImagePreview" class="image-preview" alt="Preview">
                                <input type="hidden" id="currentImage" name="current_image">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editBlogStatus" class="form-label">Status *</label>
                                <select class="form-control" id="editBlogStatus" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editBlogAuthor" class="form-label">Author Name</label>
                            <input type="text" class="form-control" id="editBlogAuthor" name="author">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Set the current entries per page value
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const perPage = urlParams.get('per_page') || '10';
            document.getElementById('entriesPerPage').value = perPage;
        });
        
        // Change entries per page
        function changeEntriesPerPage() {
            const perPage = document.getElementById('entriesPerPage').value;
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('per_page', perPage);
            urlParams.set('page', '1'); // Reset to first page
            window.location.search = urlParams.toString();
        }
        
        // Image preview function
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Add blog form submission
        document.getElementById('addBlogForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('add_blog.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addBlogModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error creating blog post', 'danger');
            });
        });
        
        // Edit blog
        function editBlog(id) {
            fetch(`get_blog.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const blog = data.blog;
                        document.getElementById('editBlogId').value = blog.id;
                        document.getElementById('editBlogTitle').value = blog.title;
                        document.getElementById('editBlogCategory').value = blog.category;
                        document.getElementById('editBlogExcerpt').value = blog.excerpt;
                        document.getElementById('editBlogContent').value = blog.content;
                        document.getElementById('editBlogStatus').value = blog.status;
                        document.getElementById('editBlogAuthor').value = blog.author;
                        document.getElementById('currentImage').value = blog.image_url;
                        
                        if (blog.image_url) {
                            document.getElementById('editImagePreview').src = blog.image_url;
                            document.getElementById('editImagePreview').style.display = 'block';
                        }
                        
                        new bootstrap.Modal(document.getElementById('editBlogModal')).show();
                    } else {
                        showAlert('Error loading blog post', 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Error loading blog post', 'danger');
                });
        }
        
        // Edit form submission
        document.getElementById('editBlogForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('update_blog.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editBlogModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error updating blog post', 'danger');
            });
        });
        
        // Delete blog
        function deleteBlog(id) {
            if (confirm('Are you sure you want to delete this blog post?')) {
                fetch('delete_blog.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        document.querySelector(`tr[data-id="${id}"]`).remove();
                        updateStats();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Error deleting blog post', 'danger');
                });
            }
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#blogsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Update statistics
        function updateStats() {
            fetch('get_blog_stats.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalBlogs').textContent = data.total;
                    document.getElementById('publishedBlogs').textContent = data.published;
                    document.getElementById('recentBlogs').textContent = data.recent;
                })
                .catch(error => console.error('Error updating stats:', error));
        }
        
        // Alert system
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // Check for URL parameters
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const message = urlParams.get('message');
            
            if (status && message) {
                showAlert(decodeURIComponent(message), status);
            }
        });
    </script>
</body>
</html>