<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter NoCacheFilter
 *
 * Mencegah browser pengguna dan proxy menyimpan cache halaman dinamis atau respon JSON.
 * Otomatis menyematkan parameter versi berbasis filemtime (?v=timestamp) ke seluruh
 * tag <script src="..."> dan <link ... href="..."> lokal agar setiap ada pembaruan kode,
 * browser pengguna otomatis mengunduh asset terbaru tanpa perlu Clear Cache / Ctrl+F5.
 */
class NoCacheFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // No action needed before controller execution
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 1. Terapkan HTTP Headers Anti-Cache untuk seluruh respon dinamis (kecuali file unduhan/attachment)
        $disposition = $response->getHeaderLine('Content-Disposition');
        if (empty($disposition) || stripos($disposition, 'attachment') === false) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
        }

        // 2. Jika respon berupa halaman HTML, otomatis inject parameter versi (?v=filemtime)
        // pada semua script dan stylesheet lokal yang belum memiliki versi dinamis.
        $contentType = $response->getHeaderLine('Content-Type');
        if (empty($contentType) || stripos($contentType, 'text/html') !== false) {
            $body = $response->getBody();
            if (!empty($body) && is_string($body)) {
                if (!function_exists('auto_version')) {
                    helper('path');
                }

                // Injeksi auto_version pada tag <script src="..."> lokal
                $body = preg_replace_callback(
                    '/<script\s+([^>]*?)src=["\']([^"\']+\.js(?:\?[^"\']*)?)["\']([^>]*)>/i',
                    function ($matches) {
                        $preAttr  = $matches[1];
                        $src      = $matches[2];
                        $postAttr = $matches[3];

                        $newSrc = function_exists('auto_version') ? auto_version($src) : $src;
                        return "<script {$preAttr}src=\"{$newSrc}\"{$postAttr}>";
                    },
                    $body
                );

                // Injeksi auto_version pada tag <link ... href="...css..."> lokal
                $body = preg_replace_callback(
                    '/<link\s+([^>]*?)href=["\']([^"\']+\.css(?:\?[^"\']*)?)["\']([^>]*)>/i',
                    function ($matches) {
                        $preAttr  = $matches[1];
                        $href     = $matches[2];
                        $postAttr = $matches[3];

                        $newHref = function_exists('auto_version') ? auto_version($href) : $href;
                        return "<link {$preAttr}href=\"{$newHref}\"{$postAttr}>";
                    },
                    $body
                );

                $response->setBody($body);
            }
        }

        return $response;
    }
}
