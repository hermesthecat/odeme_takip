<?php
/**
 * Portföy API
 * @author A. Kerem Gök
 */

require_once 'config.php';
require_once 'borsa.php';

$borsaTakip = new BorsaTakip();
$portfoy = $borsaTakip->portfoyListele();

foreach ($portfoy as $hisse) {
    $anlik_fiyat = $borsaTakip->anlikFiyatGetir($hisse['sembol']);
    $kar_zarar = $borsaTakip->karZararHesapla($hisse);
    $kar_zarar_class = $kar_zarar >= 0 ? 'kar' : 'zarar';
    
    echo "<tr>
            <td>{$hisse['sembol']}</td>
            <td>{$hisse['adet']}</td>
            <td>{$hisse['alis_fiyati']} ₺</td>
            <td>{$anlik_fiyat} ₺</td>
            <td class='{$kar_zarar_class}'>" . number_format($kar_zarar, 2) . " ₺</td>
            <td>
                <button class='btn btn-danger btn-sm' onclick='hisseSil({$hisse['id']})'>Sil</button>
            </td>
        </tr>";
} 