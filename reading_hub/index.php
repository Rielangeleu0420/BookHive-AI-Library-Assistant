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
    <link rel="stylesheet" href="style.css">
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
                    <a href="#home" class="text-red-100 hover:text-white transition-colors">Home</a>
                    <a href="#news" class="text-red-100 hover:text-white transition-colors">News & Announcements</a>
                    <a href="login.php" class="btn btn-primary ml-4 shadow-lg">
                        Login / Register
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
                    <p class="text-foreground/70">Discover what makes our modern library special</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <?php
                    $stats = [
                        ['icon' => 'book-open', 'label' => 'Books Available', 'value' => '15,000+'],
                        ['icon' => 'users', 'label' => 'Active Members', 'value' => '2,500+'],
                        ['icon' => 'clock', 'label' => 'Operating Hours', 'value' => '8AM - 10PM'],
                        ['icon' => 'star', 'label' => 'Rating', 'value' => '4.8/5'],
                    ];
                    $colors = ['var(--primary)', 'var(--secondary)', 'var(--accent)', 'var(--primary)'];
                    $bgColors = ['bg-primary/10', 'bg-secondary/10', 'bg-accent/10', 'bg-primary/10'];

                    foreach ($stats as $index => $stat):
                    ?>
                        <div class="card text-center border-0 shadow-lg hover:shadow-xl transition-all duration-300 <?php echo $bgColors[$index % 4]; ?> backdrop-blur-sm">
                            <div class="card-content pt-8 pb-6">
                                <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background-color: <?php echo $colors[$index % 4]; ?>">
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
                                    <div class="card-title text-xl text-foreground leading-tight"><?php echo $item['title']; ?></div>
                                </div>
                                <div class="card-content">
                                    <p class="text-foreground/70 leading-relaxed"><?php echo $item['description']; ?></p>
                                    <a href="#" class="btn btn-link p-0 h-auto mt-3 text-secondary hover:text-primary">
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
                                        <div class="card-title text-xl text-foreground"><?php echo $announcement['title']; ?></div>
                                        <span class="badge px-3 py-1 rounded-full text-sm font-medium <?php echo $type_badge_class; ?>">
                                            <?php echo $announcement['type']; ?>
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
                <div class="grid md:grid-cols-3 gap-8 text-red-100">
                    <div>
                        <h4 class="text-white font-semibold mb-3">📍 Location</h4>
                        <p class="text-sm">Doña Matilde Memorial Elementary School<br/>Matingain 1, Lemery, Batangas</p>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">🕒 Hours</h4>
                        <p class="text-sm">Mon-Fri: 8:00 AM - 10:00 PM<br/>Weekends: 9:00 AM - 8:00 PM</p>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">📞 Contact</h4>
                        <p class="text-sm">library@bookhive.edu<br/>+1 (555) 123-4567</p>
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