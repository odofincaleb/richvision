<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
class RichVision_Cooperative_Activator{

	public static function activate(){
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Create the table for members
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            referral_code VARCHAR(255) UNIQUE,
            referrer_id BIGINT(20) UNSIGNED,
            date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            active TINYINT(1) DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID),
            FOREIGN KEY (referrer_id) REFERENCES {$wpdb->prefix}users(ID)
            ) $charset_collate;";
        $wpdb->query($sql);
        
        // Create the table for referrals
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_referrals (
          id INT AUTO_INCREMENT PRIMARY KEY,
          referrer_id BIGINT(20) UNSIGNED NOT NULL,
          referred_id BIGINT(20) UNSIGNED NOT NULL,
          date_referred TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (referrer_id) REFERENCES {$wpdb->prefix}users(ID),
          FOREIGN KEY (referred_id) REFERENCES {$wpdb->prefix}users(ID)
      ) $charset_collate;";
        $wpdb->query($sql);

        // Create the table for savings
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_savings (
          id INT AUTO_INCREMENT PRIMARY KEY,
          member_id BIGINT(20) UNSIGNED NOT NULL,
          savings_package VARCHAR(255) NOT NULL,
          amount DECIMAL(10, 2) NOT NULL,
          savings_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}users(ID)
        ) $charset_collate;";
        $wpdb->query($sql);

         // Create the table for wallet
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_wallets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            wallet_type VARCHAR(50) NOT NULL,
            balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}users(ID)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Create the table for commissions
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_commissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            level INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            date_earned TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(20) DEFAULT 'pending',
            FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}users(ID)
        ) $charset_collate;";
        $wpdb->query($sql);

         // Create the table for loans
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_loans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            loan_amount DECIMAL(10, 2) NOT NULL,
            application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approval_date TIMESTAMP NULL,
            status VARCHAR(20) DEFAULT 'pending',
            FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}users(ID)
        ) $charset_collate;";
        $wpdb->query($sql);

         // Create the table for loan repayments
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_loan_repayments (
          id INT AUTO_INCREMENT PRIMARY KEY,
          loan_id BIGINT(20) UNSIGNED NOT NULL,
          repayment_amount DECIMAL(10, 2) NOT NULL,
          repayment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          status VARCHAR(20) DEFAULT 'pending',
          FOREIGN KEY (loan_id) REFERENCES {$wpdb->prefix}richvision_loans(ID)
        ) $charset_collate;";
      $wpdb->query($sql);

        // Create the table for rankings
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_rankings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            rank_name VARCHAR(50) NOT NULL,
            date_achieved TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}users(ID)
        ) $charset_collate;";
        $wpdb->query($sql);

          // Create the table for admin vouchers
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_vouchers (
          id INT AUTO_INCREMENT PRIMARY KEY,
          code VARCHAR(255) UNIQUE NOT NULL,
          amount DECIMAL(10, 2) NOT NULL,
          created_by BIGINT(20) UNSIGNED NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          status VARCHAR(20) DEFAULT 'active',
            FOREIGN KEY (created_by) REFERENCES {$wpdb->prefix}users(ID)
          ) $charset_collate;";
        $wpdb->query($sql);


        // Create the table for member meta
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}richvision_member_meta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value LONGTEXT,
            FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}users(ID)
          ) $charset_collate;";
      $wpdb->query($sql);
	}
}
?>
