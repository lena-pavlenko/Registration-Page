<?php
class Helper 
{
    public static function tokenGenerate(int $count = 64): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $count; $i++) {
            $randstring .= $characters[rand(0, strlen($characters)-1)];
        }
        return $randstring;
    }

    public static function convertTime(int $time): string
    {
        $sec = $time % 60;
        $min = intdiv($time, 60);
        $hours = intdiv($time, 3600);
        $min = $min % 60;
        $convertedTime = "$hours ч. $min мин. $sec сек.";
        return $convertedTime;
    }

    public static function dd(...$data): void
    {
        foreach ($data as $value) {
            echo '<pre>';
            var_dump($value);
            echo '</pre>';
        }
        
    }
}