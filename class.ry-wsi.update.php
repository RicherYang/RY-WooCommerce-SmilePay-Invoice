<?php

final class RY_WSI_update
{
    public static function update()
    {
        $now_version = RY_WSI::get_option('version');

        if ($now_version === false) {
            $now_version = '0.0.0';
        }
        if ($now_version == RY_WSI_VERSION) {
            return;
        }

        if (version_compare($now_version, '0.0.1', '<')) {
            RY_WSI::update_option('version', '0.0.1');
        }
    }
}
