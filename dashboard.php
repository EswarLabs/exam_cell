<?php
require_once 'includes/auth.php';
requireLogin();

$conn = getDBConnection();
$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

// Add Notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_notification') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $admin_id    = $_SESSION['exam_admin_id'];

    if (empty($title)) {
        $error = 'Notification title is required.';
        header("Location: dashboard.php?error=" . urlencode($error));
        exit;
    } else {
        $pdf_filename = null;
        $pdf_path     = null;


        if (!empty($_FILES['pdf_file']['name'])) {
            $file     = $_FILES['pdf_file'];
            $allowed  = ['application/pdf'];
            $max_size = 5 * 1024 * 1024; // 5 MB

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'File upload failed. Please try again.';
                header("Location: dashboard.php?error=" . urlencode($error));
                exit;
            } elseif (!in_array($file['type'], $allowed)) {
                $error = 'Only PDF files are allowed.';
                header("Location: dashboard.php?error=" . urlencode($error));
                exit;
            } elseif ($file['size'] > $max_size) {
                $error = 'File size must be under 5 MB.';
                header("Location: dashboard.php?error=" . urlencode($error));
                exit;
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }

        // Generate a safe unique filename
                $ext          = 'pdf';
                $safe_title   = preg_replace('/[^a-zA-Z0-9_-]/', '_', substr($title, 0, 30));
                $pdf_filename = date('Ymd_His') . '_' . $safe_title . '.' . $ext;
                $dest         = UPLOAD_DIR . $pdf_filename;

                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $error = 'Could not save the uploaded file. Check folder permissions.';
                    header("Location: dashboard.php?error=" . urlencode($error));
                    exit;
                } else {
                    $pdf_path = UPLOAD_URL . $pdf_filename;
                }
            }
        }


        $stmt = $conn->prepare(
            "INSERT INTO exam_notifications (title, description, pdf_filename, pdf_path, posted_by)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssi", $title, $description, $pdf_filename, $pdf_path, $admin_id);
        if ($stmt->execute()) {
            $success = 'Notification posted successfully!';
            header("Location: dashboard.php?success=" . urlencode($success));
        } else {
            $error = 'Database error. Could not save notification.';
            header("Location: dashboard.php?error=" . urlencode($error));
        }
        $stmt->close();
        exit;
    }
}

// Delete Notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_notification') {
    $notif_id = (int)($_POST['notif_id'] ?? 0);
    if ($notif_id > 0) {
        $stmt = $conn->prepare("SELECT pdf_filename FROM exam_notifications WHERE id = ?");
        $stmt->bind_param("i", $notif_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!empty($row['pdf_filename'])) {
            $file_path = UPLOAD_DIR . $row['pdf_filename'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $stmt = $conn->prepare("DELETE FROM exam_notifications WHERE id = ?");
        $stmt->bind_param("i", $notif_id);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php?success=" . urlencode('Notification deleted.'));
    }
    exit;
}

// Toggle Active/Inactive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_notification') {
    $notif_id = (int)($_POST['notif_id'] ?? 0);
    if ($notif_id > 0) {
        $conn->query("UPDATE exam_notifications SET is_active = NOT is_active WHERE id = $notif_id");
        header("Location: dashboard.php?success=" . urlencode('Notification status updated.'));
    }
    exit;
}


