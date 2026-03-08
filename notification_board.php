<?php
require_once __DIR__ . '/includes/db_config.php';

$conn = getDBConnection();

$notifications = $conn->query(
    "SELECT title, description, pdf_path, created_at
     FROM exam_notifications
     WHERE is_active = 1
     ORDER BY created_at DESC"
);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Notifications - NBKRIST</title>
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, #003366 0%, #0052a3 100%);
            color: var(--white);
            padding: 50px 20px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            right: -100px;
            top: -100px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .page-header::after {
            content: '';
            position: absolute;
            left: -80px;
            bottom: -80px;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .header-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.95;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.95;
            margin: 0;
        }

        /* Main Content */
        .notifications-section {
            padding: 50px 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .notifications-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .notifications-subtitle {
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 1rem;
        }

        .notifications-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .notification-item {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
            transition: all 0.3s ease;
            display: flex;
            align-items: flex-start;
            gap: 25px;
        }

        .notification-item:hover {
            border-color: var(--accent);
            box-shadow: 0 8px 25px rgba(0, 51, 102, 0.15);
            transform: translateY(-4px);
        }

        .notification-icon {
            width: 60px;
            height: 60px;
            min-width: 60px;
            background: linear-gradient(135deg, rgba(26, 127, 212, 0.1) 0%, rgba(0, 82, 163, 0.1) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
            font-size: 1.8rem;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .notification-description {
            color: var(--text-secondary);
            margin-bottom: 12px;
            line-height: 1.8;
            font-size: 0.95rem;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .notification-meta i {
            color: var(--accent);
            font-size: 0.9rem;
        }

        .notification-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-download {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-download:hover {
            background: linear-gradient(135deg, #004080 0%, #1a6fb8 100%);
            color: var(--white);
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 51, 102, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--lighter);
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .empty-text {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Footer */
        .page-footer {
            background: linear-gradient(135deg, #003366 0%, #0052a3 100%);
            color: var(--white);
            text-align: center;
            padding: 30px 20px;
            margin-top: 50px;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }

            .page-header p {
                font-size: 0.95rem;
            }

            .notification-item {
                flex-direction: column;
                padding: 20px;
                gap: 15px;
            }

            .notification-title {
                font-size: 1.1rem;
            }

            .notification-actions {
                width: 100%;
            }

            .btn-download {
                width: 100%;
                justify-content: center;
            }

            .notifications-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="bi bi-megaphone-fill"></i>
        </div>
        <h1>Exam Notifications</h1>
        <p>Official announcements from NBKRIST Examination Cell</p>
    </div>
</div>

<!-- Main Content -->
<section class="notifications-section">
    <?php if ($notifications->num_rows === 0): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <i class="bi bi-inbox"></i>
        </div>
        <div class="empty-title">No Notifications</div>
        <p class="empty-text">There are currently no active notifications. Please check back soon for updates.</p>
    </div>
    <?php else: ?>
    <h2 class="notifications-title">Latest Updates</h2>
    <p class="notifications-subtitle">
        <i class="bi bi-info-circle"></i> 
        Stay informed with the latest exam-related announcements
    </p>
    <div class="notifications-grid">
        <?php while ($n = $notifications->fetch_assoc()): ?>
        <div class="notification-item">
            <div class="notification-icon">
                <i class="bi bi-bell-fill"></i>
            </div>
            <div class="notification-content">
                <h3 class="notification-title"><?php echo htmlspecialchars($n['title']); ?></h3>
                <?php if (!empty($n['description'])): ?>
                <p class="notification-description">
                    <?php echo nl2br(htmlspecialchars($n['description'])); ?>
                </p>
                <?php endif; ?>
                <div class="notification-meta">
                    <span>
                        <i class="bi bi-calendar-event"></i>
                        <?php echo date('d M Y, g:i A', strtotime($n['created_at'])); ?>
                    </span>
                </div>
            </div>
            <?php if (!empty($n['pdf_path'])): ?>
            <div class="notification-actions">
                <a href="<?php echo htmlspecialchars($n['pdf_path']); ?>"
                   target="_blank"
                   class="btn-download">
                    <i class="bi bi-file-pdf-fill"></i>
                    PDF
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Footer -->
<div class="page-footer">
    <strong>NBKRIST Examination Cell</strong> | Keeping You Informed
    <br><small>&copy; <?php echo date('Y'); ?> All Rights Reserved</small>
</div>

</body>
</html>
