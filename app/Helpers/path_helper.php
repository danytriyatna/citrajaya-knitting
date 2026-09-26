<?php

function assets_url($dir = null){

    if( !empty( $dir ) ){
        return base_url() . '/assets' . $dir;
    }else{
        return base_url() . '/assets';
    }
    
}

/**
 * Otomatis tambahkan parameter versi berbasis filemtime (?v=timestamp_file)
 * ke URL aset (JS/CSS) lokal, sehingga setiap kali file di-update di server,
 * browser pengguna otomatis mengunduh versi terbaru tanpa perlu CTRL+F5 / CTRL+Shift+R.
 *
 * @param string $url
 * @return string
 */
function auto_version($url)
{
    if (empty($url)) {
        return $url;
    }

    // Abaikan external URL / CDN
    if (preg_match('/^(https?:)?\/\//i', $url)) {
        $baseUrl = function_exists('base_url') ? base_url() : '';
        if (empty($baseUrl) || !str_contains($url, $baseUrl)) {
            return $url;
        }
    }

    $parts = explode('?', $url, 2);
    $path = $parts[0];

    // Dapatkan path relatif terhadap public folder (FCPATH)
    $cleanPath = $path;
    if (function_exists('base_url')) {
        $bUrl = base_url();
        if (!empty($bUrl) && str_starts_with($cleanPath, $bUrl)) {
            $cleanPath = substr($cleanPath, strlen($bUrl));
        }
    }

    $cleanPath = ltrim(str_replace('index.php/', '', $cleanPath), '/');

    $fcPath = defined('FCPATH') ? FCPATH : '';
    $realFile = $fcPath . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);

    $ver = (!empty($realFile) && file_exists($realFile)) ? filemtime($realFile) : time();

    return $path . '?v=' . $ver;
}