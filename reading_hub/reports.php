<?php
require_once 'header.php';

// Redirect if not a librarian
if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

// Fetch data for reports
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_students = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'];
$active_loans = $conn->query("SELECT COUNT(*) as count FROM loans WHERE status IN ('borrowed', 'overdue')")->fetch_assoc()['count'];

// Check if penalties table exists and get total
$total_penalties = 0;
$check_penalties = $conn->query("SHOW TABLES LIKE 'penalties'");
if ($check_penalties->num_rows > 0) {
    $penalty_result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM penalties WHERE status = 'pending'");
    $total_penalties = $penalty_result->fetch_assoc()['total'];
}

// Get most borrowed books (Top 10) - FIXED: removed author column
$popular_books = $conn->query("
    SELECT 
        b.book_id,
        b.title,
        b.year_level,
        b.quantity_total,
        b.quantity_available,
        COUNT(l.loan_id) as borrow_count
    FROM books b 
    LEFT JOIN loans l ON b.book_id = l.book_id 
    GROUP BY b.book_id 
    ORDER BY borrow_count DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get top borrowers (Top 10 users with most books borrowed) - FIXED: changed student_id to user_id
$top_borrowers = $conn->query("
    SELECT 
        u.user_id,
        u.username,
        u.full_name,
        u.year_level,
        u.email,
        COUNT(l.loan_id) as total_borrowed,
        COUNT(CASE WHEN l.status IN ('borrowed', 'overdue') THEN 1 END) as currently_borrowed,
        COUNT(CASE WHEN l.status = 'returned' THEN 1 END) as books_returned
    FROM users u 
    LEFT JOIN loans l ON u.user_id = l.user_id 
    WHERE u.role = 'student'
    GROUP BY u.user_id 
    ORDER BY total_borrowed DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get overdue books - FIXED: removed author column and changed student_id to user_id
$overdue_books = $conn->query("
    SELECT 
        b.title, 
        u.full_name, 
        u.username,
        u.year_level,
        l.due_date, 
        DATEDIFF(CURDATE(), l.due_date) as days_overdue
    FROM loans l 
    JOIN books b ON l.book_id = b.book_id 
    JOIN users u ON l.user_id = u.user_id 
    WHERE l.status = 'overdue' 
    ORDER BY days_overdue DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get borrowing statistics by year level - FIXED: changed student_id to user_id
$year_level_stats = $conn->query("
    SELECT 
        u.year_level,
        COUNT(l.loan_id) as borrow_count,
        COUNT(DISTINCT u.user_id) as student_count
    FROM users u 
    LEFT JOIN loans l ON u.user_id = l.user_id 
    WHERE u.role = 'student'
    GROUP BY u.year_level
    ORDER BY borrow_count DESC
")->fetch_all(MYSQLI_ASSOC);

// Get monthly borrowing statistics for charts
$monthly_stats = $conn->query("
    SELECT 
        DATE_FORMAT(borrow_date, '%Y-%m') as month,
        DATE_FORMAT(borrow_date, '%b %Y') as month_name,
        COUNT(*) as borrow_count
    FROM loans 
    WHERE borrow_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(borrow_date, '%Y-%m'), month_name
    ORDER BY month
")->fetch_all(MYSQLI_ASSOC);

// Get book availability statistics
$availability_stats = $conn->query("
    SELECT 
        COUNT(*) as total_books,
        SUM(quantity_available) as available_copies,
        SUM(quantity_total - quantity_available) as borrowed_copies,
        ROUND((SUM(quantity_available) / SUM(quantity_total)) * 100, 2) as availability_rate
    FROM books
")->fetch_assoc();

// Prepare data for JavaScript
$top_borrowers_data = [];
foreach ($top_borrowers as $borrower) {
    $top_borrowers_data[] = [
        'name' => $borrower['full_name'] ?: $borrower['username'],
        'username' => $borrower['username'],
        'year_level' => $borrower['year_level'],
        'borrowed' => (int)$borrower['total_borrowed'],
        'current' => (int)$borrower['currently_borrowed'],
        'returned' => (int)$borrower['books_returned']
    ];
}

$popular_books_data = [];
foreach ($popular_books as $book) {
    $popular_books_data[] = [
        'title' => $book['title'],
        'year_level' => $book['year_level'],
        'borrow_count' => (int)$book['borrow_count'],
        'available' => (int)$book['quantity_available'],
        'total' => (int)$book['quantity_total']
    ];
}

$year_level_data = [];
foreach ($year_level_stats as $stat) {
    $year_level_data[] = [
        'level' => $stat['year_level'],
        'borrow_count' => (int)$stat['borrow_count'],
        'student_count' => (int)$stat['student_count']
    ];
}

$monthly_stats_data = [];
foreach ($monthly_stats as $stat) {
    $monthly_stats_data[] = [
        'month' => $stat['month_name'],
        'count' => (int)$stat['borrow_count']
    ];
}
?>

<link rel="stylesheet" href="reports.css">
<!-- Add Chart.js for graphs -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="reports-container">
    <div class="reports-content">
        <div class="reports-header">
            <i data-lucide="bar-chart-3" class="header-icon"></i>
            <h2>Library Reports & Analytics</h2>
            <p>Generate comprehensive reports and analytics for library management</p>
        </div>

        <!-- Quick Stats -->
        <div class="stats-cards">
            <div class="stat-card">
                <i data-lucide="book-open" class="stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_books); ?></div>
                    <div class="stat-label">Total Books</div>
                </div>
            </div>
            <div class="stat-card">
                <i data-lucide="users" class="stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_students); ?></div>
                    <div class="stat-label">Active Students</div>
                </div>
            </div>
            <div class="stat-card">
                <i data-lucide="book-check" class="stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($active_loans); ?></div>
                    <div class="stat-label">Active Loans</div>
                </div>
            </div>
            <div class="stat-card">
                <i data-lucide="award" class="stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-value"><?php echo !empty($top_borrowers) ? $top_borrowers[0]['total_borrowed'] : '0'; ?></div>
                    <div class="stat-label">Top Borrower</div>
                </div>
            </div>
        </div>

        <!-- Library Overview -->
        <div class="overview-section">
            <div class="overview-card">
                <h4><i data-lucide="library" class="overview-icon"></i> Library Overview</h4>
                <div class="overview-stats">
                    <div class="overview-stat">
                        <span class="overview-label">Availability Rate</span>
                        <span class="overview-value"><?php echo $availability_stats['availability_rate']; ?>%</span>
                    </div>
                    <div class="overview-stat">
                        <span class="overview-label">Available Copies</span>
                        <span class="overview-value"><?php echo $availability_stats['available_copies']; ?></span>
                    </div>
                    <div class="overview-stat">
                        <span class="overview-label">Borrowed Copies</span>
                        <span class="overview-value"><?php echo $availability_stats['borrowed_copies']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h4><i data-lucide="trophy" class="chart-icon"></i> Top 10 Borrowers</h4>
                    <p>Students with the most books borrowed</p>
                </div>
                <div class="chart-container">
                    <canvas id="topBorrowersChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h4><i data-lucide="book-open" class="chart-icon"></i> Most Borrowed Books</h4>
                    <p>Top 10 popular books</p>
                </div>
                <div class="chart-container">
                    <canvas id="popularBooksChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h4><i data-lucide="graduation-cap" class="chart-icon"></i> Borrowing by Year Level</h4>
                    <p>Reading activity per grade level</p>
                </div>
                <div class="chart-container">
                    <canvas id="yearLevelChart"></canvas>
                </div>
            </div>

            <div class="chart-card full-width">
                <div class="chart-header">
                    <h4><i data-lucide="calendar" class="chart-icon"></i> Monthly Borrowing Trends</h4>
                    <p>Last 6 months borrowing activity</p>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Report Cards Grid -->
        <div class="reports-grid">
            <!-- Top Borrowers Report -->
            <div class="report-card">
                <div class="report-card-header">
                    <i data-lucide="award" class="report-icon"></i>
                    <h4>Top Borrowers Report</h4>
                </div>
                <div class="report-card-content">
                    <p>Detailed analysis of top readers and their borrowing patterns.</p>
                    <div class="report-metrics">
                        <div class="metric">
                            <span class="metric-value"><?php echo count($top_borrowers); ?></span>
                            <span class="metric-label">Top Readers</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo !empty($top_borrowers) ? $top_borrowers[0]['total_borrowed'] : '0'; ?></span>
                            <span class="metric-label">Highest Count</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn-report" onclick="generateReport('top_borrowers')">
                            <i data-lucide="download"></i>
                            Generate PDF
                        </button>
                        <button class="btn-report-view" onclick="viewTopBorrowers()">
                            <i data-lucide="eye"></i>
                            View Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Popular Books Report -->
            <div class="report-card">
                <div class="report-card-header">
                    <i data-lucide="book-open" class="report-icon"></i>
                    <h4>Popular Books Report</h4>
                </div>
                <div class="report-card-content">
                    <p>Analysis of most frequently borrowed books and reading trends.</p>
                    <div class="report-metrics">
                        <div class="metric">
                            <span class="metric-value"><?php echo count($popular_books); ?></span>
                            <span class="metric-label">Top Books</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo !empty($popular_books) ? $popular_books[0]['borrow_count'] : '0'; ?></span>
                            <span class="metric-label">Most Borrowed</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn-report" onclick="generateReport('popular_books')">
                            <i data-lucide="download"></i>
                            Generate PDF
                        </button>
                        <button class="btn-report-view" onclick="viewPopularBooks()">
                            <i data-lucide="eye"></i>
                            View Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Year Level Report -->
            <div class="report-card">
                <div class="report-card-header">
                    <i data-lucide="graduation-cap" class="report-icon"></i>
                    <h4>Year Level Report</h4>
                </div>
                <div class="report-card-content">
                    <p>Reading activity analysis by grade level and student engagement.</p>
                    <div class="report-metrics">
                        <div class="metric">
                            <span class="metric-value"><?php echo count($year_level_stats); ?></span>
                            <span class="metric-label">Year Levels</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo !empty($year_level_stats) ? $year_level_stats[0]['year_level'] : 'N/A'; ?></span>
                            <span class="metric-label">Most Active</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn-report" onclick="generateReport('year_level')">
                            <i data-lucide="download"></i>
                            Generate PDF
                        </button>
                        <button class="btn-report-view" onclick="viewYearLevelStats()">
                            <i data-lucide="eye"></i>
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Preview Section -->
        <div class="data-preview-section">
            <div class="preview-card">
                <h4><i data-lucide="trophy" class="preview-icon"></i> Top 5 Borrowers</h4>
                <div class="preview-list">
                    <?php if (!empty($top_borrowers)): ?>
                        <?php foreach (array_slice($top_borrowers, 0, 5) as $index => $borrower): ?>
                            <div class="preview-item">
                                <span class="preview-rank">#<?php echo $index + 1; ?></span>
                                <div class="preview-info">
                                    <span class="preview-title"><?php echo htmlspecialchars($borrower['full_name'] ?: $borrower['username']); ?></span>
                                    <span class="preview-meta"><?php echo $borrower['year_level']; ?> • <?php echo $borrower['currently_borrowed']; ?> current</span>
                                </div>
                                <span class="preview-count"><?php echo $borrower['total_borrowed']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="preview-item">No borrowing data available</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="preview-card">
                <h4><i data-lucide="book-open" class="preview-icon"></i> Top 5 Popular Books</h4>
                <div class="preview-list">
                    <?php if (!empty($popular_books)): ?>
                        <?php foreach (array_slice($popular_books, 0, 5) as $index => $book): ?>
                            <div class="preview-item">
                                <span class="preview-rank">#<?php echo $index + 1; ?></span>
                                <div class="preview-info">
                                    <span class="preview-title"><?php echo htmlspecialchars($book['title']); ?></span>
                                    <span class="preview-meta"><?php echo $book['year_level']; ?> • Available: <?php echo $book['quantity_available']; ?>/<?php echo $book['quantity_total']; ?></span>
                                </div>
                                <span class="preview-count"><?php echo $book['borrow_count']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="preview-item">No borrowing data available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Chart data from PHP
    const topBorrowersData = <?php echo json_encode($top_borrowers_data); ?>;
    const popularBooksData = <?php echo json_encode($popular_books_data); ?>;
    const yearLevelData = <?php echo json_encode($year_level_data); ?>;
    const monthlyStatsData = <?php echo json_encode($monthly_stats_data); ?>;

    // Initialize charts when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        lucide.createIcons();
    });

    function initializeCharts() {
        // Top Borrowers Chart (Horizontal Bar)
        const topBorrowersCtx = document.getElementById('topBorrowersChart').getContext('2d');
        new Chart(topBorrowersCtx, {
            type: 'bar',
            data: {
                labels: topBorrowersData.map(b => b.name.length > 15 ? b.name.substring(0, 15) + '...' : b.name),
                datasets: [{
                    label: 'Total Books Borrowed',
                    data: topBorrowersData.map(b => b.borrowed),
                    backgroundColor: '#BD1B19',
                    borderColor: '#BD1B19',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const borrower = topBorrowersData[context.dataIndex];
                                return [
                                    `Total: ${borrower.borrowed} books`,
                                    `Current: ${borrower.current} books`,
                                    `Year: ${borrower.year_level}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Books'
                        }
                    }
                }
            }
        });

        // Popular Books Chart (Bar)
        const popularBooksCtx = document.getElementById('popularBooksChart').getContext('2d');
        new Chart(popularBooksCtx, {
            type: 'bar',
            data: {
                labels: popularBooksData.map(b => b.title.length > 20 ? b.title.substring(0, 20) + '...' : b.title),
                datasets: [{
                    label: 'Times Borrowed',
                    data: popularBooksData.map(b => b.borrow_count),
                    backgroundColor: '#D89233',
                    borderColor: '#D89233',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                const index = tooltipItems[0].dataIndex;
                                return popularBooksData[index].title;
                            },
                            label: function(context) {
                                const book = popularBooksData[context.dataIndex];
                                return [
                                    `Borrowed: ${book.borrow_count} times`,
                                    `Available: ${book.available}/${book.total} copies`,
                                    `Year Level: ${book.year_level}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Times Borrowed'
                        }
                    }
                }
            }
        });

        // Year Level Chart (Doughnut)
        const yearLevelCtx = document.getElementById('yearLevelChart').getContext('2d');
        new Chart(yearLevelCtx, {
            type: 'doughnut',
            data: {
                labels: yearLevelData.map(y => y.level),
                datasets: [{
                    data: yearLevelData.map(y => y.borrow_count),
                    backgroundColor: [
                        '#BD1B19',
                        '#D89233',
                        '#3b82f6',
                        '#10b981',
                        '#8b5cf6'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const level = yearLevelData[context.dataIndex];
                                return [
                                    `Borrowed: ${level.borrow_count} books`,
                                    `Students: ${level.student_count}`
                                ];
                            }
                        }
                    }
                }
            }
        });

        // Monthly Trends Chart (Line)
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: monthlyStatsData.map(m => m.month),
                datasets: [{
                    label: 'Books Borrowed',
                    data: monthlyStatsData.map(m => m.count),
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Books'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }

    // View functions
    function viewTopBorrowers() {
        Swal.fire({
            title: 'Top Borrowers Details',
            html: `<div style="text-align: left; max-height: 500px; overflow-y: auto;">
                <h4 style="color: #BD1B19; margin-bottom: 15px;">Top 10 Readers</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <strong>Total Top Readers:</strong> ${topBorrowersData.length}<br>
                    <strong>Highest Count:</strong> ${topBorrowersData[0]?.borrowed || 0} books
                </div>
                <div class="borrowers-list">
                    ${topBorrowersData.map((borrower, index) => `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: ${index % 2 === 0 ? '#f8f9fa' : 'white'}; border-radius: 6px; margin-bottom: 8px; border-left: 4px solid #BD1B19;">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #BD1B19;">#${index + 1} ${borrower.name}</div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    Username: ${borrower.username} • ${borrower.year_level}<br>
                                    Total: ${borrower.borrowed} books • Current: ${borrower.current} books • Returned: ${borrower.returned} books
                                </div>
                            </div>
                            <div style="font-weight: 700; color: #D89233; font-size: 1.2rem;">${borrower.borrowed}</div>
                        </div>
                    `).join('')}
                </div>
            </div>`,
            width: 700,
            confirmButtonText: 'Close',
            confirmButtonColor: '#6b7280'
        });
    }

    function viewPopularBooks() {
        Swal.fire({
            title: 'Popular Books Details',
            html: `<div style="text-align: left; max-height: 500px; overflow-y: auto;">
                <h4 style="color: #D89233; margin-bottom: 15px;">Top 10 Most Borrowed Books</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <strong>Total Books Tracked:</strong> ${popularBooksData.length}<br>
                    <strong>Most Popular:</strong> ${popularBooksData[0]?.borrow_count || 0} times
                </div>
                <div class="books-list">
                    ${popularBooksData.map((book, index) => `
                        <div style="padding: 12px; background: ${index % 2 === 0 ? '#f8f9fa' : 'white'}; border-radius: 6px; margin-bottom: 8px; border-left: 4px solid #D89233;">
                            <div style="font-weight: 600; color: #1f2937; font-size: 1.1rem;">#${index + 1} ${book.title}</div>
                            <div style="font-size: 0.9rem; color: #666; margin: 4px 0;">
                                <strong>Year Level:</strong> ${book.year_level}<br>
                                <strong>Availability:</strong> ${book.available}/${book.total} copies available
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                <span style="font-size: 0.8rem; color: #BD1B19; font-weight: 600;">Times Borrowed</span>
                                <span style="font-weight: 700; color: #BD1B19; font-size: 1.1rem;">${book.borrow_count}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>`,
            width: 700,
            confirmButtonText: 'Close',
            confirmButtonColor: '#6b7280'
        });
    }

    function viewYearLevelStats() {
        Swal.fire({
            title: 'Year Level Statistics',
            html: `<div style="text-align: left; max-height: 500px; overflow-y: auto;">
                <h4 style="color: #3b82f6; margin-bottom: 15px;">Reading Activity by Year Level</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <strong>Total Year Levels:</strong> ${yearLevelData.length}<br>
                    <strong>Most Active:</strong> ${yearLevelData[0]?.level || 'N/A'} with ${yearLevelData[0]?.borrow_count || 0} books
                </div>
                <div class="year-level-list">
                    ${yearLevelData.map((level, index) => `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: ${index % 2 === 0 ? '#f8f9fa' : 'white'}; border-radius: 6px; margin-bottom: 8px; border-left: 4px solid #3b82f6;">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1f2937; font-size: 1.1rem;">${level.level}</div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    Students: ${level.student_count} • Average: ${Math.round(level.borrow_count / Math.max(1, level.student_count))} books per student
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: #3b82f6; font-size: 1.2rem;">${level.borrow_count}</div>
                                <div style="font-size: 0.8rem; color: #666;">books total</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>`,
            width: 600,
            confirmButtonText: 'Close',
            confirmButtonColor: '#6b7280'
        });
    }

    function generateReport(type) {
        Swal.fire({
            title: 'Generating Report',
            html: `<div style="text-align: left;">
                <p>Generating ${type.replace('_', ' ')} report...</p>
                <div class="report-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                </div>
            </div>`,
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                const progressBar = Swal.getHtmlContainer().querySelector('.progress-fill');
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    progressBar.style.width = `${progress}%`;
                    if (progress >= 100) {
                        clearInterval(interval);
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Report Generated!',
                                html: `<div style="text-align: left;">
                                    <p>Your ${type.replace('_', ' ')} report has been generated successfully.</p>
                                    <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 15px 0;">
                                        <strong>Report Type:</strong> ${type.replace('_', ' ').toUpperCase()}<br>
                                        <strong>Generated:</strong> ${new Date().toLocaleString()}<br>
                                        <strong>Records:</strong> ${getReportRecordCount(type)}<br>
                                        <strong>Format:</strong> PDF
                                    </div>
                                </div>`,
                                showCancelButton: true,
                                confirmButtonText: 'Download PDF',
                                cancelButtonText: 'View Online',
                                confirmButtonColor: '#10b981'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    Swal.fire('Download Started!', 'Your report download has started.', 'info');
                                }
                            });
                        }, 500);
                    }
                }, 100);
            }
        });
    }

    function getReportRecordCount(type) {
        switch(type) {
            case 'top_borrowers': return topBorrowersData.length;
            case 'popular_books': return popularBooksData.length;
            case 'year_level': return yearLevelData.length;
            default: return 'N/A';
        }
    }
</script>

<?php
require_once 'footer.php';
?>