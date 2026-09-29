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
 * Mendukung URL relatif, base_url(), domain (citraknitt.com), IP (103.102.153.192),
 * localhost, serta mempertahankan query string yang sudah ada sebelumnya.
 *
 * @param string $url
 * @return string
 */
function auto_version($url)
{
    if (empty($url)) {
        return $url;
    }

    $trimmed = trim($url);
    $parsed = parse_url($trimmed);
    $path = $parsed['path'] ?? '';

    if (empty($path)) {
        return $url;
    }

    // Filter external domains (CDN, Google Fonts, dll.)
    if (isset($parsed['host'])) {
        $host = strtolower($parsed['host']);
        $localHosts = ['localhost', '127.0.0.1', 'citraknitt.com', 'www.citraknitt.com', '103.102.153.192'];

        if (function_exists('base_url')) {
            $baseHost = parse_url(base_url(), PHP_URL_HOST);
            if (!empty($baseHost)) {
                $localHosts[] = strtolower($baseHost);
            }
        }
        if (!empty($_SERVER['HTTP_HOST'])) {
            $h = explode(':', $_SERVER['HTTP_HOST'])[0];
            $localHosts[] = strtolower($h);
        }
        if (!empty($_SERVER['SERVER_NAME'])) {
            $localHosts[] = strtolower($_SERVER['SERVER_NAME']);
        }
        $localHosts = array_unique(array_filter($localHosts));

        if (!in_array($host, $localHosts, true)) {
            return $url;
        }
    }

    // Ambil path relatif terhadap folder public (FCPATH)
    $cleanPath = ltrim(str_replace('index.php', '', $path), '/');
    $fcPath = defined('FCPATH') ? FCPATH : '';
    $realFile = $fcPath . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);

    $ver = (file_exists($realFile) && !is_dir($realFile)) ? filemtime($realFile) : time();

    // Pertahankan parameter query yang sudah ada dan set/timpa parameter 'v'
    $query = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $query);
    }
    $query['v'] = $ver;

    // Rekonstruksi URL
    $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
    $host = $parsed['host'] ?? '';
    $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
    $newQuery = http_build_query($query);
    $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

    return $scheme . $host . $port . $path . '?' . $newQuery . $fragment;
}