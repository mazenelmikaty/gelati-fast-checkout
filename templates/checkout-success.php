<!-- Progress Bar -->
<div class="before-checkout">
  <div class="cart content done">
    <li>1</li>
    <h4>Cart</h4>
  </div>
  <p>……………………</p>
  <div class="address content done">
    <li>2</li>
    <h4>Address &amp; Payments</h4>
  </div>
  <p>……………………</p>
  <div class="done content active">
    <li class="active">3</li>
    <h4 class="active">Order Done</h4>
  </div>
</div>

<div class="gfc-order-success woocommerce-order">
  <nav class="gfc-steps">
    <ul>
      <li class="done"><span>1</span><br>Cart</li>
      <li class="done"><span>2</span><br>Address &amp; Payments</li>
      <li class="current"><span>3</span><br>Order Done</li>
    </ul>
  </nav>

  <div class="gfc-success-content">
    <img src="https://uat.gelati.me/wp-content/uploads/2022/12/Group-580.svg"
         alt="Order received" class="gfc-success-illustration">

    <div class="gfc-success-text">
      <p class="gfc-order-number">#<?php echo esc_html( $order_number ); ?></p>
      <h2>Your order has been received Successfully.</h2>
      <p>Go to your account to track your order.</p>
      <a href="<?php echo esc_url( $order_url ); ?>" class="button gfc-button">
        Go To Order
      </a>
    </div>
  </div>
</div>