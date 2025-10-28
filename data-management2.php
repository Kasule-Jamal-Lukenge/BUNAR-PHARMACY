<?php
    session_start();

    // Checking if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=" . urlencode("Please log in to access this page"));
        exit;
    }

    // Database connection
    require_once('conn.php');

    // Getting statistics
    $total_drugs_query = "SELECT COUNT(*) as total FROM drugs";
    $total_result = $conn->query($total_drugs_query);
    $total_drugs = $total_result->fetch_assoc()['total'];

    $recent_drugs_query = "SELECT COUNT(*) as recent FROM drugs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    $recent_result = $conn->query($recent_drugs_query);
    $recent_drugs = $recent_result->fetch_assoc()['recent'];

    // Getting user info
    $username = $_SESSION['username'] ?? 'User';
    $role = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Inventory Management - Bunar Pharmacy</title>
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
        
        .action-cards {
            margin-bottom: 30px;
        }
        
        .action-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .action-card h4 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .upload-area {
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background-color: #fafafa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            background-color: rgba(148, 6, 27, 0.05);
            border-color: #7d0518;
        }
        
        .upload-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .file-input {
            display: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #7d0518;
            border-color: #7d0518;
        }
        
        .add-drug-form {
            display: flex;
            gap: 10px;
            align-items: end;
        }
        
        .add-drug-form .form-group {
            flex: 1;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-header h4 {
            color: var(--primary-color);
            margin: 0;
            font-weight: bold;
        }
        
        .search-box {
            position: relative;
            max-width: 300px;
        }
        
        .search-box input {
            padding-right: 40px;
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .table {
            margin: 0;
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
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.85rem;
        }
        
        .badge-new {
            background-color: var(--success-color);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: 10px;
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
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .add-drug-form {
                flex-direction: column;
            }
            
            .add-drug-form .form-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.html"><i class="fas fa-pills me-2"></i>Bunar Pharmacy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="data-management.php">Drug Inventory</a></li>
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
            <h1><i class="fas fa-capsules me-3"></i>Drug Inventory Management</h1>
            <p class="mb-0">Manage your pharmacy's drug inventory efficiently</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-value" id="totalDrugs"><?php echo $total_drugs; ?></div>
                        <div class="stat-label">Total Drugs in Database</div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="stat-value" id="recentDrugs"><?php echo $recent_drugs; ?></div>
                        <div class="stat-label">Drugs Added in Past Month</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="action-cards">
            <div class="row">
                <!-- Bulk Upload Section -->
                <div class="col-lg-6 mb-4">
                    <div class="action-card">
                        <h4><i class="fas fa-file-excel me-2"></i>Bulk Upload Drugs</h4>
                        <p class="text-muted">Upload an Excel file containing drug names. The Excel file should have drug names in the first column.</p>
                        
                        <form id="bulkUploadForm" action="import_drugs.php" method="post" enctype="multipart/form-data">
                            <div class="upload-area" onclick="document.getElementById('bulkFileInput').click()">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h5>Click to select or drag and drop</h5>
                                <p class="text-muted mb-0">Excel files only (.xls, .xlsx)</p>
                                <input type="file" id="bulkFileInput" name="excel_file" class="file-input" accept=".xls,.xlsx">
                            </div>
                            
                            <div class="mt-3" id="bulkFileInfo" style="display: none;">
                                <div class="alert alert-info d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-file-excel me-2"></i><span id="bulkFileName"></span></span>
                                    <button type="button" class="btn-close" onclick="clearBulkFile()"></button>
                                </div>
                            </div>
                            
                            <button type="submit" name="bulk_import" class="btn btn-primary w-100 mt-3" id="bulkUploadBtn" disabled>
                                <i class="fas fa-upload me-2"></i>Upload Excel File
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Add Single Drug Section -->
                <div class="col-lg-6 mb-4">
                    <div class="action-card">
                        <h4><i class="fas fa-plus-square me-2"></i>Add Single Drug</h4>
                        <p class="text-muted">Add a new drug to your inventory manually.</p>
                        
                        <form id="addDrugForm" action="add_drug.php" method="post">
                            <div class="add-drug-form">
                                <div class="form-group">
                                    <label for="drugName" class="form-label">Drug Name</label>
                                    <input type="text" class="form-control" id="drugName" name="drug_name" placeholder="Enter drug name" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Drug
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Use this form to add newly manufactured drugs or new inventory items.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drugs Table -->
        <div class="table-container">
            <div class="table-header">
                <h4><i class="fas fa-list me-2"></i>Drug Inventory</h4>
                <div class="d-flex gap-2">
                    <div class="search-box">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search drugs...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <button class="btn btn-outline-primary" onclick="exportDrugs()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </div>

            <?php
            $drugs_query = "SELECT * FROM drugs ORDER BY created_at DESC";
            $drugs_result = $conn->query($drugs_query);
            
            if ($drugs_result && $drugs_result->num_rows > 0) {
                echo '<div class="table-responsive">
                        <table class="table table-hover" id="drugsTable">
                            <thead>
                                <tr>
                                    <th width="80px">ID</th>
                                    <th>Drug Name</th>
                                    <th width="180px">Date Added</th>
                                    <th width="120px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>';
                
                while ($drug = $drugs_result->fetch_assoc()) {
                    $is_new = (strtotime($drug['created_at']) > strtotime('-7 days'));
                    $date_added = date('M j, Y', strtotime($drug['created_at']));
                    
                    echo "<tr data-id='{$drug['id']}'>
                            <td>{$drug['id']}</td>
                            <td>
                                {$drug['drug_name']}
                                " . ($is_new ? '<span class="badge-new">NEW</span>' : '') . "
                            </td>
                            <td>{$date_added}</td>
                            <td>
                                <div class='action-buttons'>
                                    <button class='btn btn-sm btn-outline-primary' onclick='editDrug({$drug['id']}, \"" . addslashes($drug['drug_name']) . "\")'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                    <button class='btn btn-sm btn-outline-danger' onclick='deleteDrug({$drug['id']}, \"" . addslashes($drug['drug_name']) . "\")'>
                                        <i class='fas fa-trash'></i>
                                    </button>
                                </div>
                            </td>
                          </tr>";
                }
                
                echo '</tbody></table></div>';
            } else {
                echo '<div class="no-data">
                        <i class="fas fa-pills"></i>
                        <h5>No drugs in inventory</h5>
                        <p>Upload an Excel file or add drugs manually to get started</p>
                      </div>';
            }
            
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Edit Drug Modal -->
    <div class="modal fade" id="editDrugModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Drug</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editDrugForm" action="update_drug.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editDrugId" name="drug_id">
                        <div class="mb-3">
                            <label for="editDrugName" class="form-label">Drug Name:</label>
                            <input type="text" class="form-control" id="editDrugName" name="drug_name" placeholder="Name of the drug..." required>
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
        // Bulk file upload handling
        const bulkFileInput = document.getElementById('bulkFileInput');
        const bulkFileInfo = document.getElementById('bulkFileInfo');
        const bulkFileName = document.getElementById('bulkFileName');
        const bulkUploadBtn = document.getElementById('bulkUploadBtn');
        
        bulkFileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                bulkFileName.textContent = file.name;
                bulkFileInfo.style.display = 'block';
                bulkUploadBtn.disabled = false;
            }
        });
        
        function clearBulkFile() {
            bulkFileInput.value = '';
            bulkFileInfo.style.display = 'none';
            bulkUploadBtn.disabled = true;
        }
        
        // Adding drug form submission
        document.getElementById('addDrugForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('add_drug.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    document.getElementById('drugName').value = '';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error adding drug', 'danger');
            });
        });
        
        // Searching functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#drugsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Edit drug
        function editDrug(id, name) {
            document.getElementById('editDrugId').value = id;
            document.getElementById('editDrugName').value = name;
            new bootstrap.Modal(document.getElementById('editDrugModal')).show();
        }
        
        // Edit form submission
        document.getElementById('editDrugForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('update_drug.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editDrugModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error updating drug', 'danger');
            });
        });
        
        // Delete drug
        function deleteDrug(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                fetch('delete_drug.php', {
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
                    showAlert('Error deleting drug', 'danger');
                });
            }
        }
        
        // Export function
        function exportDrugs() {
            window.location.href = 'export_drugs.php';
        }
        
        // Update statistics
        function updateStats() {
            fetch('get_drug_stats.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalDrugs').textContent = data.total;
                    document.getElementById('recentDrugs').textContent = data.recent;
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