<?php
/*
Plugin Name: RichVision Cooperative
Description: A plugin to manage RichVision Cooperative functionalities, including registration, savings, wallet, MLM, loans, and rankings.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Activation Hook
function richvision_activate() {
    global $wpdb;
    
    // Create savings table
    $table_name = $wpdb->prefix . 'richvision_savings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        savings_package VARCHAR(20) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        date_created DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'richvision_activate');

// Shortcode for Savings Form
function richvision_savings_form() {
    ob_start();
    ?>
    <form id="richvision-savings-form" method="post">
        <h2>Select Your Savings Package</h2>
        <p>
            <label for="savings-package">Choose a Package:</label>
            <select name="savings_package" id="savings-package" required>
                <option value="alpha">Alpha - ₦5000</option>
                <option value="bravo">Bravo - ₦10000</option>
                <option value="delta">Delta - ₦20000</option>
                <option value="gold">Gold - ₦50000</option>
                <option value="platinum">Platinum - ₦100000</option>
            </select>
        </p>
        <p>
            <button type="submit" class="btn-submit">Save Package</button>
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('richvision_savings', 'richvision_savings_form');

// Handle Savings Form Submission
function richvision_handle_savings_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['savings_package'])) {
        global $wpdb;

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_die('You must be logged in to select a savings package.');
        }

        $savings_package = sanitize_text_field($_POST['savings_package']);
        $package_amounts = [
            'alpha' => 5000,
            'bravo' => 10000,
            'delta' => 20000,
            'gold' => 50000,
            'platinum' => 100000,
        ];

        if (!array_key_exists($savings_package, $package_amounts)) {
            wp_die('Invalid package selection.');
        }

        $amount = $package_amounts[$savings_package];

        $table_name = $wpdb->prefix . 'richvision_savings';
        $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'savings_package' => $savings_package,
                'amount' => $amount,
                'date_created' => current_time('mysql'),
            ]
        );

        wp_redirect(add_query_arg('success', '1', wp_get_referer()));
        exit;
    }
}
add_action('init', 'richvision_handle_savings_submission');

// Add CSS for Savings Form
function richvision_savings_styles() {
    ?>
    <style>
        #richvision-savings-form {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        #richvision-savings-form h2 {
            text-align: center;
            color: #333;
        }
        #richvision-savings-form label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        #richvision-savings-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn-submit {
            display: block;
            width: 100%;
            padding: 10px;
            background: #0073aa;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .btn-submit:hover {
            background: #005f8c;
        }
    </style>
    <?php
}
add_action('wp_head', 'richvision_savings_styles');
