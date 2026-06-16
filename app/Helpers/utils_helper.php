<?php

if (!function_exists('sess_prefix')) {
    function sess_prefix()
    {
        $CI = &get_instance();
        return $CI->config->item('sess_prefix', 'ion_auth');
    }
}

if (!function_exists('format_angka')) {
    function format_angka($angka, $dec = 0)
    {
       if(!empty($angka)){
            $hasil = number_format($angka, $dec, ".", ",");
            return $hasil;
       }else{
        return 0;
       }
    }
}

if (!function_exists('remove_format_angka')) {
    function remove_format_angka($angka)
    {
        $hasil = str_replace(",", "", $angka);
        //$hasil = str_replace(".","",$angka);
        return $hasil;
    }
}

if (!function_exists('get_result_notif')) {
    function get_result_notif()
    {
        $CI = &get_instance();
        $CI->load->model("reference/mcommon", "mcom");
        //$data = $CI->mcom->get_notif_list(); 
        $data = array();
        return $data;
    }
}

if (!function_exists('get_count_notif')) {
    function get_count_notif()
    {
        $CI = &get_instance();
        $CI->load->model("reference/mcommon", "mcom");
        //$cnt = $CI->mcom->get_notif_count();
        $cnt = 0;
        return $cnt;
    }
}

if (!function_exists('str_crslug')) {
    function str_crslug($string)
    {
        $replacement = '-'; // '_';
        $CI = &get_instance();
        $CI->load->helper(array('url', 'text', 'string'));
        $string = strtolower(url_title(convert_accented_characters($string), $replacement));
        return reduce_multiples($string, $replacement, TRUE);
    }
}

//untuk mengetahui bulan bulan
if (!function_exists('bulan')) {
    function bulan($bln)
    {
        switch ($bln) {
            case 1:
                return "Januari";
                break;
            case 2:
                return "Februari";
                break;
            case 3:
                return "Maret";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "Mei";
                break;
            case 6:
                return "Juni";
                break;
            case 7:
                return "Juli";
                break;
            case 8:
                return "Agustus";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "Oktober";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "Desember";
                break;
        }
    }
}

//untuk mengetahui bulans bulans
if (!function_exists('bulans')) {
    function bulans($bln)
    {
        switch ($bln) {
            case 1:
                return "Jan";
                break;
            case 2:
                return "Feb";
                break;
            case 3:
                return "Mar";
                break;
            case 4:
                return "Apr";
                break;
            case 5:
                return "Mei";
                break;
            case 6:
                return "Jun";
                break;
            case 7:
                return "Jul";
                break;
            case 8:
                return "Agust";
                break;
            case 9:
                return "Sep";
                break;
            case 10:
                return "Okt";
                break;
            case 11:
                return "Nov";
                break;
            case 12:
                return "Des";
                break;
        }
    }
}

if (!function_exists('fdate_by_fromat')) {

    function fdate_by_format($dt, $input_format, $output_format)
    {
        $return_dt = '';
        if (trim($dt) != '') {
            $date = DateTime::createFromFormat($input_format, $dt);
            $return_dt = date_format($date, $output_format);
        }
        return $return_dt;
    }
}

if (!function_exists('fdate_ind_to_eng')) {
    // Konversi dd-mm-yyyy -> yyyy-mm-dd
    function fdate_ind_to_eng($dt)
    {
        $return_dt = '';
        if (trim($dt) != '' && $dt != '00-00-0000') {
            $return_dt = date("Y-m-d", strtotime($dt));
        }
        return $return_dt;
    }
}

if (!function_exists('fdate_eng_to_ind')) {
    // Konversi yyyy-mm-dd -> dd-mm-yyyy
    function fdate_eng_to_ind($dt, $use_long_format = false)
    {
        $return_dt = '';
        if (trim($dt) != '' && $dt != '0000-00-00') {
            $date = DateTime::createFromFormat('Y-m-d', $dt);

            if ($date) {
                $format = 'd-m-Y';
                if ($use_long_format) $format = '%d %B %Y';

                // $return_dt = strftime($format, $date->getTimestamp());
                $timestamp = $date->getTimestamp();
                $dateTime = (new DateTime())->setTimestamp($timestamp);
                $return_dt = $dateTime->format($format);
            }
        }
        return $return_dt;
    }
}

