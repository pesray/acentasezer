<?php
/**
 * Anasayfa section'larını veritabanına ekle
 */

$host = '5.2.85.141';
$dbname = 'ahmetkes_agency';
$username = 'ahmetkes_sezer';
$password = 'Szr4569*-';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Anasayfa ID'sini al
    $stmt = $pdo->query("SELECT id FROM pages WHERE is_homepage = 1 LIMIT 1");
    $homepage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$homepage) {
        // Anasayfa yoksa oluştur
        $pdo->exec("INSERT INTO pages (title, slug, status, is_homepage, sort_order) VALUES ('Ana Sayfa', 'ana-sayfa', 'published', 1, 1)");
        $homepageId = $pdo->lastInsertId();
        echo "Anasayfa oluşturuldu (ID: $homepageId)\n";
    } else {
        $homepageId = $homepage['id'];
        echo "Mevcut anasayfa kullanılıyor (ID: $homepageId)\n";
    }
    
    // Mevcut section'ları temizle
    $pdo->prepare("DELETE FROM sections WHERE page_id = ?")->execute([$homepageId]);
    echo "Eski section'lar temizlendi\n";
    
    // Section'ları ekle
    $sections = [
        [
            'section_key' => 'hero',
            'section_type' => 'hero',
            'title' => 'Discover Your Perfect Journey',
            'subtitle' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'content' => '',
            'settings' => json_encode([
                'video_url' => 'assets/img/travel/video-2.mp4',
                'button1_text' => 'Start Exploring',
                'button1_url' => '#',
                'button2_text' => 'Browse Tours',
                'button2_url' => '#',
                'show_booking_form' => true,
                'form_title' => 'Plan Your Adventure'
            ]),
            'background_video' => 'assets/img/travel/video-2.mp4',
            'sort_order' => 1,
            'is_active' => 1
        ],
        [
            'section_key' => 'why_us',
            'section_type' => 'why_us',
            'title' => 'Explore the World with Confidence',
            'subtitle' => 'Why Choose Us for Your Next Adventure',
            'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>',
            'settings' => json_encode([
                'image' => 'assets/img/travel/showcase-8.webp',
                'stats' => [
                    ['number' => 1200, 'label' => 'Happy Travelers'],
                    ['number' => 85, 'label' => 'Countries Covered'],
                    ['number' => 15, 'label' => 'Years Experience']
                ],
                'experience_badge' => '15+',
                'experience_text' => 'Years of Excellence'
            ]),
            'background_image' => 'assets/img/travel/showcase-8.webp',
            'sort_order' => 2,
            'is_active' => 1
        ],
        [
            'section_key' => 'featured_destinations',
            'section_type' => 'destinations',
            'title' => 'Featured Destinations',
            'subtitle' => 'Check Our Featured Destinations',
            'content' => '',
            'settings' => json_encode([
                'limit' => 4,
                'show_featured_only' => true
            ]),
            'sort_order' => 3,
            'is_active' => 1
        ],
        [
            'section_key' => 'featured_tours',
            'section_type' => 'tours',
            'title' => 'Featured Tours',
            'subtitle' => 'Check Our Featured Tours',
            'content' => '',
            'settings' => json_encode([
                'limit' => 6,
                'show_featured_only' => true,
                'show_view_all' => true,
                'view_all_url' => '/turlar'
            ]),
            'sort_order' => 4,
            'is_active' => 1
        ],
        [
            'section_key' => 'testimonials',
            'section_type' => 'testimonials',
            'title' => 'Testimonials',
            'subtitle' => 'What Our Customers Are Saying',
            'content' => '',
            'settings' => json_encode([
                'limit' => 5,
                'autoplay' => true,
                'autoplay_delay' => 5000
            ]),
            'sort_order' => 5,
            'is_active' => 1
        ],
        [
            'section_key' => 'cta',
            'section_type' => 'cta',
            'title' => 'Discover Your Next Adventure',
            'subtitle' => 'Limited Time Offer',
            'content' => '<p>Unlock incredible destinations with our specially curated travel packages. From exotic beaches to mountain peaks, your perfect getaway awaits.</p>',
            'settings' => json_encode([
                'image' => 'assets/img/travel/showcase-3.webp',
                'button1_text' => 'Explore Now',
                'button1_url' => '/destinasyonlar',
                'button1_icon' => 'bi-compass',
                'button2_text' => 'View Deals',
                'button2_url' => '/turlar',
                'button2_icon' => 'bi-percent',
                'phone' => '+1 (555) 123-456',
                'stats' => [
                    ['number' => '500+', 'label' => 'Destinations'],
                    ['number' => '10K+', 'label' => 'Happy Travelers']
                ],
                'show_newsletter' => true,
                'newsletter_title' => 'Stay in the Loop',
                'newsletter_text' => 'Get exclusive travel deals and destination guides delivered to your inbox',
                'benefits' => [
                    ['icon' => 'bi-geo-alt', 'title' => 'Handpicked Destinations', 'text' => 'Every location is carefully selected by our travel experts'],
                    ['icon' => 'bi-award', 'title' => 'Award-Winning Service', 'text' => 'Recognized for excellence with 5-star ratings'],
                    ['icon' => 'bi-heart', 'title' => 'Personalized Care', 'text' => 'Tailored itineraries designed around your preferences']
                ]
            ]),
            'background_image' => 'assets/img/travel/showcase-3.webp',
            'sort_order' => 6,
            'is_active' => 1
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO sections (page_id, section_key, section_type, title, subtitle, content, settings, background_image, background_video, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($sections as $section) {
        $stmt->execute([
            $homepageId,
            $section['section_key'],
            $section['section_type'],
            $section['title'],
            $section['subtitle'],
            $section['content'],
            $section['settings'],
            $section['background_image'] ?? null,
            $section['background_video'] ?? null,
            $section['sort_order'],
            $section['is_active']
        ]);
        echo "Section eklendi: {$section['section_key']}\n";
    }
    
    // Features ekle
    $pdo->exec("DELETE FROM features");
    $features = [
        ['Local Experts', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium totam.', 'bi-people-fill', 1],
        ['Safe & Secure', 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum.', 'bi-shield-check', 2],
        ['Best Prices', 'Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet consectetur adipisci velit.', 'bi-cash', 3],
        ['24/7 Support', 'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam nisi.', 'bi-headset', 4],
        ['Global Destinations', 'Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae.', 'bi-geo-alt-fill', 5],
        ['Premium Experience', 'Excepteur sint occaecat cupidatat non proident sunt in culpa qui officia deserunt mollit anim.', 'bi-star-fill', 6]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO features (title, description, icon, sort_order, is_active) VALUES (?, ?, ?, ?, 1)");
    foreach ($features as $f) {
        $stmt->execute($f);
    }
    echo "\nFeatures eklendi (6 adet)\n";
    
    // Testimonials ekle
    $pdo->exec("DELETE FROM testimonials");
    $testimonials = [
        ['Saul Goodman', 'Ceo & Founder', 'assets/img/person/person-m-9.webp', 'Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et.', 5],
        ['Sara Wilsson', 'Designer', 'assets/img/person/person-f-5.webp', 'Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid malis quorum velit fore eram velit sunt aliqua noster fugiat.', 5],
        ['Jena Karlis', 'Store Owner', 'assets/img/person/person-f-12.webp', 'Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute.', 5],
        ['Matt Brandon', 'Freelancer', 'assets/img/person/person-m-12.webp', 'Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat dolor enim duis veniam ipsum anim magna sunt elit fore.', 4],
        ['John Larson', 'Entrepreneur', 'assets/img/person/person-m-13.webp', 'Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam sunt culpa nulla illum cillum fugiat legam esse.', 5]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO testimonials (customer_name, customer_title, customer_image, content, rating, is_featured, is_approved, sort_order) VALUES (?, ?, ?, ?, ?, 1, 1, ?)");
    $order = 1;
    foreach ($testimonials as $t) {
        $stmt->execute([$t[0], $t[1], $t[2], $t[3], $t[4], $order++]);
    }
    echo "Testimonials eklendi (5 adet)\n";
    
    // Örnek destinasyonlar ekle
    $pdo->exec("DELETE FROM destinations");
    $destinations = [
        ['Tropical Paradise', 'tropical-paradise', 'Pristine beaches, crystal-clear waters, and luxury overwater villas await in this tropical paradise destination.', 'assets/img/travel/destination-3.webp', 'Maldives', 'Maldives', 'Asia', 2150, 'Popular Choice', 4.9, 412, 22, 1],
        ['Mountain Adventure', 'mountain-adventure', 'Breathtaking Himalayan peaks and ancient Buddhist temples create an unforgettable spiritual journey.', 'assets/img/travel/destination-7.webp', 'Nepal', 'Nepal', 'Asia', 1420, 'Best Value', 4.8, 180, 16, 1],
        ['Cultural Heritage', 'cultural-heritage', 'Discover ancient civilizations, colorful markets, and archaeological wonders in the heart of South America.', 'assets/img/travel/destination-11.webp', 'Peru', 'Peru', 'South America', 980, null, 4.7, 95, 9, 1],
        ['Safari Experience', 'safari-experience', 'Witness the Big Five and experience the great migration in Africa\'s most spectacular wildlife reserves.', 'assets/img/travel/destination-16.webp', 'Kenya', 'Kenya', 'Africa', 2750, 'Limited Spots', 4.9, 220, 11, 1]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO destinations (title, slug, description, featured_image, location, country, continent, starting_price, badge, rating, review_count, tour_count, is_featured, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?)");
    $order = 1;
    foreach ($destinations as $d) {
        $stmt->execute([$d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8], $d[9], $d[10], $d[11], $d[12], $order++]);
    }
    echo "Destinations eklendi (4 adet)\n";
    
    // Örnek turlar ekle
    $pdo->exec("DELETE FROM tours");
    $tours = [
        ['Serene Beach Retreat', 'serene-beach-retreat', 'Mauris ipsum neque, cursus ac ipsum at, iaculis facilisis ligula. Suspendisse non sapien vel enim cursus semper.', 'assets/img/travel/tour-1.webp', 8, 2150, 'Top Rated', 4.8, 95, 6, 1],
        ['Arctic Wilderness Expedition', 'arctic-wilderness-expedition', 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae.', 'assets/img/travel/tour-2.webp', 10, 5700, 'Only 3 Spots!', 4.6, 55, 8, 1],
        ['Sahara Desert Discovery', 'sahara-desert-discovery', 'Pellentesque euismod tincidunt turpis ac tristique. Phasellus vitae lacus in enim mollis facilisis.', 'assets/img/travel/tour-4.webp', 5, 1400, 'Newly Added', 4.9, 72, 10, 1],
        ['Mediterranean Coastal Cruise', 'mediterranean-coastal-cruise', 'Nullam lacinia justo eget ex sodales, vel finibus orci aliquet. Donec auctor, elit ut molestie gravida.', 'assets/img/travel/tour-5.webp', 9, 1980, 'Popular Choice', 4.7, 110, 15, 1],
        ['Amazon Rainforest Trek', 'amazon-rainforest-trek', 'Quisque dictum felis eu tortor mollis, quis tincidunt arcu pharetra. A pellentesque sit amet.', 'assets/img/travel/tour-6.webp', 12, 2650, 'Eco-Friendly', 4.5, 88, 10, 1],
        ['Patagonian Peaks & Glaciers', 'patagonian-peaks-glaciers', 'Vivamus eget semper neque. Ut porttitor mi at odio egestas, non vestibulum est malesuada.', 'assets/img/travel/tour-8.webp', 14, 3950, 'Adventure Seekers', 4.9, 60, 10, 1]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO tours (title, slug, description, featured_image, duration_days, price, currency, badge, rating, review_count, group_size_max, is_featured, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, 'USD', ?, ?, ?, ?, ?, 'published', ?)");
    $order = 1;
    foreach ($tours as $t) {
        $stmt->execute([$t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $t[6], $t[7], $t[8], $t[9], $t[10], $order++]);
    }
    echo "Tours eklendi (6 adet)\n";
    
    echo "\n=== Anasayfa section'ları ve örnek veriler eklendi! ===\n";
    
} catch (PDOException $e) {
    echo "[HATA] " . $e->getMessage() . "\n";
}
