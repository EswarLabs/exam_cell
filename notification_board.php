<?php
require_once __DIR__ . '/includes/db_config.php';

$conn = getDBConnection();

$result = $conn->query(
    "SELECT title, description, pdf_path, created_at
     FROM exam_notifications
     WHERE is_active = 1
     ORDER BY created_at DESC"
);

$notifications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['search_text'] = strtolower(trim($row['title'] . ' ' . ($row['description'] ?? '')));
        $notifications[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Notifications - NBKRIST</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #003366;
            --secondary: #0052a3;
            --light: #f5f7fa;
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --border: #e0e6ed;
            --white: #ffffff;
            --shadow-sm: 0 4px 14px rgba(0, 51, 102, 0.08);
            --shadow-md: 0 12px 28px rgba(0, 51, 102, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        [hidden] {
            display: none !important;
        }

        body {
            background: var(--light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
            line-height: 1.5;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            padding: 42px 20px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .page-header p {
            opacity: 0.95;
            font-size: 1rem;
        }

        .content {
            max-width: 980px;
            margin: 0 auto;
            padding: 30px 20px 50px;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: end;
            gap: 16px;
            margin-bottom: 18px;
        }

        .toolbar h2 {
            font-size: 1.45rem;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .toolbar p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .search-wrap {
            position: relative;
            min-width: min(100%, 360px);
            width: 360px;
            max-width: 100%;
        }

        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .search-input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 11px 12px 11px 34px;
            font-size: 0.95rem;
            outline: none;
            background: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .search-input:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(0, 82, 163, 0.1);
        }

        .notifications-grid {
            display: grid;
            gap: 14px;
        }

        .notification-item {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px;
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 16px;
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .notification-item:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .notification-title {
            font-size: 1.08rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 6px;
        }

        .notification-description {
            color: var(--text-secondary);
            font-size: 0.93rem;
            margin-bottom: 10px;
            white-space: pre-line;
        }

        .notification-meta {
            color: var(--text-secondary);
            font-size: 0.84rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-download {
            background: var(--secondary);
            color: var(--white);
            border-radius: 7px;
            border: 1px solid var(--secondary);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 9px 14px;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-download:hover {
            background: #004a94;
            border-color: #004a94;
            color: var(--white);
        }

        .empty-state,
        .filter-empty-state {
            text-align: center;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 40px 24px;
            color: var(--text-secondary);
            box-shadow: var(--shadow-sm);
        }

        .empty-state i,
        .filter-empty-state i {
            font-size: 2rem;
            color: #9eb1c2;
            margin-bottom: 10px;
            display: inline-block;
        }

        .empty-state h3,
        .filter-empty-state h3 {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .filter-empty-state {
            margin-bottom: 12px;
        }

        .page-footer {
            text-align: center;
            padding: 22px 16px 30px;
            color: #5f7286;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 32px 16px;
            }

            .page-header h1 {
                font-size: 1.65rem;
            }

            .content {
                padding: 24px 14px 40px;
            }

            .toolbar {
                align-items: stretch;
            }

            .search-wrap {
                width: 100%;
            }

            .notification-item {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-download {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <header class="page-header">
        <h1>Exam Notifications</h1>
        <p>Official announcements from NBKRIST Examination Cell</p>
    </header>

    <main class="content">
        <?php if (count($notifications) === 0): ?>
            <section class="empty-state">
                <i class="bi bi-inbox"></i>
                <h3>No Notifications</h3>
                <p>There are currently no active notifications. Please check back soon.</p>
            </section>
        <?php else: ?>
            <section class="toolbar">
                <div>
                    <h2>Latest Updates</h2>
                    <p>Simple list view with quick search.</p>
                </div>
                <label class="search-wrap" for="noticeSearch">
                    <i class="bi bi-search"></i>
                    <input id="noticeSearch" class="search-input" type="search" placeholder="Search notifications"
                        autocomplete="off">
                </label>
            </section>

            <section id="filterEmptyState" class="filter-empty-state" hidden>
                <i class="bi bi-search"></i>
                <h3>No matching notifications</h3>
                <p>Try a different keyword.</p>
            </section>

            <section class="notifications-grid" id="notificationList">
                <?php foreach ($notifications as $n): ?>
                    <article class="notification-item"
                        data-search="<?php echo htmlspecialchars($n['search_text'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="notification-content">
                            <h3 class="notification-title"><?php echo htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>

                            <?php if (!empty($n['description'])): ?>
                                <p class="notification-description">
                                    <?php echo htmlspecialchars($n['description'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php endif; ?>

                            <div class="notification-meta">
                                <i class="bi bi-calendar-event"></i>
                                <?php echo date('d M Y, g:i A', strtotime($n['created_at'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($n['pdf_path'])): ?>
                            <a class="btn-download" href="<?php echo htmlspecialchars($n['pdf_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-file-pdf-fill"></i>
                                View PDF
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer class="page-footer">
        <strong>NBKRIST Examination Cell</strong><br>
        &copy; <?php echo date('Y'); ?> All Rights Reserved
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('noticeSearch');
            var cards = Array.prototype.slice.call(document.querySelectorAll('.notification-item'));
            var filterEmptyState = document.getElementById('filterEmptyState');

            if (!searchInput || cards.length === 0 || !filterEmptyState) {
                return;
            }

            function applyFilter() {
                var term = searchInput.value.toLowerCase().trim();
                var visibleCount = 0;

                cards.forEach(function (card) {
                    var text = card.getAttribute('data-search') || '';
                    var match = text.indexOf(term) !== -1;
                    card.hidden = !match;

                    if (match) {
                        visibleCount++;
                    }
                });

                filterEmptyState.hidden = visibleCount !== 0;
            }

            searchInput.addEventListener('input', applyFilter);
            applyFilter();
        });
    </script>

</body>

</html>