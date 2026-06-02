<?php

namespace Database\Seeders;

use App\Models\ShowroomItem;
use Illuminate\Database\Seeder;

class ShowroomSeeder extends Seeder
{
    public function run(): void
    {
        ShowroomItem::create([
            'title'       => 'Sample Web App',
            'description' => 'A placeholder demo application. Replace this embed URL with your actual app.',
            'embed_url'   => 'https://example.com',
            'tech_tags'   => 'Laravel,Alpine.js,Tailwind',
            'is_active'   => true,
            'sort_order'  => 1,
        ]);

        ShowroomItem::create([
            'title'       => 'Dashboard Tool',
            'description' => 'An analytics and reporting dashboard.',
            'embed_url'   => 'https://example.com',
            'tech_tags'   => 'Vue.js,Chart.js',
            'is_active'   => true,
            'sort_order'  => 2,
        ]);
    }
}
