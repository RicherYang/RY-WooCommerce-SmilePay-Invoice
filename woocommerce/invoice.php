<?php

final class RY_WSI_WC_Invoice extends RY_WSI_Model
{
    protected static $_instance = null;

    protected $model_type = 'smilepay_invoice';

    public static function instance(): RY_WSI_WC_Invoice
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        include_once RY_WSI_PLUGIN_DIR . 'woocommerce/abstracts/abstract-smilepay.php';
        include_once RY_WSI_PLUGIN_DIR . 'woocommerce/invoice-api.php';

        switch (RY_WSI::get_option('get_mode')) {
            case 'auto_paid':
                $paid_statuses = wc_get_is_paid_statuses();
                foreach ($paid_statuses as $status) {
                    add_action('woocommerce_order_status_' . $status, [$this, 'auto_get_invoice']);
                }
                break;
            case 'auto_completed':
                $completed_statuses = ['completed'];
                foreach ($completed_statuses as $status) {
                    add_action('woocommerce_order_status_' . $status, [$this, 'auto_get_invoice']);
                }
                break;
        }

        if ('auto_cancell' === RY_WSI::get_option('invalid_mode')) {
            add_action('woocommerce_order_status_cancelled', [$this, 'auto_delete_invoice']);
            add_action('woocommerce_order_status_refunded', [$this, 'auto_delete_invoice']);
        }

        if (is_admin()) {
            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/admin/ajax.php';
            RY_WSI_WC_Admin_Ajax::instance();

            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/admin/invoice.php';
            RY_WSI_WC_Admin_Invoice::instance();
        } else {
            add_filter('default_checkout_invoice_company_name', [$this, 'set_default_invoice_company_name']);
            if ('yes' === RY_WSI::get_option('show_invoice_number', 'no')) {
                add_filter('woocommerce_account_orders_columns', [$this, 'add_invoice_column']);
                add_action('woocommerce_my_account_my_orders_column_invoice-number', [$this, 'show_invoice_column']);
            }
        }
    }

    public function auto_get_invoice($order_ID)
    {
        $order = wc_get_order($order_ID);
        if (!$order) {
            return false;
        }

        $skip_shipping = apply_filters('ry_wsi_skip_autoget_invoice_shipping', []);
        if (!empty($skip_shipping)) {
            foreach ($order->get_items('shipping') as $item) {
                if (in_array($item->get_method_id(), $skip_shipping)) {
                    return false;
                }
            }
        }

        if ('yes' === RY_WSI::get_option('skip_foreign_order', 'no')) {
            if ('TW' !== $order->get_billing_country()) {
                if ($order->needs_shipping_address()) {
                    if ('TW' !== $order->get_shipping_country()) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }

        WC()->queue()->schedule_single(time() + 10, RY_WSI::OPTION_PREFIX . 'auto_get_invoice', [$order_ID], '');
    }

    public function auto_delete_invoice($order_ID)
    {
        $order = wc_get_order($order_ID);
        if (!$order) {
            return false;
        }

        $invoice_number = $order->get_meta('_invoice_number');
        if ($invoice_number) {
            if ('zero' == $invoice_number) {
            } elseif ('negative' == $invoice_number) {
            } else {
                WC()->queue()->schedule_single(time() + 10, RY_WSI::OPTION_PREFIX . 'auto_invalid_invoice', [$order_ID], '');
            }
        }
    }

    public function set_default_invoice_company_name()
    {
        if (is_user_logged_in()) {
            $customer = new WC_Customer(get_current_user_id(), true);

            return $customer->get_billing_company();
        }

        return '';
    }

    public function add_invoice_column($columns)
    {
        $add_index = array_search('order-total', array_keys($columns)) + 1;
        $pre_array = array_splice($columns, 0, $add_index);
        $array = [
            'invoice-number' => __('Invoice number', 'ry-woocommerce-smilepay-invoice'),
        ];
        return array_merge($pre_array, $array, $columns);
    }

    public function show_invoice_column($order)
    {
        $invoice_number = $order->get_meta('_invoice_number');
        if (!in_array($invoice_number, ['delay', 'zero', 'negative'])) {
            echo esc_html($invoice_number);
        }
    }

    public function get_api_info()
    {
        if ($this->is_testmode()) {
            $Grvc = 'SEI1000034';
            $Verify_key = '9D73935693EE0237FABA6AB744E48661';
        } else {
            $Grvc = RY_WSI::get_option('smilepay_Grvc');
            $Verify_key = RY_WSI::get_option('smilepay_Verify_key');
        }

        return [$Grvc, $Verify_key];
    }
}
