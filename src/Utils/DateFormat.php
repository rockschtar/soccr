<?php

namespace Rockschtar\WordPress\Soccr\Utils;

class DateFormat
{

    public static function toWordPress(\DateTime $dateTime): string
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $dateTime->getTimestamp());
    }

}
