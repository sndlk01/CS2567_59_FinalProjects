<?php

namespace App\Helpers;

class ThaiNumberHelper
{
    private static $numbers = [
        "" => ["ศูนย์", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"],
        "สิบ" => ["", "สิบ", "ยี่สิบ", "สามสิบ", "สี่สิบ", "ห้าสิบ", "หกสิบ", "เจ็ดสิบ", "แปดสิบ", "เก้าสิบ"],
        "ร้อย" => ["", "หนึ่งร้อย", "สองร้อย", "สามร้อย", "สี่ร้อย", "ห้าร้อย", "หกร้อย", "เจ็ดร้อย", "แปดร้อย", "เก้าร้อย"],
        "พัน" => ["", "หนึ่งพัน", "สองพัน", "สามพัน", "สี่พัน", "ห้าพัน", "หกพัน", "เจ็ดพัน", "แปดพัน", "เก้าพัน"],
    ];

    public static function convertToText($number)
    {
        $number = number_format($number, 2, '.', '');
        list($integer, $decimal) = explode('.', $number);
        
        $text = self::convertIntegerPart((int)$integer);
        $text .= 'บาท';
        
        if ($decimal > 0) {
            $text .= self::convertDecimalPart($decimal) . 'สตางค์';
        }
        
        return $text;
    }

    private static function convertIntegerPart($number)
    {
        if ($number == 0) return 'ศูนย์';
        
        $text = '';
        $positions = ['พัน', 'ร้อย', 'สิบ', ''];
        $digits = str_split(sprintf('%04d', $number));
        
        foreach ($digits as $i => $digit) {
            if ($digit > 0) {
                $text .= self::$numbers[$positions[$i]][$digit];
            }
        }
        
        return $text;
    }

    private static function convertDecimalPart($decimal)
    {
        $digits = str_split(sprintf('%02d', $decimal));
        $text = '';
        
        if ($digits[0] > 0) {
            $text .= self::$numbers['สิบ'][$digits[0]];
        }
        if ($digits[1] > 0) {
            $text .= self::$numbers[''][$digits[1]];
        }
        
        return $text;
    }
} 