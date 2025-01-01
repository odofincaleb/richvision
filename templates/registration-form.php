<div class="richvision-registration-form">
    <h2>Member Registration</h2>
    <form id="richvision-registration-form" method="post">
		<?php wp_nonce_field( 'richvision_register_user', 'richvision_register_nonce' ); ?>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
		<div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="referral_code">Referral Code:</label>
            <input type="text" id="referral_code" name="referral_code">
        </div>
        <div class="form-group">
			<label for="payment_method">Payment Method:</label>
			<select id="payment_method" name="payment_method" required>
				<option value="woocommerce">Pay with WooCommerce</option>
				<option value="voucher">Use Voucher</option>
			</select>
		</div>
		 <div class="form-group voucher-field" style="display: none;">
            <label for="voucher_code">Voucher Code:</label>
            <input type="text" id="voucher_code" name="voucher_code">
        </div>
        <button type="submit" class="submit-button">Register</button>
		<div id="registration-message" class="message"></div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethodSelect = document.getElementById('payment_method');
        const voucherField = document.querySelector('.voucher-field');
        const voucherCodeInput = document.getElementById('voucher_code');


        paymentMethodSelect.addEventListener('change', function() {
            if (paymentMethodSelect.value === 'voucher') {
                voucherField.style.display = 'block';
				voucherCodeInput.required = true;

            } else {
                voucherField.style.display = 'none';
				voucherCodeInput.required = false;
            }
        });
    });
</script>
