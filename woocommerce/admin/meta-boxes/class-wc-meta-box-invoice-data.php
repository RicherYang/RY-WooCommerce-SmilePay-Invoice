<?php
class RY_WSI_MetaBox_Invoice_Data
{
    protected static $fields;

    protected static function init_fields($order)
    {
        self::$fields = [
            'type' => [
                'label' => __('Invoice type', 'ry-woocommerce-smilepay-invoice'),
                'show' => false,
                'class' => 'select short',
                'type' => 'select',
                'options' => [
                    'personal' => _x('personal', 'invoice type', 'ry-woocommerce-smilepay-invoice'),
                    'company' => _x('company', 'invoice type', 'ry-woocommerce-smilepay-invoice'),
                    'donate' => _x('donate', 'invoice type', 'ry-woocommerce-smilepay-invoice'),
                ],
            ],
            'carruer_type' => [
                'label' => __('Carruer type', 'ry-woocommerce-smilepay-invoice'),
                'show' => false,
                'class' => 'select short',
                'type' => 'select',
                'options' => [
                    'none' => _x('none', 'carruer type', 'ry-woocommerce-smilepay-invoice'),
                    'smilepay_host' => _x('smilepay_host', 'carruer type', 'ry-woocommerce-smilepay-invoice'),
                    'MOICA' => _x('MOICA', 'carruer type', 'ry-woocommerce-smilepay-invoice'),
                    'phone_barcode' => _x('phone_barcode', 'carruer type', 'ry-woocommerce-smilepay-invoice'),
                ],
            ],
            'carruer_no' => [
                'label' => __('Carruer number', 'ry-woocommerce-smilepay-invoice'),
                'show' => false,
                'type' => 'text',
            ],
            'no' => [
                'label' => __('Tax ID number', 'ry-woocommerce-smilepay-invoice'),
                'show' => false,
                'type' => 'text',
            ],
            'donate_no' => [
                'label' => __('Donate number', 'ry-woocommerce-smilepay-invoice'),
                'show' => false,
                'type' => 'text',
            ],
        ];
        if ('no' === RY_WSI::get_option('support_carruer_type_none', 'no')) {
            unset(self::$fields['carruer_type']['options']['none']);
        }
        unset(self::$fields['carruer_type']['options']['ecpay_host']);

        if ($order->is_paid()) {
            self::$fields['number'] = [
                'label' => __('Invoice number', 'ry-woocommerce-smilepay-invoice'),
                'show' => false,
                'type' => 'text',
            ];
            self::$fields['random_number'] = [
                'label' => __('Invoice random number', 'ry-woocommerce-smilepay-invoice'),
                'show' => false,
                'type' => 'text',
                'pattern' => '[0-9]{4}',
            ];
            self::$fields['date'] = [
                'label' => __('Invoice date', 'ry-woocommerce-smilepay-invoice'),
                'show' => false,
                'type' => 'date',
            ];
        }
    }

