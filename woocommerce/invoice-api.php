<?php

class RY_WSI_WC_Invoice_Api extends RY_WSI_SmilePay
{
    protected static $_instance = null;

    protected $api_test_url = [
        'get' => 'https://ssl.smse.com.tw/api_test/SPEinvoice_Storage.asp',
        'invalid' => 'https://ssl.smse.com.tw/api_test/SPEinvoice_Storage_Modify.asp',
    ];

    protected $api_url = [
        'get' => 'https://ssl.smse.com.tw/api/SPEinvoice_Storage.asp',
        'invalid' => 'https://ssl.smse.com.tw/api/SPEinvoice_Storage_Modify.asp',
    ];

    public static function instance(): RY_WSI_WC_Invoice_Api
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get($order_ID)
    {
        $order = wc_get_order($order_ID);
        if (!$order) {
            return false;
        }

        if ($order->get_meta('_invoice_number')) {
            return false;
        }

        list($Grvc, $Verify_key) = RY_WSI_WC_Invoice::instance()->get_api_info();

        $args = $this->make_get_data($order, $Grvc, $Verify_key);
        if (0 == $args['AllAmount']) {
            $order->update_meta_data('_invoice_number', 'zero');
            $order->save();
            $order->add_order_note(__('Zero total fee without invoice', 'ry-woocommerce-smilepay-invoice'));
            return;
        }
        if (0 > $args['AllAmount']) {
            $order->update_meta_data('_invoice_number', 'negative');
            $order->save();
            $order->add_order_note(__('Negative total fee can\'t invoice', 'ry-woocommerce-smilepay-invoice'));
            return;
        }

        do_action('ry_wsi_get_invoice', $args, $order);
        $args['Description'] = implode('|', $args['Description']);
        $args['Quantity'] = implode('|', $args['Quantity']);
        $args['UnitPrice'] = implode('|', $args['UnitPrice']);
        $args['Unit'] = implode('|', $args['Unit']);
        $args['Amount'] = implode('|', $args['Amount']);

        RY_WSI_WC_Invoice::instance()->log('Issue invoice for #' . $order->get_id(), WC_Log_Levels::INFO, ['data' => $args]);

        if (RY_WSI_WC_Invoice::instance()->is_testmode()) {
            $post_url = $this->api_test_url['get'];
        } else {
            $post_url = $this->api_url['get'];
        }
        $result = $this->link_server($post_url, $args);

        if (null === $result) {
            return;
        }

        if ((string) $result->Status != '0') {
            $order->add_order_note(sprintf(
                /* translators: %s Error messade */
                __('Issue invoice error: %s', 'ry-woocommerce-smilepay-invoice'),
                (string) $result->Desc,
            ));
            return;
        }

        $invoice_date = new DateTime(str_replace('/', '-', (string) $result->InvoiceDate));
        $invoice_time = explode(':', (string) $result->InvoiceTime);
        $invoice_date->setTime($invoice_time[0], $invoice_time[1], $invoice_time[2]);

        if (apply_filters('ry_wsi_add_api_success_notice', true)) {
            $order->add_order_note(
                __('Invoice number', 'ry-woocommerce-smilepay-invoice') . ': ' . (string) $result->InvoiceNumber . "\n"
                . __('Invoice random number', 'ry-woocommerce-smilepay-invoice') . ': ' . (string) $result->RandomNumber . "\n"
                . __('Invoice create time', 'ry-woocommerce-smilepay-invoice') . ': ' . $invoice_date->format('Y-m-d H:i:s'),
            );
        }

        $order->update_meta_data('_invoice_number', (string) $result->InvoiceNumber);
        $order->update_meta_data('_invoice_random_number', (string) $result->RandomNumber);
        $order->update_meta_data('_invoice_date', $invoice_date->format('Y-m-d H:i:s'));
        $order->save();

        do_action('ry_wsi_get_invoice_response', $result, $order);
    }

    protected function make_get_data($order, $Grvc, $Verify_key)
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
            'TrackSystemID' => RY_WSI::get_option('used_track', ''),
            'Intype' => '07',
            'TaxType' => '1',
            'DonateMark' => '0',
            'LoveKey' => '',
            'orderid' => $this->generate_trade_no($order->get_id(), RY_WSI::get_option('order_prefix', '')),
            'MainRemark' => '',
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
                $data['UnitTAX'] = 'Y';
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