if (!function_exists('fdate_eng_to_ind_slas')) {
    // Konversi yyyy-mm-dd -> dd-mm-yyyy
    function fdate_eng_to_ind_slas($dt, $use_long_format = false)
    {
        $return_dt = '';
        if (trim($dt) != '' && $dt != '0000-00-00') {
            $date = DateTime::createFromFormat('Y-m-d', $dt);

            if ($date) {
                $format = 'd/m/Y';
                if ($use_long_format) $format = '%d %B %Y';

                // $return_dt = strftime($format, $date->getTimestamp());
                $timestamp = $date->getTimestamp();
                $dateTime = (new DateTime())->setTimestamp($timestamp);
                $return_dt = $dateTime->format($format);
            }
        }
        return $return_dt;
    }
}

if (!function_exists('fdate_eng_to_ind_2')) {
    // Konversi yyyy-mm-dd -> dd-mm-yyyy
    function fdate_eng_to_ind_2($dt, $use_long_format = false)
    {
        $return_dt = '';
        if (trim($dt) != '' && $dt != '0000-00-00') {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $dt);
            // $date = new DateTime($dt);

            if ($date) {
                $format = '%d-%m-%Y %H:%i';
                if ($use_long_format) $format = '%d %B %Y %H:%i';

                // $return_dt = strftime($format, $date->getTimestamp());
                $timestamp = $date->getTimestamp();
                $dateTime = (new DateTime())->setTimestamp($timestamp);
                $return_dt = $dateTime->format($format);
            }
        }
        return $return_dt;
    }
}

if (!function_exists('fdate_eng_to_ind_3')) {
    // Konversi yyyy-mm-dd -> dd-mm-yyyy
    function fdate_eng_to_ind_3($dt, $use_long_format = false, $strip_format = false)
    {
        $return_dt = '';
        if (trim($dt) != '' && $dt != '0000-00-00') {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $dt);
            // $date = new DateTime($dt);

            if ($date) {
                $format = '%d-%m-%Y';
                if ($use_long_format) $format = '%d %B %Y %H:%i';
                if ($strip_format) $format = '%d/%m/%Y';

                // $return_dt = strftime($format, $date->getTimestamp());
                $timestamp = $date->getTimestamp();
                $dateTime = (new DateTime())->setTimestamp($timestamp);
                $return_dt = $dateTime->format($format);
            }
        }
        return $return_dt;
    }
}


if (!function_exists('fdate_eng_to_ind_4')) {
    // Konversi yyyy-mm-dd -> dd-mm-yyyy
    function fdate_eng_to_ind_4($dt, $use_long_format = false, $strip_format = false)
    {
        $return_dt = '';
        if (trim($dt) != '' && $dt != '0000-00-00') {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $dt);
            // $date = new DateTime($dt);

            if ($date) {
                $format = 'd-m-Y';
                if ($use_long_format) $format = '%d %B %Y %H:%i';
                if ($strip_format) $format = '%d/%m/%Y';

                // $return_dt = strftime($format, $date->getTimestamp());
                $timestamp = $date->getTimestamp();
                $dateTime = (new DateTime())->setTimestamp($timestamp);
                $return_dt = $dateTime->format($format);
            }
        }
        return $return_dt;
    }
}

if(!function_exists('formatTanggalIndonesia')){
    function formatTanggalIndonesia($tanggal, $use_long_format = true) {
        // Buang bagian waktu jika ada (misal: '2026-06-16 00:00:00' → '2026-06-16')
        $tanggal = explode(' ', $tanggal)[0];

        $tanggalArray = explode('-', $tanggal);
        $tahun       = $tanggalArray[0];
        $bulanAngka  = $tanggalArray[1];
        $hari        = $tanggalArray[2];

        if ($use_long_format) {
            $bulanNama = bulan((int)$bulanAngka);
        } else {
            $bulanNama = bulans((int)$bulanAngka);
        }

        return $hari . ' ' . $bulanNama . ' ' . $tahun;
    }
}

