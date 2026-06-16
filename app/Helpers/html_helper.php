<?php

function btn_action_group($paramId, $edit_url = '', $delete_url = '', $att_other = null)
{
    $att = array(
        'data-toggle' => 'tooltip',
        'data-placement' => 'top',
    );
    $html = '';
    $html .= '<div class="btn-group">
                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cog"></i> Aksi <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-act" role="menu">';
    $att = array();
    if (is_array($edit_url)) {
        $html .= '<li>';
        $att['title'] = $edit_url['title'];
        $att['class'] = $edit_url['class'];
        if (isset($edit_url['onclick']))
            $att['onclick'] = $edit_url['onclick'];
        if ($edit_url['url'] != "" && $edit_url['url'] != "#")
            $edit_url['url'] .= $paramId;
        $html .=  anchor($edit_url['url'], '<i class="fa fa-fw fa-edit"></i>Edit', $att);
        $html .= '</li>';
    } elseif ($edit_url != "") {
        $html .= '<li>';
        $att['title'] = 'Edit';
        $att['class'] = 'editButton';
        if ($edit_url != "" && $edit_url != "#")
            $edit_url .= $paramId;
        $html .=  anchor($edit_url, '<i class="fa fa-fw fa-edit"></i>Edit', $att);
        $html .= '</li>';
    }

    $att = array();
    if (is_array($delete_url)) {
        $html .= '<li>';
        $att['title'] =  $delete_url['title'];
        $att['class'] = $delete_url['class'];

        if (isset($delete_url['del_msg']))
            $att['del_msg'] = $delete_url['del_msg'];
        else
            $att['del_msg'] = "Anda yakin ingin menghapus data ini?";

        if (isset($delete_url['url']))
            $att['url'] = $delete_url['url'];
        else
            $att['url'] = "";

        $html .= '<a href="javascript:void(0)" class="atr_del" data-item-delete="' . $att['url'] . $paramId . '" data-confirm-message="' . $att['del_msg'] . '" title="Hapus"><i class="fa fa-fw fa-trash"></i> Hapus</a></li>';
    } elseif ($delete_url != "") {
        $html .= '<li>';
        $att['title'] = 'Hapus';
        $att['class'] = 'deleteButton';
        $att['onclick'] = "return confirm('Hapus data ini ?');";
        if ($delete_url != "" && $delete_url != "#")
            $delete_url .= $paramId;
        $html .=  anchor($delete_url, '<i class="fa fa-fw fa-trash"></i>Hapus', $att);
        $html .= '</li>';
    }

    $att = array();
    if (!empty($att_other) && is_array($att_other)) {
        $html .= '<li>';
        $att['title'] = $att_other['title'];
        $att['class'] = $att_other['class'];
        if (isset($att_other['onclick']))
            $att['onclick'] = $att_other['onclick'];
        if (isset($att_other['target']))
            $att['target'] = $att_other['target'];

        if ($att_other['url'] != "" && $att_other['url'] != "#")
            $att_other['url'] .= $paramId;
        if (isset($att_other['icon_class']))
            $html .=  anchor($att_other['url'], '<i class="fa fa-fw ' . $att_other['icon_class'] . '"></i>' . $att_other['title'], $att);
        else
            $html .=  anchor($att_other['url'], '<i class="fa fa-fw fa-external-link"></i>' . $att_other['title'], $att);
        $html .= '</li>';
    }
    $html .= '  </ul>
            </div>';

    return $html;
}

function getBulan($bln)
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

function getPackHeaders(array $rows): array
{
    $packNames = [];
    foreach ($rows as $row) {
        $packs = parsePacks($row->pack_data);
        foreach (array_keys($packs) as $name) {
            if (!in_array($name, $packNames)) {
                $packNames[] = $name;
            }
        }
    }

    usort($packNames, function($a, $b) {
        $aIsNum = is_numeric($a);
        $bIsNum = is_numeric($b);

        if ($aIsNum && $bIsNum) {
            // keduanya angka → urutkan numerik
            return $a - $b;
        } elseif ($aIsNum && !$bIsNum) {
            // angka duluan sebelum huruf
            return -1;
        } elseif (!$aIsNum && $bIsNum) {
            return 1;
        } else {
            // keduanya huruf → urutkan A-Z
            return strcasecmp($a, $b);
        }
    });

    return $packNames;
}
function parsePacks($data): array
{
    $result = [];
    if (empty($data)) return $result;

    foreach (explode('|', $data) as $item) {
        $parts = explode(':', $item, 2); // limit 2 agar aman
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $qty  = trim($parts[1]);
            $result[$name] = $qty;
        }
    }
    return $result;
}

/**
 * Hitung jumlah kolom pack maksimal dari semua rows
 */
function getMaxPacks(array $rows): int
{
    $max = 0;
    foreach ($rows as $row) {
        $packs = parsePacks($row->pack_data);
        $max = max($max, count($packs));
    }
    return $max;
}
