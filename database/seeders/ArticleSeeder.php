<?php

namespace Database\Seeders;
// ========================================
// 3. SEEDER: database/seeders/ArticleSeeder.php (Optional untuk testing)
// Jalankan: php artisan make:seeder ArticleSeeder
// ========================================

use Illuminate\Database\Seeder;
use App\Models\Article;
use Carbon\Carbon;

class ArticleSeeder extends Seeder
{
    public function run()
    {
        $articles = [
            [
                'title' => 'Panduan Lengkap React untuk Pemula',
                'content' => 'React adalah library JavaScript yang dikembangkan oleh Facebook untuk membangun user interface yang interaktif dan dinamis...',
                'excerpt' => 'Pelajari konsep-konsep dasar React JavaScript library untuk membangun user interface yang interaktif dan modern.',
                'author' => 'Ahmad Fadhil',
                'category' => 'Programming',
                'tags' => 'React,JavaScript,Frontend,Tutorial',
                'status' => 'published',
                'featured_image' => 'https://via.placeholder.com/800x400/3b82f6/ffffff?text=React+Tutorial',
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Menguasai Laravel dalam 30 Hari',
                'content' => 'Laravel adalah framework PHP yang paling populer untuk pengembangan web modern...',
                'excerpt' => 'Panduan step-by-step untuk menguasai Laravel framework dalam waktu 30 hari.',
                'author' => 'Sari Dewi',
                'category' => 'Programming',
                'tags' => 'Laravel,PHP,Backend,Framework',
                'status' => 'published',
                'featured_image' => 'https://via.placeholder.com/800x400/f56565/ffffff?text=Laravel+Guide',
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Tips UI/UX Design untuk Developer',
                'content' => 'Sebagai developer, memahami prinsip UI/UX design sangat penting...',
                'excerpt' => 'Tips praktis UI/UX design yang bisa diterapkan oleh developer untuk membuat aplikasi yang user-friendly.',
                'author' => 'Budi Santoso',
                'category' => 'Design',
                'tags' => 'UI,UX,Design,Frontend',
                'status' => 'draft',
                'featured_image' => 'https://via.placeholder.com/800x400/10b981/ffffff?text=UI+UX+Tips',
                'published_at' => null,
            ]
        ];

        foreach ($articles as $article) {
            Article::create($article);
        }
    }
}