/**
 * Konversi tanggal yyyy-mm-dd H:i:s -> dd-mm-yyyy H:i:s
 *
 * @param $dt
 * @return string
 */
function fdatetime_eng_to_ind($dt, $incSeconds = true, $shortYear = false)
{
    if (trim($dt) != '' && $dt != '0000-00-00 00:00:00') {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $dt);
        $fmt_year = 'Y';
        $fmt_sec = '';
        if ($shortYear) $fmt_year = 'y';
        if ($incSeconds) $fmt_sec = ':s';
        $format = sprintf('d-m-%s H:i%s', $fmt_year, $fmt_sec);
        return date_format($date, $format);
    }
    return '';
}

/**
 * Konversi tanggal dd-mm-yyyy H:i:s -> yyyy-mm-dd H:i:s
 *
 * @param $dt
 * @return string
 */
function fdatetime_ind_to_eng($dt)
{
    if (trim($dt) != '' && $dt != '00-00-0000 00:00:00') {
        $date = strtotime($dt);
        return date('Y-m-d H:i:s', $date);
    }
    return '';
}

//format tanggal timestamp
if (!function_exists('tgl_indo_timestamp')) {

    function tgl_indo_timestamp($tgl)
    {
        $inttime = date('Y-m-d H:i:s', strtotime($tgl)); //mengubah format menjadi tanggal biasa
        $tglBaru = explode(" ", $inttime); //memecah berdasarkan spaasi

        $tglBaru1 = $tglBaru[0]; //mendapatkan variabel format yyyy-mm-dd
        $tglBaru2 = $tglBaru[1]; //mendapatkan fotmat hh:ii:ss
        $tglBarua = explode("-", $tglBaru1); //lalu memecah variabel berdasarkan -

        $tgl = $tglBarua[2];
        $bln = $tglBarua[1];
        $thn = $tglBarua[0];

        //$bln=bulan($bln); //mengganti bulan angka menjadi text dari fungsi bulan
        $ubahTanggal = "$tgl-$bln-$thn $tglBaru2 "; //hasil akhir tanggal

        return $ubahTanggal;
    }
}

if (!function_exists('is_array_multi')) {
    function is_array_multi($arr)
    {
        $rv = array_filter($arr, 'is_array');
        if (count($rv) > 0) return true;
        return false;
    }
}

if (!function_exists('cempty_to_null')) {
    function cempty_to_null($value)
    {
        return empty($value) ? NULL : $value;
    }
}

if (!function_exists('array_to_tree')) {
    /**
     * Flat Array Parent Child to Tree Object
     * @author Adnan
     * @param array $items
     * @param array $config
     * @return array
     */
    function array_to_tree($items, $config = null)
    {
        ini_set('error_reporting', E_STRICT);

        if ($config === null) {
            $config = ['id' => 'id', 'parentId' => 'parentId'];
        }

        // the resulting unflattened tree
        $rootItems = [];

        // stores all already processed items with their ids as key so we can easily look them up
        $lookup = [];

        // idea of this loop:
        // whenever an item has a parent, but the parent is not yet in the lookup object, we store a preliminary parent
        // in the lookup object and fill it with the data of the parent later
        // if an item has no parentId, add it as a root element to rootItems
        $items_1 = $items;
        $cnt = count($items_1);
        for ($_i = 0; $_i < $cnt; $_i++) {
            $item = $items_1[$_i];
            $itemId = $item->{$config['id']};
            $parentId = $item->{$config['parentId']};

            // look whether item already exists in the lookup table
            //            if (!array_key_exists($itemId, $lookup)) {
            //                // item is not yet there, so add a children
            //                $item->children = [];
            //            }

            // add the current item's data to the item in the lookup table
            $lookup[$itemId] = $item;

            $TreeItem = $lookup[$itemId];

            if ($parentId === '0' || $parentId === null) {
                // is a root item
                $rootItems[] = $TreeItem;
            } else {

                // has a parent
                // look whether the parent already exists in the lookup table
                if (!array_key_exists($parentId, $lookup)) {
                    // parent is not yet there, so add a preliminary parent (its data will be added later)
                    $lookup[$parentId]->children = [];
                }

                // add the current item to the parent
                $lookup[$parentId]->children[] = $TreeItem;
            }
        }
        return $rootItems;
    }
}

