<?php
/**
 * Plugin Name:     Gelati Fast Checkout
 * Description:     Fully custom AJAX checkout (COD) with cart preview—no redirects.
 * Version:         1.1.0
 * Author:          Gelati Tech
 */

if (!defined('ABSPATH'))
    exit;

// 1) Shortcode: include the HTML template
add_shortcode('gelati_fast_checkout', 'gfc_render_custom_checkout_form');
function gfc_render_custom_checkout_form()
{
    if (!WC()->cart->get_cart_contents_count()) {
        return '<p class="woocommerce-info">Your cart is currently empty.</p>';
    }

    // Render the form template
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/checkout-form.php';
    return ob_get_clean();
}

// 2) Enqueue CSS & JS when the shortcode is on the page
add_action('wp_enqueue_scripts', 'gfc_enqueue_assets', 20);
function gfc_enqueue_assets()
{
    if (is_singular() && has_shortcode(get_post()->post_content, 'gelati_fast_checkout')) {
        $css_file = plugin_dir_path(__FILE__) . 'css/gfc-style.css';
        wp_enqueue_style(
            'gfc-style',
            plugin_dir_url(__FILE__) . 'css/gfc-style.css',
            [],
            filemtime($css_file)
        );

        $js_file = plugin_dir_path(__FILE__) . 'js/gfc-custom-checkout.js';
        wp_enqueue_script(
            'gfc-custom-checkout',
            plugin_dir_url(__FILE__) . 'js/gfc-custom-checkout.js',
            ['jquery'],
            filemtime($js_file),
            true
        );

        wp_localize_script('gfc-custom-checkout', 'gfc_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
        // ✅ Add WooCommerce built-in scripts that handle dynamic fields
        wp_enqueue_script('wc-checkout');
        wp_enqueue_script('wc-country-select');
        wp_enqueue_script('wc-address-i18n');
    }
}

// 3) AJAX handler
add_action('wp_ajax_gfc_place_order', 'gfc_place_order');
add_action('wp_ajax_nopriv_gfc_place_order', 'gfc_place_order');
function gfc_place_order()
{
    try {
        // Sanitize and collect all relevant billing fields
        $address = array(
            'first_name' => sanitize_text_field($_POST['billing_first_name'] ?? ''),
            'last_name'  => sanitize_text_field($_POST['billing_last_name'] ?? ''),
            'email'      => sanitize_email($_POST['billing_email'] ?? ''),
            'phone'      => sanitize_text_field($_POST['billing_phone'] ?? ''),
            'country'    => sanitize_text_field($_POST['billing_country'] ?? ''),
            'city'       => sanitize_text_field($_POST['billing_city'] ?? ''),
            'address_1'  => sanitize_text_field($_POST['billing_address_1'] ?? ''),
            'address_2'  => sanitize_text_field($_POST['billing_address_2'] ?? ''),
        );

        // Create the order
        $order = wc_create_order();
        foreach (WC()->cart->get_cart() as $item) {
            $order->add_product($item['data'], $item['quantity']);
        }
        $order->set_address($address, 'billing');
        $order->set_address($address, 'shipping');
        $order->set_payment_method('cod');
        $order->calculate_totals();
        $order->update_status('processing');

        // Save additional meta
        if (!empty($_POST['billing_neighborhood'])) {
            $order->update_meta_data('_billing_neighborhood', sanitize_text_field($_POST['billing_neighborhood']));
        }
        if (!empty($_POST['address_nickname'])) {
            $order->update_meta_data('_address_nickname', sanitize_text_field($_POST['address_nickname']));
        }
        $order->save();

        // Clear the cart
        WC()->cart->empty_cart();

        wp_send_json_success(array(
            'order_id' => $order->get_id(),
            'order_number' => $order->get_order_number(),
        ));
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}

add_action('wp_ajax_gfc_refresh_cart_totals', 'gfc_refresh_cart_totals');
add_action('wp_ajax_nopriv_gfc_refresh_cart_totals', 'gfc_refresh_cart_totals');

function gfc_refresh_cart_totals() {
  WC()->cart->calculate_totals();

  $subtotal = wc_price(WC()->cart->get_subtotal());
  $total = WC()->cart->get_cart_total();

  $packages = WC()->shipping->get_packages();
  $package  = reset($packages);
  $chosen_method = WC()->session->get('chosen_shipping_methods')[0] ?? '';
  $shipping_cost = '';

  if (!empty($package['rates'])) {
    foreach ($package['rates'] as $rate_id => $rate) {
      if ($rate_id === $chosen_method) {
        $shipping_cost = wc_price($rate->cost);
        break;
      }
    }
  }

  wp_send_json([
    'subtotal' => $subtotal,
    'shipping' => $shipping_cost ?: __('To be calculated at next step', 'gelati-fast-checkout'),
    'total'    => $total,
  ]);
}
add_action('wp_footer', 'inject_city_neighborhood_data_script');
function inject_city_neighborhood_data_script() {
    if (!is_checkout()) return;
    ?>
    <script type="text/javascript">
        console.log("Custom city-neighborhood script loaded");

        document.addEventListener('DOMContentLoaded', function () {
            const cityField = document.querySelector('select[name="billing_state"]');
            const neighborhoodField = document.querySelector('select[name="billing_neighborhood"]');

            const cityData = {
                alexandria: {
                    smouha: "Smouha",
                    gleem: "Gleem",
                    sidi_gaber: "Sidi Gaber",
                    loran: "Loran"
                },
                cairo: {
                    nasr_city: "Nasr City",
                    heliopolis: "Heliopolis",
                    maadi: "Maadi",
                    zamalek: "Zamalek"
                }
            };

            console.log("City Field:", cityField);
            console.log("Neighborhood Field:", neighborhoodField);

            if (!cityField || !neighborhoodField) return;

            function updateNeighborhoods(cityKey) {
                const neighborhoods = cityData[cityKey] || {};
                neighborhoodField.innerHTML = '<option value="">Select Neighborhood</option>';

                for (const [value, label] of Object.entries(neighborhoods)) {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    neighborhoodField.appendChild(option);
                }
            }

            // Set on load
            if (cityField.value) updateNeighborhoods(cityField.value);

            cityField.addEventListener('change', function () {
                updateNeighborhoods(this.value);
            });
        });
    </script>
    <?php
}
