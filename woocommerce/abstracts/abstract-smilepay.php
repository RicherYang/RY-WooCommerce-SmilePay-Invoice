<?php

abstract class RY_SmilePay_Invoice
{
    protected static function generate_trade_no($order_id, $order_prefix = '')
    {
        $trade_no = $order_prefix . $order_id . 'TS' . random_int(0, 9) . strrev((string) time());
        $trade_no = substr($trade_no, 0, 20);
        $trade_no = apply_filters('ry_smilepay_trade_no', $trade_no);
        return substr($trade_no, 0, 20);
    }

    protected static function link_server($post_url, $args)
    {
        wc_set_time_limit(40);

        $response = wp_remote_post($post_url, [
            'timeout' => 20,
            'body' => $args
        ]);

        if (is_wp_error($response)) {
            RY_WSI_Invoice::log('Link SmilePay failed. Post error: ' . implode("\n", $response->get_error_messages()), 'error');
            return null;
        }

        if ($response['response']['code'] != '200') {
            RY_WSI_Invoice::log('Link SmilePay failed. Http code: ' . $response['response']['code'], 'error');
            return null;
        }

        RY_WSI_Invoice::log('Link SmilePay result: ' . $response['body']);
        $result = @simplexml_load_string($response['body']);

        if (!is_object($result)) {
            RY_WSI_Invoice::log('Link SmilePay failed. Response parse failed.', 'error');
            return null;
        }

        RY_WSI_Invoice::log('Link SmilePay result: ' . var_export($result, true));

        return $result;
    }

    protected static function get_order_id($ipn_info, $order_prefix = '')
    {
        if (isset($ipn_info['od_sob'])) {
            $order_id = $ipn_info['od_sob'];
            $order_id = (int) substr($order_id, strlen($order_prefix), strrpos($order_id, 'TS'));
            $order_id = apply_filters('ry_smilepay_trade_no_to_order_id', $order_id, $ipn_info['od_sob']);
            if ($order_id > 0) {
                return $order_id;
            }
        }
        return false;
    }

    protected static function die_success()
    {
        die('1|OK');
    }

    protected static function die_error()
    {
        die('0|');
    }
}
