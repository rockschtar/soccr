<?php

namespace Rockschtar\WordPress\Soccr\Utils;

class DateFormat
{
    public static function toWordPress(\DateTime $dateTime): string
    {
        $localTs = $dateTime->getTimestamp() + $dateTime->getOffset();
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $localTs, true);
    }

    public static function toDate(\DateTime $dateTime): string
    {
        $localTs = $dateTime->getTimestamp() + $dateTime->getOffset();
        $weekday = date_i18n('l', $localTs, true);
        $date = date_i18n(get_option('date_format'), $localTs, true);
        return $weekday . ', ' . $date;
    }

    public static function toTime(\DateTime $dateTime): string
    {
        return $dateTime->format(get_option('time_format'));
    }
}
