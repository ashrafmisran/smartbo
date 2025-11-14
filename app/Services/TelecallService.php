<?php

namespace App\Services;

class TelecallService
{
    /**
     * Get the telecall script HTML content
     */
    public static function getSkripPanggilan(): string
    {
        return '
            <h2>Skrip Panggilan Telefon – Perikatan Nasional</h2>

            <p class="text-blue-500"><strong>Pembuka:</strong><br>
            Assalamualaikum, saya [NAMA PEMANGGIL] dari pasukan sukarelawan Perikatan Nasional. 
            Maaf ganggu sekejap, boleh saya ambil sedikit masa Tuan/Puan?</p>
            <hr>
            <h2><strong>Bahagian 1: Soalan Kajian Halus</strong></h2>
            <p>
            Kami sedang buat sedikit tinjauan ringan — pada pandangan Tuan/Puan, 
            kalau ada pilihan, siapakah yang Tuan/Puan rasa paling layak menjaga kebajikan rakyat di 
            DUN [NAMA DUN] nanti?<br>
            (Dengar jawapan dengan sopan. Jika sebut parti lain, jangan lawan; 
            ucap terima kasih dan teruskan bahagian seterusnya dengan nada berhemah.)</p>
            <hr>
            <h2><strong>Bahagian 2: Jambatan ke Mesej Kempen</strong></h2>
            <p>Terima kasih atas pandangan Tuan/Puan.</p>
            <p>Kami juga ingin kongsikan sedikit — Perikatan Nasional kini berusaha membawa politik 
            yang lebih bersih dan berprinsip.</p>
            <p>Alhamdulillah, semasa waktu sukar dulu seperti Covid-19, kerajaan di bawah pimpinan PN 
            telah menjaga kebajikan rakyat, bantu dari segi bantuan tunai, moratorium, 
            dan inisiatif ekonomi rakyat.</p>
            <p>Ramai rakyat waktu itu rasa lega kerana keprihatinan dan kecekapan pentadbiran PN.</p>
            <hr>
            <h2><strong>Bahagian 3: Penutup Kempen</strong></h2>
            <p>Sebab itu, kami mengajak Tuan/Puan supaya pertimbangkan untuk 
            mengundi calon Perikatan Nasional di DUN [NAMA DUN].</p>
            <p>Kami percaya wakil PN akan terus membawa suara rakyat dengan integriti, 
            amanah dan keprihatinan — bukan janji kosong, tapi sudah terbukti dengan tindakan.</p>
            <p>Terima kasih banyak atas masa Tuan/Puan. Semoga Allah permudahkan urusan kita semua 
            dan berkati pilihan yang dibuat.</p>

            <p><strong>Jika penerima neutral atau positif:</strong><br>
            Terima kasih Tuan/Puan atas sokongan. Insya-Allah, kita doakan PN terus kuat 
            untuk bela rakyat.</p>

            <p><strong>Jika penerima menolak:</strong><br>
            Tidak mengapa Tuan/Puan, kami hormati pandangan. Terima kasih kerana sudi luangkan masa.</p>
        ';
    }

    /**
     * Get phone numbers from a Pengundi record
     * Handles comma-separated values in Tel_Bimbit and Tel_Rumah
     */
    public static function getPhoneNumbers($pengundi): array
    {
        $numbers = [];
        
        try {
            $bancian = $pengundi->bancian;
            
            if ($bancian) {
                // Process Tel_Bimbit (mobile numbers)
                if ($bancian->Tel_Bimbit && $bancian->Tel_Bimbit != '' && $bancian->Tel_Bimbit != '0') {
                    $mobileNumbers = self::parsePhoneNumbers($bancian->Tel_Bimbit, 'Bimbit');
                    $numbers = array_merge($numbers, $mobileNumbers);
                }
                
                // Process Tel_Rumah (home numbers)
                if ($bancian->Tel_Rumah && $bancian->Tel_Rumah != '' && $bancian->Tel_Rumah != '0') {
                    $homeNumbers = self::parsePhoneNumbers($bancian->Tel_Rumah, 'Rumah');
                    $numbers = array_merge($numbers, $homeNumbers);
                }
            }
        } catch (\Exception $e) {
            // If we can't access phone numbers due to permissions, return empty array
            \Log::warning("Could not fetch phone numbers for pengundi: " . $e->getMessage());
        }
        
        // Remove duplicates and return
        return array_values(array_unique($numbers, SORT_REGULAR));
    }

    /**
     * Parse comma-separated phone numbers and format them
     */
    private static function parsePhoneNumbers($phoneString, $type): array
    {
        $numbers = [];
        
        // Split by comma and clean up
        $phoneArray = explode(',', $phoneString);
        
        foreach ($phoneArray as $phone) {
            $phone = trim($phone);
            
            // Skip empty or invalid numbers
            if ($phone === '' || $phone === '0' || $phone === '-') {
                continue;
            }
            
            $numbers[] = [
                'number' => $phone,
                'type' => $type,
                'display' => $phone . ' (' . $type . ')',
            ];
        }
        
        return $numbers;
    }
}
