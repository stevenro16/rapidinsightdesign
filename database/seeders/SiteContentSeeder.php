<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'hero_headline'    => 'Smart Design, Optimized Workflows Built for Efficiency.',
            'hero_subheadline' => 'We craft high-performance web applications and custom software solutions that scale with your business — fast, fluid, and beautifully engineered.',
            'about_text'       => 'RapidInsight Designs specializes in building custom web applications and software tools tailored to real business needs. We focus on performance, clean design, and seamless user experience.',
            'contact_intro'    => 'Have a project in mind? We\'d love to hear about it. Fill out the form and we\'ll get back to you within one business day.',
        ];

        foreach ($defaults as $key => $value) {
            SiteContent::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
