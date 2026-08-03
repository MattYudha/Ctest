<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LetterTemplate;

class LetterTemplateCutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content = <<<'HTML'
<p>Kepada Yth.</p>
<h4 style="color: rgb(206, 212, 218);">
<p style="font-size: 16px; font-weight: 400;">HRD PT Aratech Nusantara Indonesia</p>
<p style="font-size: 16px; font-weight: 400;">di Tempat</p>
<p style="font-size: 16px; font-weight: 400;"><br></p>
<p style="font-size: 16px; font-weight: 400;">Dengan hormat,</p>
<p style="font-size: 16px; font-weight: 400;"><br></p>
<p style="font-size: 16px; font-weight: 400;">Yang bertanda tangan di bawah ini:</p>
<p style="font-size: 16px; font-weight: 400;"><span style="font-weight: bolder;">Nama:</span> [nama_karyawan_aktif]</p>
<p style="font-size: 16px; font-weight: 400;"><span style="font-weight: bolder;">Jabatan:</span> [jabatan_karyawan_aktif]</p>
<p style="font-size: 16px; font-weight: 400;"><span style="font-weight: bolder;">Departemen:</span> [departemen_karyawan_aktif]</p>
<p style="font-size: 16px; font-weight: 400;"><br></p>
<p style="font-size: 16px; font-weight: 400;">Dengan ini mengajukan permohonan cuti pada tanggal [tanggal_cuti_multiple]</p>
<p style="font-size: 16px; font-weight: 400;"><span style="font-weight: bolder;">Alasan pengajuan: </span>[alasan_pengajuan]</p>
<p style="font-size: 16px; font-weight: 400;"><span style="font-weight: bolder;">Pekerjaan diserahkan kepada: </span>[diserahkan_kepada]</p>
<p style="font-size: 16px; font-weight: 400;"><br></p>
<p style="font-size: 16px; font-weight: 400;">Demikian surat permohonan ini saya buat. Atas perhatian dan persetujuannya, saya ucapkan terima kasih.</p>
<p style="font-size: 16px; font-weight: 400;"><br></p>
<p style="font-size: 16px; font-weight: 400;">Hormat saya,</p>
<p style="font-size: 16px; font-weight: 400;">[nama_karyawan_aktif]</p>
</h4>
HTML;

        LetterTemplate::updateOrCreate(
            ['name' => 'Surat Cuti'],
            [
                'type' => 'official',
                'description' => 'Template untuk pengajuan cuti dengan beberapa tanggal',
                'content' => $content,
                'is_active' => true,
            ]
        );
    }
}