if (!function_exists('build_tree')) {
    /**
     * Flat array to tree
     * https://stackoverflow.com/a/27360654/526523
     * @param $flatList - a flat list of tree nodes; a node is an array with keys: id, parentId, name.
     * @return array
     */
    function build_tree(array $flatList, $pidKey = 'parentId', $idKey = 'id', $root = null)
    {
        $tree = [];
        $grouped = [];
        foreach ($flatList as $node) {
            $grouped[$node[$pidKey]][] = $node;
        }

        $fnBuilder = function ($siblings) use (&$fnBuilder, $grouped, $idKey) {
            foreach ($siblings as $k => $sibling) {
                $id = $sibling[$idKey];
                if (isset($grouped[$id])) {
                    $sibling['children'] = $fnBuilder($grouped[$id]);
                }
                $siblings[$k] = $sibling;
            }
            return $siblings;
        };

        if (isset($grouped[$root])) $tree = $fnBuilder($grouped[$root]);
        return $tree;
    }
}

if (!function_exists('get_children_ids')) {
    /**
     * Get children ids from parent
     * @param $tree - a flat list of tree nodes; a node is an array with keys: id, parentId, name.
     * @return mixed
     */
    function get_children_ids(array $tree, $id, $pidKey = 'parentId', $idKey = 'id', $childKey = 'children', $buildTree = FALSE)
    {
        $results = [];

        if ($buildTree === TRUE) $tree = build_tree($tree, $pidKey, $idKey, $id);

        foreach ($tree as $el) {
            if ($el[$pidKey] == $id) {
                $results[] = $el[$idKey];
            }
            if (isset($el[$childKey]) && count($el[$childKey]) > 0 && ($children = get_children_ids($el[$childKey], $el[$idKey], $pidKey, $idKey, $childKey)) !== FALSE) {
                $results = array_merge($results, $children);
            }
        }
        return count($results) > 0 ? $results : FALSE;
    }
}

if (!function_exists('build_tree_list')) {
    function build_tree_list($elements, $pidKey = 'parentId', $idKey = 'id', $root = 0, $fieldName = null)
    {
        $branch = array();
        foreach ($elements as $element) {
            if ($element->{$pidKey} == $root) {
                $element->treename = $element->{$fieldName};
                $branch[] = $element;
                $children = $this->buildTree($elements, $element->{$idKey});
                if ($children) {
                    foreach ($children as $chd) {
                        if (strlen($chd->treename) >= 14 && substr($chd->treename, 0, 14) == "&nbsp;|_&nbsp;") {
                            $chd->treename = "&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;" . $chd->{$fieldName};
                        } elseif (strlen($chd->treename) >= 32 && substr($chd->treename, 0, 32) == "&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;") {
                            $chd->treename = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;" . $chd->{$fieldName};
                        } elseif (strlen($chd->treename) >= 32 && substr($chd->treename, 0, 50) == "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;") {
                            $chd->treename = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;" . $chd->{$fieldName};
                        } elseif (strlen($chd->treename) >= 68 && substr($chd->treename, 0, 60) == "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;") {
                            $chd->treename = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.|_&nbsp;" . $chd->{$fieldName};
                        } else {
                            $chd->treename = "&nbsp;|_&nbsp;" . $chd->{$fieldName};
                        }
                        $branch[] = $chd;
                    }
                }
            }
        }
        return $branch;
    }
}

if (!function_exists('get_icon_mime')) {
    function get_icon_mime($ext)
    {
        $mimes = array(
            'gif' => "fa fa-file-image-o",
            'jpg' => "fa fa-file-image-o",
            'png' => "fa fa-file-image-o",
            'jpeg' => "fa fa-file-image-o",
            'bmp' => "fa fa-file-image-o",
            'pdf' => "fa fa-file-pdf-o",
            'txt' => "fa fa-file-text-o",
            'csv' => "fa fa-file-excel-o",
            'xls' => "fa fa-file-excel-o",
            'xlsx' => "fa fa-file-excel-o",
            'doc' => "fa fa-file-word-o",
            'docx' => "fa fa-file-word-o",
            'ppt' => "fa fa-file-powerpoint-o",
            'pptx' => "fa fa-file-powerpoint-o"
        );


        $ret_value = "fa fa-file-o";
        foreach ($mimes as $key => $value) {
            if ($ext == $key) {
                $ret_value = $value;
                break;
            }
        }

        return $ret_value;
    }
}

