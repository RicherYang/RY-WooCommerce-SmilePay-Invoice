<?php

class RY_WSI_Invoice_Api extends RY_SmilePay_Invoice
{
    public static $api_test_url = [
        'get' => 'https://ssl.smse.com.tw/api_test/SPEinvoice_Storage.asp',
        'invalid' => 'https://ssl.smse.com.tw/api_test/SPEinvoice_Storage_Modify.asp'
    ];

    public static $api_url = [
        'get' => 'https://ssl.smse.com.tw/api/SPEinvoice_Storage.asp',
        'invalid' => 'https://ssl.smse.com.tw/api/SPEinvoice_Storage_Modify.asp'
    ];

    public static function get($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        if ($order->get_meta('_invoice_number')) {
            return false;
        }

        list($Grvc, $Verify_key) = RY_WSI_Invoice::get_smilepay_api_info();

        $args = self::make_get_data($order, $Grvc, $Verify_key);
        if ($args['AllAmount'] == 0) {
            $order->update_meta_data('_invoice_number', 'zero');
            $order->save_meta_data();
            $order->add_order_note(__('Zero total fee without invoice', 'ry-woocommerce-smilepay-invoice'));
            return;
        }
        if ($args['AllAmount'] < 0) {
            $order->update_meta_data('_invoice_number', 'negative');
            $order->save_meta_data();
            $order->add_order_note(__('Negative total fee can\'t invoice', 'ry-woocommerce-smilepay-invoice'));
            return;
        }

        do_action('ry_wei_get_invoice', $args, $order);
        $args['Description'] = implode('|', $args['Description']);
        $args['Quantity'] = implode('|', $args['Quantity']);
        $args['UnitPrice'] = implode('|', $args['UnitPrice']);
        $args['Unit'] = implode('|', $args['Unit']);
        $args['Amount'] = implode('|', $args['Amount']);

        RY_WSI_Invoice::log('Create POST: ' . var_export($args, true));

        if ('yes' === RY_WSI::get_option('smilepay_testmode', 'no')) {
            $post_url = self::$api_test_url['get'];
        } else {
            $post_url = self::$api_url['get'];
        }
        $result = self::link_server($post_url, $args);

        if ($result === null) {
            return;
        }

        if ((string) $result->Status != '0') {
            $order->add_order_note(sprintf(
                /* translators: %s Error messade */
                __('Get invoice error: %s', 'ry-woocommerce-smilepay-invoice'),
                (string) $result->Desc
            ));
            return;
        }

        if (apply_filters('ry_wsi_add_api_success_notice', true)) {
            $order->add_order_note(
                __('Invoice number', 'ry-woocommerce-smilepay-invoice') . ': ' . (string) $result->InvoiceNumber . "\n"
                . __('Invoice random number', 'ry-woocommerce-smilepay-invoice') . ': ' . (string) $result->RandomNumber . "\n"
                . __('Invoice create time', 'ry-woocommerce-smilepay-invoice') . ': ' . str_replace('/', '-', (string) $result->InvoiceDate) . ' ' . (string) $result->InvoiceTime . "\n"
            );
        }

        $order->update_meta_data('_invoice_number', (string) $result->InvoiceNumber);
        $order->update_meta_data('_invoice_random_number', (string) $result->RandomNumber);
        $order->update_meta_data('_invoice_date', str_replace('/', '-', (string) $result->InvoiceDate) . ' ' . (string) $result->InvoiceTime);
        $order->save_meta_data();

        do_action('ry_wsi_get_invoice_response', $result, $order);
    }

