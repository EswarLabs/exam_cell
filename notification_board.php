<?php
require_once __DIR__ . '/includes/db_config.php';

$conn = getDBConnection();

// Fetch only active notifications, newest first
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
    <title>Exam Notifications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #1e40af;
            --light-blue: #dbeafe;
            --lighter-blue: #f0f9ff;
            --dark-blue: #1e3a8a;
            --border-blue: #bfdbfe;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--text-dark);
        }

        .exam-notification-board {
            padding: 2rem 0;
            min-height: 100vh;
        }

        .notif-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .notif-header {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .notif-header-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .notif-header-icon {
            width: 40px;
            height: 40px;
            background: var(--light-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-size: 1.5rem;
        }

        .notif-header-subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .notif-empty {
            background: var(--white);
            border: 1px solid var(--border-blue);
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            color: var(--text-muted);
        }

        .notif-empty-icon {
            font-size: 3rem;
            color: var(--light-blue);
            margin-bottom: 1rem;
        }

        .notif-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .notif-card {
            background: var(--white);
            border: 1px solid var(--border-blue);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
        }

        .notif-card:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.1);
            transform: translateY(-2px);
        }

        .notif-card-left {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            flex: 1;
        }

        .notif-card-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            background: var(--lighter-blue);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-size: 1.5rem;
        }

        .notif-card-content {
            flex: 1;
        }

        .notif-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .notif-card-description {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }

        .notif-card-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .notif-card-meta i {
            color: var(--primary-blue);
            font-size: 0.9rem;
        }

        .notif-card-right {
            display: flex;
            align-items: center;
        }

        .notif-pdf-btn {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .notif-pdf-btn:hover {
            background: var(--dark-blue);
            color: var(--white);
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(30, 64, 175, 0.2);
        }

        .notif-pdf-btn i {
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .notif-card {
                flex-direction: column;
                padding: 1.25rem;
            }

            .notif-card-right {
                width: 100%;
            }

            .notif-pdf-btn {
                width: 100%;
                justify-content: center;
            }

            .notif-header-title {
                font-size: 1.5rem;
            }

            .notif-header {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>

<!-- =====================================================
     EXAM CELL NOTIFICATION BOARD
     Minimal Clean Design with Blue & White Theme
     ===================================================== -->

<section class="exam-notification-board" id="exam-notifications">
    <div class="notif-container">

        <!-- Header -->
        <div class="notif-header">
            <div class="notif-header-title">
                <div class="notif-header-icon">
                    <i class="bi bi-bell"></i>
                </div>
                <span>Exam Notifications</span>
            </div>
            <p class="notif-header-subtitle">Latest updates from the Exam Cell</p>
        </div>

        <!-- Notifications List -->
        <?php if ($notifications->num_rows === 0): ?>
            <div class="notif-empty">
                <div class="notif-empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p>No notifications available at the moment.</p>
                <p style="font-size: 0.85rem;">Check back soon for updates from the Exam Cell.</p>
            </div>
        <?php else: ?>
            <div class="notif-list">
                <?php while ($n = $notifications->fetch_assoc()): ?>
                <div class="notif-card">
                    <!-- Left: Icon + Content -->
                    <div class="notif-card-left">
                        <div class="notif-card-icon">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <div class="notif-card-content">
                            <div class="notif-card-title">
                                <?php echo htmlspecialchars($n['title']); ?>
                            </div>
                            <?php if (!empty($n['description'])): ?>
                                <div class="notif-card-description">
                                    <?php echo nl2br(htmlspecialchars($n['description'])); ?>
                                </div>
                            <?php endif; ?>
                            <div class="notif-card-meta">
                                <i class="bi bi-calendar2-event"></i>
                                <span><?php echo date('M d, Y', strtotime($n['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: PDF Button -->
                    <?php if (!empty($n['pdf_path'])): ?>
                        <div class="notif-card-right">
                            <a href="<?php echo htmlspecialchars($n['pdf_path']); ?>"
                               target="_blank"
                               class="notif-pdf-btn">
                                <i class="bi bi-download"></i>
                                <span>Download</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- ===== END EXAM CELL NOTIFICATION BOARD ===== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