if (!function_exists('contains_word')) {
    function contains_word($needle, $haystack, $case_sensitive = FALSE)
    {
        if ($case_sensitive === TRUE) {
            return strpos($haystack, $needle) !== false;
        }

        return stripos($haystack, $needle) !== false;
    }
}

if (!function_exists('message_box')) {
    function message_box($title, $message, $mode = 'info', $icon = '', $fadeout = false, $close = true)
    {
        $html = '<div class="alert alert-' . $mode;
        if ($close) {
            $html .= ' alert-dismissible';
        }
        if ($fadeout) {
            $html .= ' fade in';
        }
        $html .= '">';

        if ($close) {
            $html .= '<button type="button" class="btn-close" data-dismiss="alert" aria-hidden="true"></button>';
        }
        if (!empty($title)) {
            $html .= '<h4>';
            if (!empty($icon)) {
                $html .= '<i class="icon fa fa-' . $icon . '"></i>' . str_repeat('&nbsp;', 1);
            }
            $html .= $title;
            $html .= '</h4>';
        } elseif (empty($title) && !empty($icon)) {
            $html .= '<h4>';
            if (!empty($icon)) {
                $html .= '<i class="icon fa fa-' . $icon . '"></i>' . str_repeat('&nbsp;', 1);
            }
            $html .= '</h4>';
        }
        $html .= $message;
        $html .= '</div><!-- /.alert -->';
        if ($fadeout) {
            $html .= '<script type="text/javascript">';
            $html .= 'window.setTimeout(function(){'
                . 'jQuery(".alert").alert(\'close\');'
                . '},3000);';
            $html .= '</script>';
        }

        return $html;
    }
}

if (!function_exists('terbilang')) {
    function terbilang($satuan)
    {
        $satuan = abs($satuan);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        if ($satuan < 12)
            return " " . $huruf[$satuan];
        elseif ($satuan < 20)
            return  terbilang($satuan - 10) . " Belas";
        elseif ($satuan < 100)
            return  terbilang($satuan / 10) . " Puluh" . terbilang($satuan % 10);
        elseif ($satuan < 200)
            return " Seratus" . terbilang($satuan - 100);
        elseif ($satuan < 1000)
            return  terbilang($satuan / 100) . " Ratus" . terbilang($satuan % 100);
        elseif ($satuan < 2000)
            return " Seribu" . terbilang($satuan - 1000);
        elseif ($satuan < 1000000)
            return  terbilang($satuan / 1000) . " Ribu" . terbilang($satuan % 1000);
        elseif ($satuan < 1000000000)
            return  terbilang($satuan / 1000000) . " Juta" . terbilang($satuan % 1000000);
        //elseif ($satuan >= 1000000000)
        //    echo "Angka yang Anda masukkan terlalu besar.";
        elseif ($satuan < 1000000000000)
            return  terbilang($satuan / 1000000000) . " Miliar" . terbilang($satuan % 1000000000);
        elseif ($satuan < 1000000000000000)
            return  terbilang($satuan / 1000000000000) . " Triliun" . terbilang($satuan % 1000000000000);
        elseif ($satuan >= 1000000000000000)
            echo "Angka yang Anda masukkan terlalu besar.";
    }
}

if (!function_exists('hitung_hari')) {
    function hitung_hari($wkt)
    {
        if ($wkt < 30)
            return $wkt . " Hari";
        elseif ($wkt < 360)
            return ($wkt / 30) . " Bulan";
        elseif ($wkt >= 360)
            return ($wkt / 360) . " Tahun";
    }
}

if (!function_exists('rmvSpecialChar')) {
    function rmvSpecialChar($string) {
        // Menghapus semua karakter spesial kecuali huruf, angka, dan spasi
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
    }
}
