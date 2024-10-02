<?php
// Get order details
$ordered_items = $order->get_items();

$store_gst = get_option('owlth-wp-plugin')['store_gst_number'];

$shipping_total = $order->get_shipping_total();

$order_fees = $order->get_items('fee');

$discount_total = $order->get_discount_total();

// Custom meta fields (e.g., GST number)
$claim_gst = $order->get_meta('_billing_claim_gst', true);
if ($claim_gst) {
    $gst_holder_name = $order->get_meta('_billing_gst_trade_name', true);
    $gst_number = $order->get_meta('_billing_gst_number', true);
}
$custom_logo_id = get_theme_mod('custom_logo');
$logo = wp_get_attachment_image_src($custom_logo_id, 'full');

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>A simple, clean, and responsive HTML invoice template</title>

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
            padding-right: 10px;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table,
        .invoice-box table tr.order-items table {
            padding-bottom: 40px;
        }

        tr.order-items tr.total-block table td:nth-child(2) {
            padding-right: 5px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        /* Total */
        tr.total-block table {
            max-width: 50%;
            width: 50%;
            float: right;
        }

        tr.total-block table tr td {
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }



        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /* Custom css */
        p {
            line-height: 1.5em;
        }

        .payment-info .details,
        .address-info .details {
            font-size: small;
        }

        .address-info .details {
            padding: 10px;
            border: 1px dashed #eee;
        }

        .invoice-box .address-info {
            display: flex;
            justify-content: space-between;
            padding: 40px 5px 20px;
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <!-- Logo, Invoice/Order number, and Order date -->
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <?php
                                if (has_custom_logo()) {
                                    echo '<img src="' . esc_url($logo[0]) . '" alt="' . get_bloginfo('name') . '" style="width: 100%; max-width: 165px">';
                                } else {
                                    echo '<h1>' . get_bloginfo('name') . '</h1>';
                                }
                                ?>
                            </td>
                            <td>
                                <p><?php _e('Invoice #:', 'woogst'); ?>
                                    <?php echo esc_html($order->get_order_number()); ?>
                                </p>
                                <p><?php echo date_i18n(get_option('date_format'), strtotime($order->get_date_created())); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <?php if (isset($gst_holder_name) && isset($gst_number)): ?>
                <!-- GST information if meta found for GST -->
                <tr class="information">
                    <td colspan="2">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <!-- Store GST info -->
                                <td>
                                    <p><?php echo get_bloginfo('name'); ?></p>
                                    <!-- store_gst_number -->
                                    <?php if ($store_gst): ?>
                                        <p style="font-size: x-small;">
                                            <b><?php _e('GST:', 'woogst'); ?></b>
                                            <?php echo esc_html($store_gst); ?>
                                        </p>
                                    <?php endif; ?>
                                </td>

                                <!-- Customer GST info - _billing_gst_number -->
                                <td>
                                        <p><?php echo esc_html($gst_holder_name); ?>
                                        </p>
                                        <p style="font-size: x-small;">
                                            <b><?php _e('GST:', 'woogst'); ?></b>
                                            <?php echo esc_html($gst_number); ?>
                                        </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php endif; ?>

            <!-- Order line items -->
            <tr class="order-items">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0">
                        <tr class="heading">
                            <td><?php _e('Item', 'woogst'); ?></td>
                            <td><?php _e('Price', 'woogst'); ?></td>
                        </tr>

                        <!-- Loop through order items -->
                        <?php
                        foreach ($ordered_items as $item_id => $item) {
                            $product = $item->get_product();
                            ?>
                            <tr class="item">
                                <td>
                                    <?php echo esc_html($item->get_name()); ?>
                                    <span style="font-size: x-small;margin-left:15px">x
                                        <?php echo esc_html($item->get_quantity()); ?></span>
                                </td>
                                <td><?php echo wc_price($item->get_total()); ?></td>
                            </tr>
                        <?php } ?>

                        <!-- Order total summary -->
                        <tr class="total-block">
                            <td colspan="2">
                                <table cellpadding="0" cellspacing="0">

                                    <!-- Sub total -->
                                    <tr class="sub-total">
                                        <td style="text-align: right;">
                                            <?php _e('Sub total:', 'woogst'); ?>
                                        </td>
                                        <td><?php echo wc_price($order->get_subtotal()); ?></td>
                                    </tr>

                                    <!-- Taxes (Display if taxes are present) -->
                                    <?php if ($order->get_total_tax() > 0): ?>
                                        <?php
                                        // Get tax rates
                                        $taxes = $order->get_tax_totals();
                                        foreach ($taxes as $tax) {
                                            ?>
                                            <tr class="tax">
                                                <td style="text-align: right;"><?php echo esc_html($tax->label) . ': '; ?></td>
                                                <td><?php echo wc_price($tax->amount) . '<br>'; ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php endif; ?>

                                    <!-- Shipping total -->
                                    <?php if ($shipping_total > 0): ?>
                                            <tr class="tax">
                                                <td style="text-align: right;"><?php echo __('Shipping cost', 'woogst') . ': '; ?></td>
                                                <td><?php echo wc_price($shipping_total) . '<br>'; ?></td>
                                            </tr>
                                    <?php endif; ?>

                                    <!-- Order fees -->
                                    <?php if (array_count_values($order_fees) > 0): 
                                        foreach( $order_fees as $item_id => $item_fee ):
                                            // The fee name
                                            $fee_name = $item_fee->get_name();
                                            // The fee total amount
                                            $fee_total = $item_fee->get_total();
                                        ?>
                                            <tr class="tax">
                                                <td style="text-align: right;"><?php echo esc_html($fee_name) . ': '; ?></td>
                                                <td><?php echo wc_price($fee_total) . '<br>'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <!-- If coupon applied -->
                                    <?php if ($discount_total > 0): ?>
                                        <tr class="discount">
                                            <td style="text-align: right;">
                                                <?php _e('Coupon discount:', 'woogst'); ?>
                                            </td>
                                            <td><?php echo wc_price($discount_total); ?></td>
                                        </tr>
                                    <?php endif; ?>

                                    <tr class="total">
                                        <td style="text-align: right;">
                                            <?php _e('Total:', 'woogst'); ?>
                                        </td>
                                        <td><?php echo wc_price($order->get_total()); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Payment information -->
            <tr class="payment-info">
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0">
                        <tr class="heading">
                            <td><?php _e('Payment Method', 'woogst'); ?></td>
                            <td><?php _e('Status', 'woogst'); ?></td>
                        </tr>
                        <tr class="details">
                            <td><?php echo esc_html($order->get_payment_method_title()); ?></td>
                            <?php if($order->get_payment_method() != 'cod'): ?>
                            <td><?php echo esc_html(ucfirst($order->get_status())); ?></td>
                            <?php else: ?>
                            <td><?php echo __('Cash on delivery', 'woogst'); ?></td>
                            <?php endif; ?>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Address information -->
            <tr class="address-info">
                <td class="billing">
                    <b><?php _e('Billing address:', 'woogst'); ?></b>
                    <p class="details"><?php echo wp_kses_post($order->get_formatted_billing_address()); ?></p>
                </td>
                <td class="shipping">
                    <b><?php _e('Shipping address:', 'woogst'); ?></b>
                    <p class="details"><?php echo wp_kses_post($order->get_formatted_shipping_address()); ?></p>
                </td>
            </tr>

        </table>
    </div>

</body>

</html>
<!-- <script>
        window.onload = function () {
            window.print();
        };
    </script> -->