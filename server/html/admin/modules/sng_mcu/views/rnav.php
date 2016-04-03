<?php

$li[] = '<a href="config.php?display='. urlencode($display) . '&action=add">' . _("Pair MCU") . '</a>';


if (isset($sng_mcu_results)){
    foreach ($sng_mcu_results as $r) {
        $r['host'] = $r['host'] ? $r['host'] : 'MCU ID: ' . $r['id'];
        $li[] = '<a id="' . ( $id == $r['id'] ? 'current' : '')
            . '" href="config.php?display=' . urlencode($display) . '&amp;action=update&amp;id='
            . $r['id'] . '">'
            . $r['host'] .'</a>';
    }
}

echo '<div class="rnav">' . ul($li) . '</div>';
?>
