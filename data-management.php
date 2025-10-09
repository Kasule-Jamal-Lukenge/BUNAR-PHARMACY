<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Management - Bumar Pharmacy</title>
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
            text-decoration: underline;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .page-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .custom-hr {
            border: none;
            height: 3px;
            background-color: var(--primary-color);
            width: 100px;
            margin: 20px auto;
        }
        
        .upload-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(148, 6, 27, 0.1);
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }
        
        .upload-area {
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            background-color: #fafafa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            background-color: rgba(148, 6, 27, 0.05);
            border-color: #7d0518;
        }
        
        .upload-area.dragover {
            background-color: rgba(148, 6, 27, 0.1);
            border-color: var(--primary-color);
        }
        
        .upload-icon {
            font-size: 3rem;
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
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .data-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(148, 6, 27, 0.1);
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-color), #7d0518);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
            min-width: 150px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            margin: 0;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
            padding: 12px 15px;
            vertical-align: middle;
            border-color: #f0f0f0;
        }
        
        .table tbody tr:hover {
            background-color: rgba(148, 6, 27, 0.02);
        }
        
        .delete-btn {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner-border {
            color: var(--primary-color);
        }
        
        .file-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
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
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(148, 6, 27, 0.25);
        }
        
        @media (max-width: 768px) {
            .stats-cards {
                flex-direction: column;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        
        .bulk-actions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .row-checkbox {
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.html"><i class="fas fa-pills me-2"></i>Bumar Pharmacy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link active" href="data-management.php">Data Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-database me-3"></i>Data Management System</h1>
            <p>Upload, manage, and organize your pharmacy data efficiently</p>
            <hr class="custom-hr">
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Upload Section -->
        <div class="upload-section">
            <div class="section-title">
                <i class="fas fa-cloud-upload-alt"></i>
                Upload Spreadsheet
            </div>
            
            <form id="uploadForm" action="import.php" method="post" enctype="multipart/form-data">
                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-file-excel upload-icon"></i>
                    <h5>Click to select or drag and drop your Excel file</h5>
                    <p class="text-muted">Supported formats: .xls, .xlsx (Max size: 5MB)</p>
                    <input type="file" id="fileInput" name="excel_file" class="file-input" accept=".xls,.xlsx" required>
                </div>
                
                <div class="file-info" id="fileInfo">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file-excel text-success me-2"></i>
                            <span id="fileName"></span>
                            <small class="text-muted ms-2" id="fileSize"></small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Processing your file...</p>
                </div>
                
                <div class="text-center mt-3">
                    <button type="submit" name="import" class="btn btn-primary btn-lg" id="uploadBtn" disabled>
                        <i class="fas fa-upload me-2"></i>Upload & Import Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Section -->
        <div class="data-section">
            <div class="section-title">
                <i class="fas fa-table"></i>
                Stored Records
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <h3 id="totalRecords"><?php
                        // $conn = new mysqli("localhost", "root", "", "bunar_pharmacy");
                        require_once('conn.php');
                        $result = $conn->query("SELECT COUNT(*) as total FROM users");
                        echo $result->fetch_assoc()['total'];
                    ?></h3>
                    <p>Total Records</p>
                </div>
                <div class="stat-card">
                    <h3 id="recentUploads">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as recent FROM users WHERE DATE(created_at) = CURDATE()");
                        echo $result ? $result->fetch_assoc()['recent'] : '0';
                        ?>
                    </h3>
                    <p>Today's Uploads</p>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <div class="search-filter flex-grow-1 me-3">
                    <input type="text" id="searchInput" class="form-control search-input" placeholder="Search records...">
                </div>
                <button class="btn btn-outline-primary" onclick="exportData()">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <button class="btn btn-outline-danger" onclick="toggleBulkActions()">
                    <i class="fas fa-check-square me-2"></i>Bulk Select
                </button>
            </div>
            
            <!-- Bulk Actions -->
            <div class="bulk-actions" id="bulkActions">
                <div class="d-flex justify-content-between align-items-center">
                    <span><strong id="selectedCount">0</strong> records selected</span>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="selectAll()">Select All</button>
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="deselectAll()">Deselect All</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSelected()">
                            <i class="fas fa-trash me-1"></i>Delete Selected
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Data Table -->
            <div class="table-container">
                <?php
                $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
                if ($result && $result->num_rows > 0) {
                    echo '<div class="table-responsive">
                            <table class="table table-hover" id="dataTable">
                                <thead>
                                    <tr>
                                        <th width="50px">
                                            <input type="checkbox" class="form-check-input row-checkbox" id="selectAllCheckbox" style="display:none;">
                                            ID
                                        </th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Date Added</th>
                                        <th width="100px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    while ($row = $result->fetch_assoc()) {
                        $date_added = isset($row['created_at']) ? date('M j, Y', strtotime($row['created_at'])) : 'N/A';
                        echo "<tr data-id='{$row['id']}'>
                                <td>
                                    <input type='checkbox' class='form-check-input row-checkbox record-checkbox' value='{$row['id']}' style='display:none;'>
                                    {$row['id']}
                                </td>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['phone']}</td>
                                <td>{$date_added}</td>
                                <td>
                                    <button class='btn btn-sm btn-outline-danger delete-btn' onclick='deleteRecord({$row['id']})'>
                                        <i class='fas fa-trash'></i>
                                    </button>
                                </td>
                              </tr>";
                    }
                    echo '</tbody></table></div>';
                } else {
                    echo '<div class="no-data">
                            <i class="fas fa-inbox"></i>
                            <h5>No records found</h5>
                            <p>Upload your first spreadsheet to get started</p>
                          </div>';
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // File upload handling
        const fileInput = document.getElementById('fileInput');
        const uploadArea = document.querySelector('.upload-area');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadBtn = document.getElementById('uploadBtn');
        const loadingSpinner = document.getElementById('loadingSpinner');
        
        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });
        
        fileInput.addEventListener('change', handleFileSelect);
        
        function handleFileSelect() {
            const file = fileInput.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileSize.textContent = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileInfo.style.display = 'block';
                uploadBtn.disabled = false;
            }
        }
        
        function clearFile() {
            fileInput.value = '';
            fileInfo.style.display = 'none';
            uploadBtn.disabled = true;
        }
        
        // Form submission with loading
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            loadingSpinner.style.display = 'block';
            uploadBtn.style.display = 'none';
        });
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#dataTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Delete record function
        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this record?')) {
                fetch('delete_record.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`tr[data-id="${id}"]`).remove();
                        showAlert('Record deleted successfully!', 'success');
                        updateStats();
                    } else {
                        showAlert('Error deleting record: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Error deleting record', 'danger');
                });
            }
        }
        
        // Bulk actions
        let bulkMode = false;
        
        function toggleBulkActions() {
            bulkMode = !bulkMode;
            const checkboxes = document.querySelectorAll('.record-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const bulkActions = document.getElementById('bulkActions');
            
            if (bulkMode) {
                checkboxes.forEach(cb => cb.style.display = 'block');
                selectAllCheckbox.style.display = 'block';
                bulkActions.style.display = 'block';
            } else {
                checkboxes.forEach(cb => {
                    cb.style.display = 'none';
                    cb.checked = false;
                });
                selectAllCheckbox.style.display = 'none';
                selectAllCheckbox.checked = false;
                bulkActions.style.display = 'none';
            }
            updateSelectedCount();
        }
        
        function selectAll() {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            checkboxes.forEach(cb => cb.checked = true);
            selectAllCheckbox.checked = true;
            updateSelectedCount();
        }
        
        function deselectAll() {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            checkboxes.forEach(cb => cb.checked = false);
            selectAllCheckbox.checked = false;
            updateSelectedCount();
        }
        
        function updateSelectedCount() {
            const selectedCheckboxes = document.querySelectorAll('.record-checkbox:checked');
            document.getElementById('selectedCount').textContent = selectedCheckboxes.length;
        }
        
        // Add event listeners for checkboxes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('record-checkbox')) {
                updateSelectedCount();
            }
        });
        
        function deleteSelected() {
            const selectedCheckboxes = document.querySelectorAll('.record-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                showAlert('Please select records to delete', 'warning');
                return;
            }
            
            if (confirm(`Are you sure you want to delete ${selectedCheckboxes.length} selected records?`)) {
                const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
                
                fetch('delete_multiple.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ids: ids})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        selectedCheckboxes.forEach(cb => {
                            const row = cb.closest('tr');
                            row.remove();
                        });
                        showAlert(`${data.deleted_count} records deleted successfully!`, 'success');
                        updateStats();
                        updateSelectedCount();
                    } else {
                        showAlert('Error deleting records: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Error deleting records', 'danger');
                });
            }
        }
        
        // Export function
        function exportData() {
            window.location.href = 'export_data.php';
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
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // Update statistics
        function updateStats() {
            fetch('get_stats.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalRecords').textContent = data.total;
                    document.getElementById('recentUploads').textContent = data.recent;
                })
                .catch(error => console.error('Error updating stats:', error));
        }
        
        // Check for URL parameters for alerts
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