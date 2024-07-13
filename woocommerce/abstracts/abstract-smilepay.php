<?php

abstract class RY_WSI_SmilePay
{
    protected function generate_trade_no($order_ID, $order_prefix = '')
    {
        $trade_no = $order_prefix . $order_ID . 'TS' . random_int(0, 9) . strrev((string) time());
        $trade_no = apply_filters('ry_smilepay_trade_no', $trade_no);

        return substr($trade_no, 0, 18);
    }

    protected function link_server($post_url, $args)
    {
        wc_set_time_limit(40);

        $response = wp_remote_post($post_url, [
            'timeout' => 30,
            'body' => $args,
        ]);

        if (is_wp_error($response)) {
            RY_WSI_WC_Invoice::instance()->log('Link failed', WC_Log_Levels::ERROR, ['info' => $response->get_error_messages()]);
            return null;
        }

        if (wp_remote_retrieve_response_code($response) != '200') {
            RY_WSI_WC_Invoice::instance()->log('Link HTTP status error', WC_Log_Levels::ERROR, ['info' => $response->get_error_messages()]);
            return null;
        }

        $result = @simplexml_load_string($response['body']);

        if (!is_object($result)) {
            RY_WSI_WC_Invoice::instance()->log('Link response parse failed', WC_Log_Levels::ERROR, ['info' => $response->get_error_messages()]);
            return null;
        }

        return $result;
    }

    protected function get_order_id($ipn_info, $order_prefix = '')
    {
        if (isset($ipn_info['od_sob'])) {
            $order_ID = $ipn_info['od_sob'];
            $order_ID = (int) substr($order_ID, strlen($order_prefix), strrpos($order_ID, 'TS'));
            $order_ID = apply_filters('ry_smilepay_trade_no_to_order_id', $order_ID, $ipn_info['od_sob']);
            if ($order_ID > 0) {
                return $order_ID;
            }
        }
        return false;
    }

    protected function die_success()
    {
        die('1|OK');
    }

    protected function die_error()
    {
        die('0|');
    }
}
