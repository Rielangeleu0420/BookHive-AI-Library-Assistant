<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirectToDashboard();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to BookHive</title>
    <style>
        :root {
            --primary: #BD1B19;
            --primary-dark: #A01513;
            --secondary: #D89233;
            --accent: #E8B14A;
            --background: #E7DFD2;
            --card-bg: #FDF9F3;
            --text-primary: #2B2B2B;
            --text-secondary: #666666;
            --text-muted: #8B7355;
            --border: #E5E5E5;
            --success: #28A745;
            --danger: #DC3545;
            --warning: #FFC107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .min-h-screen {
            min-height: 100vh;
        }

        /* Header Styles */
        .bg-primary {
            background: var(--primary) !important;
        }

        .border-b {
            border-bottom: 1px solid;
        }

        .border-white\/20 {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .px-6 {
            padding-left: 24px;
            padding-right: 24px;
        }

        .py-4 {
            padding-top: 16px;
            padding-bottom: 16px;
        }

        .shadow-sm {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .max-w-7xl {
            max-width: 1280px;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .space-x-3 > * + * {
            margin-left: 12px;
        }

        .w-10 {
            width: 40px;
        }

        .h-10 {
            height: 40px;
        }

        .bg-secondary {
            background: var(--secondary) !important;
        }

        .rounded-xl {
            border-radius: 12px;
        }

        .shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .text-white {
            color: white !important;
        }

        .text-xl {
            font-size: 20px;
            font-weight: 600;
        }

        .text-sm {
            font-size: 14px;
        }

        .text-red-100 {
            color: #FEE2E2 !important;
        }

        .hidden {
            display: none;
        }

        @media (min-width: 768px) {
            .md\:flex {
                display: flex !important;
            }
        }

        .space-x-6 > * + * {
            margin-left: 24px;
        }

        .transition-colors {
            transition: color 0.3s;
        }

        .hover\:text-white:hover {
            color: white !important;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .btn-primary {
            background: var(--primary) !important;
            color: white !important;
        }

        .btn-primary:hover {
            background: var(--primary-dark) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(189, 27, 25, 0.3);
        }

        .btn-secondary {
            background: var(--secondary) !important;
            color: white !important;
        }

        .btn-secondary:hover {
            background: #b67a29 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(216, 146, 51, 0.3);
        }

        .ml-4 {
            margin-left: 16px;
        }

        .w-4 {
            width: 16px;
        }

        .h-4 {
            height: 16px;
        }

        .ml-2 {
            margin-left: 8px;
        }

        /* Hero Section */
        .bg-gradient-to-br {
            background: linear-gradient(135deg, rgba(232, 177, 74, 0.2), var(--background), rgba(216, 146, 51, 0.2)) !important;
        }

        .py-20 {
            padding-top: 80px;
            padding-bottom: 80px;
        }

        .relative {
            position: relative;
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .absolute {
            position: absolute;
        }

        .inset-0 {
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .opacity-40 {
            opacity: 0.4;
        }

        .text-center {
            text-align: center;
        }

        .inline-flex {
            display: inline-flex;
        }

        .bg-accent\/20 {
            background: rgba(232, 177, 74, 0.2) !important;
        }

        .backdrop-blur-sm {
            backdrop-filter: blur(4px);
        }

        .rounded-full {
            border-radius: 9999px;
        }

        .px-4 {
            padding-left: 16px;
            padding-right: 16px;
        }

        .py-2 {
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .border {
            border: 1px solid;
        }

        .border-accent\/30 {
            border-color: rgba(232, 177, 74, 0.3);
        }

        .font-medium {
            font-weight: 500;
        }

        .mb-6 {
            margin-bottom: 24px;
        }

        .text-5xl {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .text-foreground {
            color: var(--text-primary) !important;
        }

        .text-xl {
            font-size: 20px;
        }

        .text-foreground\/80 {
            color: rgba(43, 43, 43, 0.8) !important;
        }

        .max-w-3xl {
            max-width: 768px;
        }

        .leading-relaxed {
            line-height: 1.625;
        }

        .mb-10 {
            margin-bottom: 40px;
        }

        .flex-col {
            flex-direction: column;
        }

        @media (min-width: 640px) {
            .sm\:flex-row {
                flex-direction: row;
            }
        }

        .justify-center {
            justify-content: center;
        }

        .gap-4 {
            gap: 16px;
        }

        .px-8 {
            padding-left: 32px;
            padding-right: 32px;
        }

        .py-3 {
            padding-top: 12px;
            padding-bottom: 12px;
        }

        .rounded-xl {
            border-radius: 12px;
        }

        .w-5 {
            width: 20px;
        }

        .h-5 {
            height: 20px;
        }

        /* Stats Section */
        .bg-muted {
            background: #F5F0E8 !important;
        }

        .mb-12 {
            margin-bottom: 48px;
        }

        .text-3xl {
            font-size: 30px;
            font-weight: 700;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .text-foreground\/70 {
            color: rgba(43, 43, 43, 0.7) !important;
        }

        .grid {
            display: grid;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        @media (min-width: 768px) {
            .md\:grid-cols-4 {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .gap-8 {
            gap: 32px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .border-0 {
            border: none;
        }

        .transition-all {
            transition: all 0.3s;
        }

        .duration-300 {
            transition-duration: 0.3s;
        }

        .card-content {
            padding: 32px 24px;
        }

        .pt-8 {
            padding-top: 32px;
        }

        .pb-6 {
            padding-bottom: 24px;
        }

        .w-16 {
            width: 64px;
        }

        .h-16 {
            height: 64px;
        }

        .rounded-2xl {
            border-radius: 16px;
        }

        .text-3xl {
            font-size: 30px;
            font-weight: 700;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        /* News Section */
        .mb-16 {
            margin-bottom: 64px;
        }

        .text-4xl {
            font-size: 36px;
            font-weight: 700;
        }

        .text-lg {
            font-size: 18px;
        }

        .text-2xl {
            font-size: 24px;
            font-weight: 700;
        }

        .mb-8 {
            margin-bottom: 32px;
        }

        .hover\:scale-105:hover {
            transform: scale(1.05);
        }

        .card-header {
            padding: 24px 24px 16px;
        }

        .pb-3 {
            padding-bottom: 12px;
        }

        .flex.items-center.justify-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .bg-primary\/10 {
            background: rgba(189, 27, 25, 0.1) !important;
        }

        .text-primary {
            color: var(--primary) !important;
        }

        .text-xs {
            font-size: 12px;
        }

        .text-foreground\/60 {
            color: rgba(43, 43, 43, 0.6) !important;
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            line-height: 1.3;
        }

        .leading-tight {
            line-height: 1.25;
        }

        .leading-relaxed {
            line-height: 1.625;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .btn-link {
            background: none;
            border: none;
            color: var(--secondary) !important;
            text-decoration: none;
            padding: 0;
            height: auto;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-link:hover {
            color: var(--primary) !important;
        }

        .p-0 {
            padding: 0;
        }

        .h-auto {
            height: auto;
        }

        .space-y-6 > * + * {
            margin-top: 24px;
        }

        .max-w-5xl {
            max-width: 1024px;
        }

        .border-l-4 {
            border-left-width: 4px;
        }

        .border-l-primary {
            border-left-color: var(--primary);
        }

        .border-l-secondary {
            border-left-color: var(--secondary);
        }

        .border-l-accent {
            border-left-color: var(--accent);
        }

        .bg-primary\/20 {
            background: rgba(189, 27, 25, 0.2) !important;
        }

        .bg-secondary\/20 {
            background: rgba(216, 146, 51, 0.2) !important;
        }

        .bg-accent\/20 {
            background: rgba(232, 177, 74, 0.2) !important;
        }

        /* Footer */
        .py-16 {
            padding-top: 64px;
            padding-bottom: 64px;
        }

        .opacity-30 {
            opacity: 0.3;
        }

        .w-12 {
            width: 48px;
        }

        .h-12 {
            height: 48px;
        }

        .rounded-2xl {
            border-radius: 16px;
        }

        .shadow-xl {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .w-7 {
            width: 28px;
        }

        .h-7 {
            height: 28px;
        }

        .text-left {
            text-align: left;
        }

        .text-2xl {
            font-size: 24px;
            font-weight: 700;
        }

        .mb-8 {
            margin-bottom: 32px;
        }

        .max-w-2xl {
            max-width: 672px;
        }

        @media (min-width: 768px) {
            .md\:grid-cols-3 {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .font-semibold {
            font-weight: 600;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .text-red-100\/80 {
            color: rgba(254, 226, 226, 0.8) !important;
        }

        .mt-12 {
            margin-top: 48px;
        }

        .pt-8 {
            padding-top: 32px;
        }

        .border-t {
            border-top: 1px solid;
        }

        /* Additional responsive improvements */
        @media (max-width: 768px) {
            .text-5xl {
                font-size: 36px;
            }
            
            .text-4xl {
                font-size: 28px;
            }
            
            .text-3xl {
                font-size: 24px;
            }
            
            .text-2xl {
                font-size: 20px;
            }
            
            .px-6 {
                padding-left: 16px;
                padding-right: 16px;
            }
            
            .gap-8 {
                gap: 24px;
            }
            
            .card-content {
                padding: 24px 20px;
            }
        }

        @media (max-width: 480px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
            
            .text-5xl {
                font-size: 28px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</head>
<body>
    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header class="bg-primary border-b border-white/20 px-6 py-4 shadow-sm">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-secondary rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-white">BookHive</h1>
                        <p class="text-sm text-red-100">AI-Powered Library Assistant</p>
                    </div>
                </div>
                
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="#home" class="text-red-100 hover:text-white transition-colors font-medium">Home</a>
                    <a href="#news" class="text-red-100 hover:text-white transition-colors font-medium">News & Announcements</a>
                    <a href="login.php" class="btn btn-primary ml-4 shadow-lg">
                        Get Started
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                    </a>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="home" class="bg-gradient-to-br from-accent/20 via-background to-secondary/20 py-20 relative overflow-hidden">
            <div class="absolute inset-0 opacity-40" style="
            background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23BD1B19' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);
            "></div>
            <div class="max-w-7xl mx-auto px-6 text-center relative">
                <div class="inline-flex items-center bg-accent/20 backdrop-blur-sm rounded-full px-4 py-2 mb-6 border border-accent/30">
                    <span class="text-primary font-medium">📚 Modern Library Hub</span>
                </div>
                <h1 class="text-5xl font-bold mb-6 text-foreground">Welcome to BookHive</h1>
                <p class="text-xl text-foreground/80 mb-10 max-w-3xl mx-auto leading-relaxed">
                    Your gateway to knowledge and learning. Discover, borrow, and explore thousands of books 
                    in our modern digital library system with AI-powered assistance.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4 items-center">
                    <a href="login.php" class="btn btn-primary shadow-lg px-8 py-3 rounded-xl">
                        Start Your Journey
                        <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                    </a>
                    <a href="books_available.php" class="btn btn-secondary px-8 py-3 rounded-xl">
                        Explore Collection
                    </a>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-20 px-6 bg-gradient-to-br from-background to-muted">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-foreground mb-4">Library at a Glance</h2>
                    <p class="text-foreground/70 text-lg">Discover what makes our modern library special</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <?php
                    $stats = [
                        ['icon' => 'book-open', 'label' => 'Books Available', 'value' => '15,000+'],
                        ['icon' => 'users', 'label' => 'Active Members', 'value' => '2,500+'],
                        ['icon' => 'clock', 'label' => 'Operating Hours', 'value' => '8AM - 10PM'],
                        ['icon' => 'star', 'label' => 'Rating', 'value' => '4.8/5'],
                    ];

                    foreach ($stats as $index => $stat):
                    ?>
                        <div class="card text-center border-0 shadow-lg hover:shadow-xl transition-all duration-300 backdrop-blur-sm">
                            <div class="card-content pt-8 pb-6">
                                <div class="w-16 h-16 bg-primary rounded-2xl mx-auto mb-4 flex items-center justify-center">
                                    <i data-lucide="<?php echo $stat['icon']; ?>" class="w-8 h-8 text-white"></i>
                                </div>
                                <div class="text-3xl font-bold mb-2 text-foreground"><?php echo $stat['value']; ?></div>
                                <p class="text-foreground/70 font-medium"><?php echo $stat['label']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- News & Announcements -->
        <section id="news" class="py-20 px-6 bg-gradient-to-br from-secondary/5 to-accent/10">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold mb-6 text-foreground">News & Announcements</h2>
                    <p class="text-foreground/70 text-lg">Stay updated with the latest from your AI-powered library</p>
                </div>
                
                <!-- Library News -->
                <div class="mb-16">
                    <h3 class="text-2xl font-bold mb-8 text-foreground">Latest News</h3>
                    <div class="grid md:grid-cols-3 gap-8">
                        <?php
                        $news = [
                            ['title' => 'New Digital Collection Added', 'description' => 'Explore our latest collection of e-books and digital resources.', 'date' => 'March 15, 2024'],
                            ['title' => 'Extended Weekend Hours', 'description' => 'Library now open until 8PM on weekends for your convenience.', 'date' => 'March 10, 2024'],
                            ['title' => 'Study Room Reservations', 'description' => 'New online booking system for group study rooms is now live.', 'date' => 'March 5, 2024']
                        ];
                        foreach ($news as $item):
                        ?>
                            <div class="card border-0 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                                <div class="card-header pb-3">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="badge bg-primary/10 text-primary text-xs font-medium rounded-full">
                                            NEWS
                                        </span>
                                        <span class="text-xs text-foreground/60"><?php echo $item['date']; ?></span>
                                    </div>
                                    <h4 class="card-title text-xl text-foreground leading-tight"><?php echo $item['title']; ?></h4>
                                </div>
                                <div class="card-content">
                                    <p class="text-foreground/70 leading-relaxed mb-4"><?php echo $item['description']; ?></p>
                                    <a href="#" class="btn btn-link p-0 h-auto text-secondary hover:text-primary font-medium">
                                        Read more →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Important Announcements -->
                <div>
                    <h3 class="text-2xl font-bold mb-8 text-foreground">Important Announcements</h3>
                    <div class="space-y-6 max-w-5xl mx-auto">
                        <?php
                        $announcements = [
                            ['title' => 'Spring Break Hours', 'description' => 'Modified operating hours during spring break: 9AM - 6PM', 'type' => 'important'],
                            ['title' => 'AI Assistant Available', 'description' => 'Try our new AI chatbot for instant book recommendations and assistance.', 'type' => 'feature'],
                            ['title' => 'Overdue Book Amnesty', 'description' => 'Return overdue books by March 31st with no late fees.', 'type' => 'notice']
                        ];
                        foreach ($announcements as $announcement):
                            $type_class = '';
                            $type_badge_class = '';
                            if ($announcement['type'] === 'important') {
                                $type_class = 'bg-gradient-to-r from-primary/10 to-primary/5 border-l-4 border-l-primary';
                                $type_badge_class = 'bg-primary/20 text-primary';
                            } elseif ($announcement['type'] === 'feature') {
                                $type_class = 'bg-gradient-to-r from-secondary/10 to-secondary/5 border-l-4 border-l-secondary';
                                $type_badge_class = 'bg-secondary/20 text-secondary';
                            } elseif ($announcement['type'] === 'notice') {
                                $type_class = 'bg-gradient-to-r from-accent/10 to-accent/5 border-l-4 border-l-accent';
                                $type_badge_class = 'bg-accent/20 text-accent';
                            }
                        ?>
                            <div class="card border-0 shadow-lg transition-all duration-300 hover:shadow-xl <?php echo $type_class; ?>">
                                <div class="card-header pb-4">
                                    <div class="flex items-start justify-between">
                                        <h4 class="card-title text-xl text-foreground"><?php echo $announcement['title']; ?></h4>
                                        <span class="badge px-3 py-1 rounded-full text-sm font-medium <?php echo $type_badge_class; ?>">
                                            <?php echo ucfirst($announcement['type']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <p class="text-foreground/70 leading-relaxed"><?php echo $announcement['description']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-primary py-16 px-6 relative overflow-hidden">
            <div class="absolute inset-0 opacity-30" style="
            background-image: url(&quot;data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);
            "></div>
            <div class="max-w-7xl mx-auto text-center relative">
                <div class="flex items-center justify-center space-x-3 mb-6">
                    <div class="w-12 h-12 bg-secondary rounded-2xl flex items-center justify-center shadow-xl">
                        <i data-lucide="book-open" class="w-7 h-7 text-white"></i>
                    </div>
                    <div class="text-left">
                        <span class="text-2xl font-bold text-white">BookHive</span>
                        <p class="text-red-100 text-sm">AI-Powered Library Assistant</p>
                    </div>
                </div>
                <p class="text-red-100 mb-8 text-lg max-w-2xl mx-auto leading-relaxed">
                    Empowering education through accessible knowledge and modern technology, 
                    where traditional learning meets cutting-edge digital innovation.
                </p>
                <div class="grid md:grid-cols-3 gap-8 text-red-100 mb-12">
                    <div>
                        <h4 class="text-white font-semibold mb-3">📍 Location</h4>
                        <p class="text-red-100/80 text-sm">Doña Matilde Memorial Elementary School<br/>Matingain 1, Lemery, Batangas</p>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">🕒 Hours</h4>
                        <p class="text-red-100/80 text-sm">Mon-Fri: 8:00 AM - 10:00 PM<br/>Weekends: 9:00 AM - 8:00 PM</p>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">📞 Contact</h4>
                        <p class="text-red-100/80 text-sm">library@bookhive.edu<br/>+1 (555) 123-4567</p>
                    </div>
                </div>
                <div class="mt-12 pt-8 border-t border-white/20">
                    <p class="text-red-100/80 text-sm">
                        © 2024 BookHive Library System. Where knowledge meets innovation.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>