$notifications = $conn->query(
    "SELECT n.*, a.full_name as posted_by_name
     FROM exam_notifications n
     LEFT JOIN exam_cell_admins a ON n.posted_by = a.id
     ORDER BY n.created_at DESC"
);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Cell Dashboard — NBKRIST</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #003366;
            --secondary: #0052a3;
            --accent: #1a7fd4;
            --light: #f5f7fa;
            --lighter: #ecf0f5;
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --border: #e0e6ed;
            --white: #ffffff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--lighter);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
        }

        /* Topbar */
        .topbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 51, 102, 0.15);
        }

        .topbar .brand {
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 0.5px;
            color: var(--white);
        }

        .topbar .brand span {
            opacity: 0.9;
        }

        .topbar .user-info {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* Accent bar */
        .accent-bar {
            height: 0;
        }

        /* Cards */
        .section-card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .section-card .card-header {
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(26, 127, 212, 0.05) 100%);
            color: var(--primary);
            border-bottom: 2px solid var(--accent);
            padding: 1.2rem 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
        }

        .section-card .card-body {
            padding: 2rem;
        }

        /* Form */
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
            margin-bottom: 0.6rem;
        }

        .form-control,
        .form-select {
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 0.7rem 0.9rem;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(26, 127, 212, 0.1);
            outline: none;
        }

        .form-text {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            border: none;
            color: var(--white);
            padding: 0.75rem 1.8rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #004080 0%, #1a6fb8 100%);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 51, 102, 0.2);
        }

        /* Notification table */
        .notif-table thead th {
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(26, 127, 212, 0.05) 100%);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--accent);
            padding: 0.85rem;
        }

        .notif-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }

        .notif-table tbody tr:hover {
            background: rgba(26, 127, 212, 0.02);
        }

        .notif-table td {
            padding: 0.85rem;
            vertical-align: middle;
        }

        .badge-active {
            background: #e3f2fd;
            color: #1a7fd4;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }

        .badge-inactive {
            background: #f3e5f5;
            color: #7c3aed;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }

        .badge-nopdf {
            background: #eeeeee;
            color: #616161;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }

        .btn-sm {
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 6px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-outline-secondary {
            color: var(--accent);
            border: 1px solid var(--accent);
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background: rgba(26, 127, 212, 0.1);
            border-color: var(--accent);
            color: var(--accent);
        }

        .btn-outline-danger {
            color: #dc3545;
            border: 1px solid #dc3545;
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
            color: #dc3545;
        }

        /* Stats */
        .stat-box {
            background: var(--white);
            border-radius: 12px;
            padding: 1.8rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            border-left: 4px solid var(--accent);
            transition: all 0.3s;
        }

        .stat-box:hover {
            box-shadow: 0 6px 16px rgba(0, 51, 102, 0.15);
            transform: translateY(-3px);
        }

        .stat-box .num {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 0.6rem;
        }

        .stat-box .label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        /* Alerts */
        .alert {
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--lighter);
            color: var(--accent);
            padding: 1rem;
        }

        .alert-success {
            background: #e8f5e9;
            border-color: #c8e6c9;
            color: #2e7d32;
        }

        .alert-danger {
            background: #ffebee;
            border-color: #ffcdd2;
            color: #c62828;
        }

        .btn-close {
            opacity: 0.6;
            transition: opacity 0.3s;
            color: inherit;
        }

        .btn-close:hover {
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .topbar {
                flex-direction: column;
                gap: 1rem;
            }

            .section-card .card-body {
                padding: 1rem;
            }

            .notif-table {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="brand">NBKRIST &mdash; <span>Exam Cell Admin</span></div>
    <div class="d-flex align-items-center gap-3">
        <span class="user-info d-none d-md-block">
            <i class="bi bi-person-circle me-1"></i>
            <?php echo htmlspecialchars($_SESSION['exam_admin_name']); ?>
        </span>
        <form method="POST" action="logout.php" style="margin: 0;">
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</div>
<div class="accent-bar"></div>

<div class="container-fluid py-4 px-4">
    <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show py-2">
        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2">
        <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php
    $conn2 = getDBConnection();
    $total = $conn2->query("SELECT COUNT(*) as c FROM exam_notifications")->fetch_assoc()['c'];
    $active = $conn2->query("SELECT COUNT(*) as c FROM exam_notifications WHERE is_active=1")->fetch_assoc()['c'];
    $pdfs = $conn2->query("SELECT COUNT(*) as c FROM exam_notifications WHERE pdf_filename IS NOT NULL")->fetch_assoc()['c'];
    $conn2->close();
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-box">
                <div class="num"><?php echo $total; ?></div>
                <div class="label">Total Notifications</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-box" style="border-left-color:#10b981;">
                <div class="num" style="color:#10b981;"><?php echo $active; ?></div>
                <div class="label">Active</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-box" style="border-left-color:#0ea5e9;">
                <div class="num" style="color:#0ea5e9;"><?php echo $pdfs; ?></div>
                <div class="label">With PDF Attachment</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-box" style="border-left-color:#6b7280;">
                <div class="num" style="color:#6b7280;"><?php echo ($total - $active); ?></div>
                <div class="label">Inactive</div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-header">
            <i class="bi bi-plus-circle"></i> Post New Notification
        </div>
        <div class="card-body p-4">
            <form method="POST" action="dashboard.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_notification">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Notification Title <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            name="title"
                            placeholder="e.g. Mid-Term Examination Timetable – Nov 2024"
                            maxlength="255"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Attach PDF (optional)</label>
                        <input type="file" class="form-control" name="pdf_file" accept=".pdf">
                        <div class="form-text">Max file size: 5 MB. PDF only.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description / Details (optional)</label>
                        <textarea
                            class="form-control"
                            name="description"
                            rows="2"
                            placeholder="Short description visible on the notification board..."
                            maxlength="1000"
                        ></textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="bi bi-send me-1"></i>Post Notification
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card">
        <div class="card-header">
            <i class="bi bi-bell"></i> All Notifications
            <span class="ms-auto badge bg-light text-dark"><?php echo $total; ?> total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table notif-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:40%">Title</th>
                            <th>Date Posted</th>
                            <th>PDF</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($notifications->num_rows === 0): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                    No notifications yet. Add one above!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($n = $notifications->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($n['title']); ?></strong>
                                    <?php if (!empty($n['description'])): ?>
                                        <div class="text-muted small mt-1" style="font-size:0.78rem;">
                                            <?php echo htmlspecialchars(substr($n['description'], 0, 90)) . (strlen($n['description']) > 90 ? '…' : ''); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted align-middle">
                                    <?php echo date('d M Y', strtotime($n['created_at'])); ?>
                                    <br><?php echo date('h:i A', strtotime($n['created_at'])); ?>
                                </td>
                                <td class="align-middle">
                                    <?php if (!empty($n['pdf_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($n['pdf_path']); ?>" target="_blank"
                                           class="badge badge-active text-decoration-none">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>View PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-nopdf">No PDF</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <span class="badge <?php echo $n['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?php echo $n['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="align-middle text-end">
                                    <!-- Toggle Active -->
                                    <form method="POST" action="dashboard.php" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_notification">
                                        <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                title="<?php echo $n['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="bi bi-<?php echo $n['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form method="POST" action="dashboard.php" class="d-inline"
                                          onsubmit="return confirm('Delete this notification? This cannot be undone.');">
                                        <input type="hidden" name="action" value="delete_notification">
                                        <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
