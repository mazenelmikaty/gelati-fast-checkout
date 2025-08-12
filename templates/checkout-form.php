<!-- Progress Bar -->
<div class="woocommerce">
  <div class="go-back">
    <a href="/cart/">
      <img decoding="async" src="/wp-content/uploads/2022/12/arrow-right-1-1.svg">Go Back </a>
  </div>
  <div class="before-checkout">
    <div class="cart content">
      <li>1</li>
      <h4>Cart</h4>
    </div>
    <p>……………………</p>
    <div class="address content active">
      <li class="active">2</li>
      <h4 class="active">Address &amp; Payments</h4>
    </div>
    <p>……………………</p>
    <div class="content">
      <li>3</li>
      <h4>Order Done</h4>
    </div>
  </div>

  <div class="gelati-fast-checkout">
    <form id="gelati-fast-checkout-form" class="gfc-form" novalidate>
      <h3>Address & Delivery</h3>

      <!-- Address nickname (custom) -->
      <div class="gfc-field">
        <label for="gfc_address_nickname">Address nickname (optional)</label>
        <input id="gfc_address_nickname" name="address_nickname" type="text" autocomplete="section-billing nickname">
      </div>

      <!-- Full name -> split to WC first/last on submit -->
      <div class="gfc-field">
        <label for="gfc_full_name">Full name <span class="required">*</span></label>
        <input id="gfc_full_name" name="billing_full_name" type="text" required autocomplete="name"
          placeholder="Full name">
      </div>
      <input id="billing_first_name" name="billing_first_name" type="hidden">
      <input id="billing_last_name" name="billing_last_name" type="hidden">

      <!-- Phone -->
      <div class="gfc-field">
        <label for="gfc_phone">Phone <span class="required">*</span></label>
        <input id="gfc_phone" name="billing_phone" type="tel" required inputmode="tel" autocomplete="tel-national"
          placeholder="Phone">
      </div>

      <!-- Email -->
      <div class="gfc-field">
        <label for="gfc_email">Email <span class="required">*</span></label>
        <input id="gfc_email" name="billing_email" type="email" required autocomplete="email" placeholder="Email">
      </div>

      <!-- Country -->
      <div class="gfc-field">
        <label for="gfc_country">Country / Region <span class="required">*</span></label>
        <select id="gfc_country" name="billing_country" required>
          <option value="">Select a country</option>
          <option value="EG" selected>Egypt</option>
        </select>
      </div>

      <!-- City / Governate -->
      <div class="gfc-field">
        <label for="gfc_city">City / Governate <span class="required">*</span></label>
        <select id="gfc_city" name="billing_city" required>
          <option value="">Select city</option>
          <!-- JS will populate -->
        </select>
      </div>

      <!-- Neighborhood (custom) -->
      <div class="gfc-field">
        <label for="gfc_neighborhood">Neighborhood <span class="required">*</span></label>
        <select id="gfc_neighborhood" name="billing_neighborhood" required disabled>
          <option value="">Neighborhood</option>
        </select>
      </div>

      <!-- Street address 1 -->
      <div class="gfc-field">
        <label for="gfc_address_1">Street address <span class="required">*</span></label>
        <input id="gfc_address_1" name="billing_address_1" type="text" required
          placeholder="House number and street name" autocomplete="address-line1">
      </div>

      <!-- Street address 2 -->
      <div class="gfc-field">
        <label class="screen-reader-text" for="gfc_address_2">Apartment, suite, unit, etc. (optional)</label>
        <input id="gfc_address_2" name="billing_address_2" type="text"
          placeholder="Apartment, suite, unit, etc. (optional)" autocomplete="address-line2">
      </div>

      <button type="submit" id="gfc-submit" class="button alt">Place order</button>
    </form>

    <script>
      (function () {
        const $ = (id) => document.getElementById(id);

        // Egypt: cities & neighborhoods (sample set — extend as you like)
        const MAP = {
          'Alexandria': ['Stanley', 'Sidi Gaber', 'Smouha', 'Roushdy', 'Gleem', 'San Stefano', 'Miami', 'Louran', 'Ibrahimia', 'Sporting', 'Maamoura', 'Camp Caesar', 'Mansheya'],
          'Cairo': ['Zamalek', 'Maadi', 'Heliopolis', 'Nasr City', 'New Cairo', 'Garden City', 'Shubra', 'Downtown', 'Fifth Settlement', 'Mokattam'],
          'Giza': ['Dokki', 'Mohandessin', 'Agouza', 'Haram', '6th of October', 'Sheikh Zayed'],
          'Mansoura': ['Talkha', 'Gomhouria', 'Toriel', 'El-Sefarat'],
          'Tanta': ['El Gish', 'El Bahr']
        };

        function fillCities() {
          const city = $('gfc_city');
          city.innerHTML = '<option value="">Select city</option>';
          if ($('gfc_country').value !== 'EG') { city.disabled = true; return; }
          city.disabled = false;
          Object.keys(MAP).sort().forEach(c => {
            const o = document.createElement('option'); o.value = c; o.textContent = c; city.appendChild(o);
          });
        }

        function fillNeighborhoods() {
          const n = $('gfc_neighborhood');
          const list = MAP[$('gfc_city').value] || [];
          n.innerHTML = '<option value="">Neighborhood</option>';
          n.disabled = list.length === 0;
          list.forEach(x => {
            const o = document.createElement('option'); o.value = o.textContent = x; n.appendChild(o);
          });
        }

        function splitFullName() {
          const full = $('gfc_full_name').value.trim();
          if (!full) { $('billing_first_name').value = ''; $('billing_last_name').value = ''; return; }
          const parts = full.split(/\s+/);
          $('billing_first_name').value = parts.shift() || '';
          $('billing_last_name').value = parts.join(' ') || '.';
        }

        $('gfc_country').addEventListener('change', fillCities);
        $('gfc_city').addEventListener('change', fillNeighborhoods);
        $('gfc_full_name').addEventListener('input', splitFullName);

        // init
        fillCities();
        fillNeighborhoods();

        // ensure hidden first/last are filled before submit
        document.getElementById('gelati-fast-checkout-form').addEventListener('submit', splitFullName);
      })();
    </script>

    <div id="review-order" class="woocommerce-checkout-review-order">
      <h3>My Cart</h3>
      <table class="shop_table woocommerce-checkout-review-order-table">
        <tbody>
          <?php foreach (WC()->cart->get_cart() as $item):
            $product = $item['data'];
            $qty = $item['quantity'];
            $name = $product->get_name();
            $price = wc_price($product->get_price());
            $size = $product->get_attribute('pa_size');
            // Get product brand terms
            $terms = wc_get_product_terms($product->get_id(), 'product_brand', array('fields' => 'names'));
            $brand = !empty($terms) ? implode(', ', $terms) : '';
            ?>
            <tr class="cart_item">
              <td class="product-name">
                <strong class="product-quantity">&times;<?php echo intval($qty); ?></strong>
                <?php echo esc_html($name); ?>
                <?php if ($size || $brand): ?>
                  <dl class="variation">
                    <?php if ($size): ?>
                      <dt class="variation-Size"><?php esc_html_e('Size:', 'gelati-fast-checkout'); ?></dt>
                      <dd class="variation-Size">
                        <p><?php echo esc_html($size); ?></p>
                      </dd>
                    <?php endif; ?>
                    <?php if ($brand): ?>
                      <dt class="variation-Brand"><?php esc_html_e('Brand:', 'gelati-fast-checkout'); ?></dt>
                      <dd class="variation-Brand">
                        <p><?php echo esc_html($brand); ?></p>
                      </dd>
                    <?php endif; ?>
                  </dl>
                <?php endif; ?>
              </td>
              <td class="product-total">
                <?php echo $price; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="order-subtotal">
        <strong>Subtotal:</strong> <?php echo wc_price(WC()->cart->get_subtotal()); ?>
      </p>

      <?php
      $packages = WC()->shipping->get_packages();
      $package = reset($packages);
      $chosen_method = WC()->session->get('chosen_shipping_methods')[0] ?? '';
      $shipping_method = '';

      if (!empty($package['rates'])) {
        foreach ($package['rates'] as $rate_id => $rate) {
          if ($rate_id === $chosen_method) {
            $shipping_method = $rate;
            break;
          }
        }
      }
      ?>
      <p class="order-shipping">
        <strong>Delivery:</strong>
        <?php
        echo $shipping_method ? wc_price($shipping_method->cost) : __('To be calculated at next step', 'gelati-fast-checkout');
        ?>
      </p>
      <p class="order-total">
        <strong>Total:</strong> <?php echo WC()->cart->get_cart_total(); ?>
      </p>
    </div>

    <div id="gfc-notices">
      <div id="gfc-error"></div>
      <div id="gfc-success"></div>
    </div>
  </div>
</div>