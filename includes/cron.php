<?php

final class RY_WSI_Cron
{
    public static function add_action()
    {
        add_action(RY_WSI::OPTION_PREFIX . 'check_expire', [__CLASS__, 'check_expire']);
    }

    public static function check_expire()
    {
        RY_WSI_License::instance()->check_expire();
    }
}
