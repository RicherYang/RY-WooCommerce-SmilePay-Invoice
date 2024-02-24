<?php

final class RY_WSI_Cron
{
    private static $initiated = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::$initiated = true;

            self::set_event();
        }
    }

    protected static function set_event()
    {
        add_action(RY_WSI::OPTION_PREFIX . 'check_update', ['RY_WSI_Updater', 'check_update']);
        if (!wp_next_scheduled(RY_WSI::OPTION_PREFIX . 'check_update')) {
            $time = wp_next_scheduled('wp_update_plugins');
            if (false == $time) {
                $time = time();
            }
            wp_schedule_event($time + MINUTE_IN_SECONDS, 'daily', RY_WSI::OPTION_PREFIX . 'check_update');
        }

        add_action(RY_WSI::OPTION_PREFIX . 'check_expire', ['RY_WSI_License', 'check_expire']);
        if (!wp_next_scheduled(RY_WSI::OPTION_PREFIX . 'check_expire')) {
            wp_schedule_event(time() + MINUTE_IN_SECONDS, 'daily', RY_WSI::OPTION_PREFIX . 'check_expire');
        }
    }
}

RY_WSI_Cron::init();
