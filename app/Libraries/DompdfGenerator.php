<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class DompdfGenerator
{
    protected $dompdf;

    public function __construct()
    {
        // Konfigurasi Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Untuk mengakses resource seperti gambar dari URL
        $this->dompdf = new Dompdf($options);
    }

    public function generate($html, $filename = 'document.pdf', $stream = true, $paper = 'A4', $orientation = 'landscape', $attachment = 0)
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paper, $orientation); // Ukuran kertas dan orientasi
        $this->dompdf->render();

        if ($stream) {
            // Stream ke browser
            $this->dompdf->stream($filename, ['Attachment' => $attachment]); // Attachment 0 = tampil di browser, 1 = download
        } else {
            // Simpan ke file
            return $this->dompdf->output();
        }
    }
}
