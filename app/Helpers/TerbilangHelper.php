<?php

namespace App\Helpers;

class TerbilangHelper
{
    public static function make($angka)
    {
        $angka = abs((float)$angka);
        $baca = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $terbilang = "";

        if ($angka < 12) {
            $terbilang = " " . $baca[(int)$angka];
        } else if ($angka < 20) {
            $terbilang = self::make($angka - 10) . " Belas";
        } else if ($angka < 100) {
            $terbilang = self::make($angka / 10) . " Puluh" . self::make(fmod($angka, 10));
        } else if ($angka < 200) {
            $terbilang = " Seratus" . self::make($angka - 100);
        } else if ($angka < 1000) {
            $terbilang = self::make($angka / 100) . " Ratus" . self::make(fmod($angka, 100));
        } else if ($angka < 2000) {
            $terbilang = " Seribu" . self::make($angka - 1000);
        } else if ($angka < 1000000) {
            $terbilang = self::make($angka / 1000) . " Ribu" . self::make(fmod($angka, 1000));
        } else if ($angka < 1000000000) {
            $terbilang = self::make($angka / 1000000) . " Juta" . self::make(fmod($angka, 1000000));
        } else if ($angka < 1000000000000) {
            $terbilang = self::make($angka / 1000000000) . " Milyar" . self::make(fmod($angka, 1000000000));
        } else if ($angka < 1000000000000000) {
            $terbilang = self::make($angka / 1000000000000) . " Trilyun" . self::make(fmod($angka, 1000000000000));
        }

        return $terbilang;
    }

    public static function terbilang($angka)
    {
        if ($angka == 0) {
            return "Nol Rupiah";
        }
        
        $hasil = trim(self::make($angka));
        return $hasil . " Rupiah";
    }
}