    protected static function make_get_data($order, $Grvc, $Verify_key)
    {
        $country = $order->get_billing_country();
        $countries = WC()->countries->get_countries();
        $full_country = ($country && isset($countries[$country])) ? $countries[$country] : $country;

        $state = $order->get_billing_state();
        $states = WC()->countries->get_states($country);
        $full_state = ($state && isset($states[$state])) ? $states[$state] : $state;

        $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));

        $data = [
            'Grvc' => $Grvc,
            'Verify_key' => $Verify_key,
            'InvoiceDate' => $now->format('Y/m/d'),
            'InvoiceTime' => $now->format('H:i:s'),
            'Intype' => '07',
            'TaxType' => '1',
            'DonateMark' => '0',
            'LoveKey' => '',
            'orderid' => self::generate_trade_no($order->get_id(), RY_WSI::get_option('order_prefix')),
            'Certificate_Remark' => '#' . $order->get_order_number(),

            'Description' => [],
            'Quantity' => [],
            'UnitPrice' => [],
            'Unit' => [],
            'Amount' => [],
            'AllAmount' => round($order->get_total() - $order->get_total_refunded(), 0),

            'Name' => $order->get_billing_last_name() . $order->get_billing_first_name(),
            'Address' => $full_country . $full_state . $order->get_billing_city() . $order->get_billing_address_1() . $order->get_billing_address_2(),
            'Phone' => '',
            'Email' => $order->get_billing_email(),
        ];

        switch ($order->get_meta('_invoice_type')) {
            case 'personal':
                switch ($order->get_meta('_invoice_carruer_type')) {
                    case 'none':
                        break;
                    case 'smilepay_host':
                        $data['CarrierType'] = 'EJ0113';
                        break;
                    case 'MOICA':
                        $data['CarrierType'] = 'CQ0001';
                        $data['CarrierID'] = $order->get_meta('_invoice_carruer_no');
                        break;
                    case 'phone_barcode':
                        $data['CarrierType'] = '3J0002';
                        $data['CarrierID'] = $order->get_meta('_invoice_carruer_no');
                        break;
                }
                break;
            case 'company':
                $data['Buyer_id'] = $order->get_meta('_invoice_no');
                $company = $order->get_billing_company();
                if ($company) {
                    $data['CompanyName'] = $company;
                }
                break;
            case 'donate':
                $data['DonateMark'] = '1';
                $data['LoveKey'] = $order->get_meta('_invoice_donate_no');
                break;
        }

        $use_sku = 'yes' == RY_WSI::get_option('use_sku_as_name', 'no');
        $order_items = $order->get_items(['line_item']);
        if (count($order_items)) {
            foreach ($order_items as $order_item) {
                $item_total = $order_item->get_total();
                $item_refunded = $order->get_total_refunded_for_item($order_item->get_id(), $order_item->get_type());
                if ('yes' !== get_option('woocommerce_tax_round_at_subtotal')) {
                    $item_total = round($item_total, wc_get_price_decimals());
                    $item_refunded = round($item_refunded, wc_get_price_decimals());
                }

                $item_total = $item_total - $item_refunded;
                $item_qty = $order_item->get_quantity() + $order->get_qty_refunded_for_item($order_item->get_id(), $order_item->get_type());

                if ($item_total == 0 && $item_qty == 0) {
                    continue;
                }

                $item_name = '';
                if ($use_sku && method_exists($order_item, 'get_product')) {
                    $item_name = $order_item->get_product()->get_sku();
                }
                if (empty($item_name)) {
                    $item_name = $order_item->get_name();
                }

                $data['Description'][] = $item_name;
                $data['Quantity'][] = $item_qty;
                $data['Unit'][] = __('parcel', 'ry-woocommerce-smilepay-invoice');
                $data['Amount'][] = $item_total;
            }
        }

        $fee_items = $order->get_items(['fee']);
        if (count($fee_items)) {
            foreach ($fee_items as $fee_item) {
                $item_total = $fee_item->get_total();
                $item_qty = $fee_item->get_quantity();
                $item_total = round($item_total, wc_get_price_decimals());
                if ($item_total == 0 && $item_qty == 0) {
                    continue;
                }

                $data['Description'][] = $fee_item->get_name();
                $data['Quantity'][] = $item_qty;
                $data['Unit'][] = __('parcel', 'ry-woocommerce-smilepay-invoice');
                $data['Amount'][] = $item_total;
            }
        }

        $shipping_fee = $order->get_shipping_total() - $order->get_total_shipping_refunded();
        if ($shipping_fee != 0) {
            $data['Description'][] = __('shipping fee', 'ry-woocommerce-smilepay-invoice');
            $data['Quantity'][] = 1;
            $data['Unit'][] = __('parcel', 'ry-woocommerce-smilepay-invoice');
            $data['Amount'][] = round($shipping_fee, wc_get_price_decimals());
        }

        $total_amount = array_sum($data['Amount']);
        if ($total_amount != $data['AllAmount']) {
            switch(RY_WSI::get_option('amount_abnormal_mode', '')) {
                case 'product':
                    $data['Description'][] = RY_WSI::get_option('amount_abnormal_product', __('Discount', 'ry-woocommerce-smilepay-invoice'));
                    $data['Quantity'][] = 1;
                    $data['Unit'][] = __('parcel', 'ry-woocommerce-smilepay-invoice');
                    $data['Amount'][] = round($data['AllAmount'] - $total_amount, wc_get_price_decimals());
                    break;
                case 'order':
                    $data['AllAmount'] = sprintf('%d', $total_amount);
                    break;
                default:
                    break;
            }
        }

        foreach ($data['Description'] as $key => $item) {
            $item = str_replace('|', '', $item);
            $data['Description'][$key] = mb_substr($item, 0, 80);
        }
        foreach ($data['Amount'] as $key => $item) {
            $data['Amount'][$key] = sprintf('%d', $item);
            $data['UnitPrice'][$key] = sprintf('%.2f', $data['Amount'][$key] / $data['Quantity'][$key]);
        }

        $data['Certificate_Remark'] = apply_filters('ry_wsi_invoice_remark', $data['Certificate_Remark'], $data, $order);
        $data['Certificate_Remark'] = mb_substr($data['Certificate_Remark'], 0, 30);

        return $data;
    }

    public static function invalid($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        $invoice_number = $order->get_meta('_invoice_number');

        if ($invoice_number == 'zero' || $invoice_number == 'negative') {
            $order->delete_meta_data('_invoice_number');
            $order->save_meta_data();
            return;
        }

        if (!$invoice_number) {
            return false;
        }

        list($Grvc, $Verify_key) = RY_WSI_Invoice::get_smilepay_api_info();
        $args = [
            'Grvc' => $Grvc,
            'Verify_key' => $Verify_key,
            'InvoiceNumber' => $invoice_number,
            'InvoiceDate' => str_replace('-', '/', substr($order->get_meta('_invoice_date'), 0, 10)),
            'types' => 'Cancel',
            'CancelReason' => __('Invalid invoice', 'ry-woocommerce-smilepay-invoice'),
        ];

        do_action('ry_wsi_invalid_invoice', $args, $order);

        RY_WSI_Invoice::log('Invalid POST: ' . var_export($args, true));

        if ('yes' === RY_WSI::get_option('smilepay_testmode', 'no')) {
            $post_url = self::$api_test_url['invalid'];
        } else {
            $post_url = self::$api_url['invalid'];
        }
        $result = self::link_server($post_url, $args);

        if ($result === null) {
            return;
        }

        if ((string) $result->Status != '0') {
            $order->add_order_note(sprintf(
                /* translators: %s Error messade */
                __('Invalid invoice error: %s', 'ry-woocommerce-smilepay-invoice'),
                (string) $result->Desc
            ));
            return;
        }

        if (apply_filters('ry_wsi_add_api_success_notice', true)) {
            $order->add_order_note(
                __('Invalid invoice', 'ry-woocommerce-smilepay-invoice') . ': ' . (string) $result->InvoiceNumber
            );
        }

        $order->delete_meta_data('_invoice_number');
        $order->delete_meta_data('_invoice_random_number');
        $order->delete_meta_data('_invoice_date');
        $order->save_meta_data();

        do_action('ry_wsi_invalid_invoice_response', $result, $order);
    }
}
