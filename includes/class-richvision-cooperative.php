<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
class RichVision_Cooperative{

	public function __construct(){
		add_action('init', [$this, 'register_shortcodes']);
		add_action('admin_menu', [$this, 'add_admin_menu']);
		add_action('admin_init', [$this, 'register_settings']);
	}

	/**
	 * register_shortcodes
	 *
	 * @return void
	 */
	public function register_shortcodes(){
		add_shortcode('richvision_registration', [$this, 'registration_shortcode']);
	}
		/**
	 * add_admin_menu
	 *
	 * @return void
	 */
	public function add_admin_menu(){
		add_menu_page(
			'RichVision Settings',
			'RichVision',
			'manage_options',
			'richvision-settings',
			[$this, 'settings_page'],
			'dashicons-admin-generic',
			6
		);
	}
		/**
	 * settings_page
	 *
	 * @return void
	 */
	public function settings_page(){
		?>
			<div class="wrap">
				<h2>RichVision Settings</h2>
				<form method="post" action="options.php">
					<?php
						settings_fields('richvision_settings');
						do_settings_sections('richvision-settings');
						submit_button();
					?>
				</form>
			</div>
		<?php
	}
	/**
	 * register_settings
	 *
	 * @return void
	 */
	public function register_settings(){
		register_setting('richvision_settings', 'richvision_registration_fee', 'floatval');

		add_settings_section(
			'richvision_general_settings',
			'General Settings',
			[$this, 'settings_section_callback'],
			'richvision-settings'
		);

		add_settings_field(
			'richvision_registration_fee',
			'Registration Fee',
			[$this, 'registration_fee_callback'],
			'richvision-settings',
			'richvision_general_settings'
		);
	}
	/**
	 * settings_section_callback
	 *
	 * @return void
	 */
	public function settings_section_callback(){
		echo 'Manage the general settings for the richvision cooperative.';
	}
	/**
	 * registration_fee_callback
	 *
	 * @return void
	 */
	public function registration_fee_callback(){
		$fee = get_option('richvision_registration_fee', 2500);
		?>
			<input type="number" name="richvision_registration_fee" value="<?php echo esc_attr($fee); ?>" step="0.01">
		<?php
	}
	/**
	 * registration_shortcode
	 *
	 * @param  mixed $atts
	 * @param  mixed $content
	 * @return void
	 */
	public function registration_shortcode($atts, $content = null){
		//handle form submission
		$this->handle_registration_form();

		ob_start();
		include RICHVISION_COOPERATIVE_PLUGIN_DIR . 'templates/registration-form.php';
		return ob_get_clean();
	}
	/**
	 * handle_registration_form
	 *
	 * @return void
	 */
	public function handle_registration_form(){
		if ( ! isset( $_POST['richvision_register_nonce'] ) || ! wp_verify_nonce( $_POST['richvision_register_nonce'], 'richvision_register_user' ) ) {
			return;
		}
		
		if ( ! isset($_POST['username']) || ! isset($_POST['email']) || ! isset($_POST['password']) || ! isset($_POST['first_name']) || ! isset($_POST['last_name']) || ! isset($_POST['payment_method']) ) {
			$this->display_message('error', 'Please fill all the required fields');
			return;
		}
		
		$username = sanitize_user($_POST['username']);
		$email = sanitize_email($_POST['email']);
		$password = $_POST['password'];
		$first_name = sanitize_text_field($_POST['first_name']);
		$last_name = sanitize_text_field($_POST['last_name']);
		$referral_code = isset($_POST['referral_code']) ? sanitize_text_field($_POST['referral_code']) : '';
		$payment_method = sanitize_text_field($_POST['payment_method']);
		$voucher_code = isset($_POST['voucher_code']) ? sanitize_text_field($_POST['voucher_code']) : '';
		$registration_fee = get_option('richvision_registration_fee', 2500);


		//Validate Username, Email and Password
		$username_exists = username_exists($username);
		if ($username_exists) {
			$this->display_message('error', 'Username already exists, please use another username');
			return;
		}
		$email_exists = email_exists($email);
		if ($email_exists) {
			$this->display_message('error', 'Email already exists, please use another email');
			return;
		}

		//create user
		$user_id = wp_create_user($username, $password, $email);

		if(is_wp_error($user_id)){
			$this->display_message('error', 'Error creating user, please try again.');
			return;
		}

		//Update the first and last name
		wp_update_user( array( 'ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name ) );

		//Handle Referral
		$referrer_id = null;
		if(!empty($referral_code)){
			$referrer = $this->get_member_by_referral_code($referral_code);
			if($referrer){
				$referrer_id = $referrer->user_id;
				$this->save_referral($referrer_id, $user_id);
			}
		}


		//Handle Payment
		if($payment_method === 'voucher'){
			if(empty($voucher_code)){
				$this->display_message('error', 'Please enter the voucher code');
				return;
			}
			$voucher = $this->get_voucher_by_code($voucher_code);
			if(!$voucher || $voucher->status !== 'active' ||  $voucher->amount != $registration_fee){
				$this->display_message('error', 'Invalid Voucher Code.');
				return;
			}
			$this->mark_voucher_used($voucher->id);
			$this->save_member($user_id, $referral_code, $referrer_id, 1);
			$this->display_message('success', 'Registration successful.');

		}elseif($payment_method === 'woocommerce'){
			if( ! class_exists( 'WooCommerce' ) ) {
				$this->display_message('error', 'WooCommerce is not installed.');
				return;
			}
			$this->create_woocommerce_order($user_id, $registration_fee);
			$this->save_member($user_id, $referral_code, $referrer_id);
			$this->display_message('success', 'Registration successful, please complete the payment.');
		}
	}
	/**
	 * get_member_by_referral_code
	 *
	 * @param  mixed $referral_code
	 * @return void
	 */
	public function get_member_by_referral_code($referral_code){
		global $wpdb;
		$table_name = $wpdb->prefix . 'richvision_members';
		$query = $wpdb->prepare("SELECT user_id FROM $table_name WHERE referral_code = %s", $referral_code);

		return $wpdb->get_row($query);
	}
	/**
	 * save_referral
	 *
	 * @param  mixed $referrer_id
	 * @param  mixed $referred_id
	 * @return void
	 */
	public function save_referral($referrer_id, $referred_id){
		global $wpdb;
		$table_name = $wpdb->prefix . 'richvision_referrals';

		$wpdb->insert(
			$table_name,
			[
				'referrer_id' => $referrer_id,
				'referred_id' => $referred_id
			],
			['%d', '%d']
		);

	}
	/**
	 * save_member
	 *
	 * @param  mixed $user_id
	 * @param  mixed $referral_code
	 * @param  mixed $referrer_id
	 * @param  mixed $active
	 * @return void
	 */
	public function save_member($user_id, $referral_code, $referrer_id, $active = 0){
		global $wpdb;
		$table_name = $wpdb->prefix . 'richvision_members';
		$unique_referral_code =  $this->generate_unique_referral_code();

		$wpdb->insert(
			$table_name,
			[
				'user_id' => $user_id,
				'referral_code' => $unique_referral_code,
				'referrer_id' => $referrer_id,
				'active' => $active
			],
			['%d', '%s', '%d', '%d']
		);
	}
	/**
	 * generate_unique_referral_code
	 *
	 * @return void
	 */
	public function generate_unique_referral_code(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'richvision_members';
		$code_length = 8;
		do {
			$code =  substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($code_length/strlen($x)) )),1,$code_length);
			$code_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE referral_code = %s", $code));
		}while($code_exists);
		return $code;
	}
	/**
	 * create_woocommerce_order
	 *
	 * @param  mixed $user_id
	 * @param  mixed $registration_fee
	 * @return void
	 */
	public function create_woocommerce_order($user_id, $registration_fee){
		$order = wc_create_order();
		$order->add_product( wc_get_product(get_option('richvision_registration_product_id')), 1);
		$order->set_customer_id($user_id);
		$order->calculate_totals();
		$order->update_status('pending', 'Awaiting Payment for Richvision Cooperative Registration.', true);

	}
		/**
	 * get_voucher_by_code
	 *
	 * @param  mixed $voucher_code
	 * @return void
	 */
	public function get_voucher_by_code($voucher_code){
		global $wpdb;
		$table_name = $wpdb->prefix . 'richvision_vouchers';
		$query = $wpdb->prepare("SELECT * FROM $table_name WHERE code = %s", $voucher_code);

		return $wpdb->get_row($query);
	}
		/**
	 * mark_voucher_used
	 *
	 * @param  mixed $voucher_id
	 * @return void
	 */
	public function mark_voucher_used($voucher_id){
		global $wpdb;
		$table_name = $wpdb->prefix . 'richvision_vouchers';
		$wpdb->update(
			$table_name,
			['status' => 'used'],
			['id' => $voucher_id],
			['%s'],
			['%d']
		);
	}
	/**
	 * display_message
	 *
	 * @param  mixed $type
	 * @param  mixed $message
	 * @return void
	 */
	public function display_message($type, $message){
		?>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					let messageDiv = document.getElementById('registration-message');
					messageDiv.classList.add('<?php echo esc_attr($type)?>');
					messageDiv.innerHTML = '<?php echo esc_html($message)?>';
				});
			</script>
		<?php
	}
}
?>