    public static function output($order)
    {
        $invoice_number = $order->get_meta('_invoice_number');
        $invoice_type = $order->get_meta('_invoice_type');
        $carruer_type = $order->get_meta('_invoice_carruer_type'); ?>

<h3 style="clear:both">
    <?php esc_html_e('Invoice info', 'ry-woocommerce-smilepay-invoice'); ?>
</h3>
<?php if (!empty($invoice_type)) { ?>
<div class="ivoice <?php echo($invoice_number ? '' : 'address'); ?>">
    <div class="ivoice_data_column">
        <p>
            <?php if ('zero' == $invoice_number) { ?>
            <strong><?php esc_html_e('Invoice number', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php esc_html_e('Zero no invoice', 'ry-woocommerce-smilepay-invoice'); ?><br>
            <?php } elseif ('negative' == $invoice_number) { ?>
            <strong><?php esc_html_e('Invoice number', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php esc_html_e('Negative no invoice', 'ry-woocommerce-smilepay-invoice'); ?><br>
            <?php } elseif ($invoice_number) { ?>
            <strong><?php esc_html_e('Invoice number', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php echo esc_html($invoice_number); ?><br>
            <strong><?php esc_html_e('Invoice random number', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php echo esc_html($order->get_meta('_invoice_random_number')); ?><br>
            <strong><?php esc_html_e('Invoice date', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php echo esc_html($order->get_meta('_invoice_date')); ?><br>
            <?php } ?>

            <strong><?php esc_html_e('Invoice type', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php echo esc_html(rywsi_invoice_type_to_name($invoice_type)); ?><br>

            <?php if ('personal' == $invoice_type) { ?>
            <strong><?php esc_html_e('Carruer type', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php echo esc_html(rywsi_carruer_type_to_name($carruer_type)); ?><br>

            <?php if (in_array($carruer_type, ['MOICA', 'phone_barcode'])) { ?>
            <strong><?php esc_html_e('Carruer number', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php echo esc_html($order->get_meta('_invoice_carruer_no')); ?><br>
            <?php } ?>
            <?php } ?>

            <?php if ('company' == $invoice_type) { ?>
            <strong><?php esc_html_e('Tax ID number', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php echo esc_html($order->get_meta('_invoice_no')); ?><br>
            <?php } ?>

            <?php if ('donate' == $invoice_type) { ?>
            <strong><?php esc_html_e('Donate number', 'ry-woocommerce-smilepay-invoice'); ?>:</strong> <?php echo esc_html($order->get_meta('_invoice_donate_no')); ?><br>
            <?php } ?>
        </p>
    </div>
    <div class="ivoice_action_column">
        <?php
        if ($invoice_number) {
            echo '<button id="invalid_smilepay_invoice" type="button" class="button" data-orderid="' . esc_attr($order->get_id()) . '">'
                . esc_html__('Invalid invoice', 'ry-woocommerce-smilepay-invoice')
                . '</button>';
        } elseif ($order->is_paid()) {
            echo '<button id="get_smilepay_invoice" type="button" class="button" data-orderid="' . esc_attr($order->get_id()) . '">'
                    . esc_html__('Issue invoice', 'ry-woocommerce-smilepay-invoice')
                    . '</button>';
        }
    ?>
    </div>
</div>
<?php } ?>

<div class="edit_address">
    <?php
    if (!$invoice_number) {
        self::init_fields($order);

        foreach (self::$fields as $key => $field) {
            $field['id'] = '_invoice_' . $key;
            $field['value'] = $order->get_meta($field['id']);

            switch ($field['type']) {
                case 'select':
                    woocommerce_wp_select($field);
                    break;
                case 'date':
                    ?>
    <p class="form-field form-field-wide <?php echo esc_attr($field['id']); ?>_field">
        <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></label>
        <input type="text" class="date-picker" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" maxlength="10" value="" pattern="<?php echo esc_attr(apply_filters('woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])')); ?>" />@
        &lrm;
        <input type="number" class="hour" placeholder="<?php esc_attr_e('h', 'ry-woocommerce-smilepay-invoice'); ?>" name="<?php echo esc_attr($field['id']); ?>_hour" min="0" max="23" step="1" value="" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:
        <input type="number" class="minute" placeholder="<?php esc_attr_e('m', 'ry-woocommerce-smilepay-invoice'); ?>" name="<?php echo esc_attr($field['id']); ?>_minute" min="0" max="59" step="1" value="" pattern="[0-5]{1}[0-9]{1}" />:
        <input type="number" class="second" placeholder="<?php esc_attr_e('s', 'ry-woocommerce-smilepay-invoice'); ?>" name="<?php echo esc_attr($field['id']); ?>_second" min="0" max="59" step="1" value="" pattern="[0-5]{1}[0-9]{1}" />
    </p>
    <?php
                    break;
                default:
                    woocommerce_wp_text_input($field);
                    break;
            }
        }
    } ?>
</div>
<?php
    }
}
?>
