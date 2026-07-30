<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LetterTag;

class LetterTagMultipleDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'tag_name' => 'tanggal_lupa_absen_multiple',
                'input_type' => 'multiple_date',
                'default_value' => null,
            ],
            [
                'tag_name' => 'tanggal_telat_absen_multiple',
                'input_type' => 'multiple_date',
                'default_value' => null,
            ],
            [
                'tag_name' => 'tanggal_cuti_multiple',
                'input_type' => 'multiple_date',
                'default_value' => null,
            ],
            [
                'tag_name' => 'alasan_cuti',
                'input_type' => 'long_text',
                'default_value' => null,
            ],
            [
                'tag_name' => 'diserahkan_kepada',
                'input_type' => 'text',
                'default_value' => null,
            ],
        ];

        foreach ($tags as $tag) {
            LetterTag::updateOrCreate(
                ['tag_name' => $tag['tag_name']],
                $tag
            );
        }
    }
}