        $total_refunded = $order->get_total_refunded();
        $use_sku = 'yes' === RY_WSI::get_option('use_sku_as_name', 'no');
        $order_items = $order->get_items(['line_item']);
        if (count($order_items)) {
            foreach ($order_items as $order_item) {
                $item_total = $order_item->get_total();
                $item_refunded = $order->get_total_refunded_for_item($order_item->get_id(), $order_item->get_type());
                $total_refunded -= $item_refunded;
                if ('yes' !== get_option('woocommerce_tax_round_at_subtotal')) {
                    $item_total = round($item_total, wc_get_price_decimals());
                    $item_refunded = round($item_refunded, wc_get_price_decimals());
                }

                $item_total = $item_total - $item_refunded;
                $item_qty = $order_item->get_quantity() + $order->get_qty_refunded_for_item($order_item->get_id(), $order_item->get_type());

                if (0 == $item_total && 0 == $item_qty) {
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
                $data['Quantity'][] = $item_qty == 0 ? 1 : $item_qty;
                $data['Amount'][] = $item_total;
            }
        }

        $fee_items = $order->get_items(['fee']);
        if (count($fee_items)) {
            foreach ($fee_items as $fee_item) {
                $item_total = $fee_item->get_total();
                $item_qty = $fee_item->get_quantity();
                $item_total = round($item_total, wc_get_price_decimals());
                if (0 == $item_total && 0 == $item_qty) {
                    continue;
                }

                $data['Description'][] = $fee_item->get_name();
                $data['Quantity'][] = $item_qty == 0 ? 1 : $item_qty;
                $data['Amount'][] = $item_total;
            }
        }

        $shipping_fee = $order->get_shipping_total() - $order->get_total_shipping_refunded();
        $total_refunded -= $order->get_total_shipping_refunded();
        if ($shipping_fee != 0) {
            $data['Description'][] = __('shipping fee', 'ry-woocommerce-smilepay-invoice');
            $data['Quantity'][] = 1;
            $data['Amount'][] = round($shipping_fee, wc_get_price_decimals());
        }

        if ($total_refunded != 0) {
            $data['Description'][] = __('return fee', 'ry-woocommerce-smilepay-invoice');
            $data['Quantity'][] = 1;
            $data['Amount'][] = round(-$total_refunded, wc_get_price_decimals());
        }

        $total_amount = array_sum($data['Amount']);
        if ($total_amount != $data['AllAmount']) {
            switch (RY_WSI::get_option('amount_abnormal_mode', '')) {
                case 'product':
                    $data['Description'][] = RY_WSI::get_option('amount_abnormal_product', __('Discount', 'ry-woocommerce-smilepay-invoice'));
                    $data['Quantity'][] = 1;
                    $data['Amount'][] = round($data['AllAmount'] - $total_amount, wc_get_price_decimals());
                    break;
                case 'order':
                    $data['AllAmount'] = round($total_amount, 0);
                    break;
                default:
                    break;
            }
        }

        foreach ($data['Description'] as $key => $item) {
            $item = str_replace('|', '', $item);
            $data['Description'][$key] = mb_substr($item, 0, 80);
            $data['Amount'][$key] = round($data['Amount'][$key], 0);
            $data['Quantity'][$key] = round($data['Quantity'][$key], 3);
            $data['UnitPrice'][$key] = round($data['Amount'][$key] / $data['Quantity'][$key], 2);
            $data['Unit'][$key] = __('parcel', 'ry-woocommerce-smilepay-invoice');
        }

        $data['MainRemark'] = apply_filters('ry_wsi_invoice_main_remark', $data['MainRemark'], $data, $order);
        $data['MainRemark'] = mb_substr($data['MainRemark'], 0, 100);

        $data['Certificate_Remark'] = apply_filters('ry_wsi_invoice_remark', $data['Certificate_Remark'], $data, $order);
        $data['Certificate_Remark'] = mb_substr($data['Certificate_Remark'], 0, 30);

        return $data;
    }

    public function invalid($order_ID)
    {
        $order = wc_get_order($order_ID);
        if (!$order) {
            return false;
        }

        $invoice_number = $order->get_meta('_invoice_number');

        if ('zero' == $invoice_number || 'negative' == $invoice_number) {
            $order->delete_meta_data('_invoice_number');
            $order->save();
            return;
        }

        if (!$invoice_number) {
            return false;
        }

        list($Grvc, $Verify_key) = RY_WSI_WC_Invoice::instance()->get_api_info();
        $args = [
            'Grvc' => $Grvc,
            'Verify_key' => $Verify_key,
            'InvoiceNumber' => $invoice_number,
            'InvoiceDate' => str_replace('-', '/', substr($order->get_meta('_invoice_date'), 0, 10)),
            'types' => 'Cancel',
            'CancelReason' => __('Order cancel', 'ry-woocommerce-smilepay-invoice'),
        ];

        do_action('ry_wsi_invalid_invoice', $args, $order);

        RY_WSI_WC_Invoice::instance()->log('Invalid invoice for #' . $order->get_id(), WC_Log_Levels::INFO, ['data' => $args]);

        if (RY_WSI_WC_Invoice::instance()->is_testmode()) {
            $post_url = $this->api_test_url['invalid'];
        } else {
            $post_url = $this->api_url['invalid'];
        }
        $result = $this->link_server($post_url, $args);

        if (null === $result) {
            return;
        }

        if ((string) $result->Status != '0') {
            $order->add_order_note(sprintf(
                /* translators: %s Error messade */
                __('Invalid invoice error: %s', 'ry-woocommerce-smilepay-invoice'),
                (string) $result->Desc,
            ));
            return;
        }

        if (apply_filters('ry_wsi_add_api_success_notice', true)) {
            $order->add_order_note(
                __('Invalid invoice', 'ry-woocommerce-smilepay-invoice') . ': ' . (string) $result->InvoiceNumber,
            );
        }

        $order->delete_meta_data('_invoice_number');
        $order->delete_meta_data('_invoice_random_number');
        $order->delete_meta_data('_invoice_date');
        $order->save();

        do_action('ry_wsi_invalid_invoice_response', $result, $order);
    }
}
