<?php

final class RY_WSI_update
{
    public static function update()
    {
        $now_version = RY_WSI::get_option('version');

        if (false === $now_version) {
            $now_version = '0.0.0';
        }
        if (RY_WSI_VERSION == $now_version) {
            return;
        }

        if (version_compare($now_version, '1.0.4', '<')) {
            RY_WSI::update_option('version', '1.0.4');
        }
    }
}
