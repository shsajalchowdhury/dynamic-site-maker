<?php
/**
 * Spam Protection Class
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Spam_Protection
 *
 * Handles spam protection by preventing multiple form submissions from the same user.
 */
class DSMK_Spam_Protection {

    /**
     * Cookie name
     *
     * @var string
     */
    private $cookie_name = 'dsmk_submission_status';

    /**
     * Cookie expiration in days
     *
     * @var int
     */
    private $cookie_expiration = 30;

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing needed in constructor for now
    }

    /**
     * Check if the user has already submitted the form
     *
     * @return bool True if already submitted, false otherwise.
     */
    public function has_submitted() {
        // Check for cookie
        if ( isset( $_COOKIE[ $this->cookie_name ] ) && $_COOKIE[ $this->cookie_name ] === 'submitted' ) {
            return true;
        }

        // Check for IP in database if enabled
        if ( $this->is_ip_tracking_enabled() ) {
            return $this->is_ip_in_database();
        }

        return false;
    }

    /**
     * Mark the user as having submitted the form
     */
    public function mark_as_submitted() {
        // Set cookie
        setcookie(
            $this->cookie_name,
            'submitted',
            time() + ( $this->cookie_expiration * DAY_IN_SECONDS ),
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true // HttpOnly flag
        );

        // Store IP in database if enabled
        if ( $this->is_ip_tracking_enabled() ) {
            $this->store_ip_in_database();
        }
    }

    /**
     * Check if IP tracking is enabled
     *
     * @return bool True if enabled, false otherwise.
     */
    private function is_ip_tracking_enabled() {
        return get_option( 'dsmk_enable_ip_tracking', false );
    }

    /**
     * Check if IP is in database
     *
     * @return bool True if IP is in database, false otherwise.
     */
    private function is_ip_in_database() {
        $ip_address = $this->get_user_ip();
        $submissions = get_option( 'dsmk_ip_submissions', array() );

        return isset( $submissions[ $ip_address ] );
    }

    /**
     * Store IP in database
     */
    private function store_ip_in_database() {
        $ip_address = $this->get_user_ip();
        $submissions = get_option( 'dsmk_ip_submissions', array() );

        // Add current IP with timestamp
        $submissions[ $ip_address ] = time();

        // Clean up old entries (older than 30 days)
        $this->cleanup_old_ip_entries( $submissions );

        update_option( 'dsmk_ip_submissions', $submissions );
    }

    /**
     * Clean up old IP entries
     *
     * @param array $submissions IP submissions array.
     * @return array Cleaned up submissions.
     */
    private function cleanup_old_ip_entries( $submissions ) {
        $thirty_days_ago = time() - ( 30 * DAY_IN_SECONDS );

        foreach ( $submissions as $ip => $timestamp ) {
            if ( $timestamp < $thirty_days_ago ) {
                unset( $submissions[ $ip ] );
            }
        }

        return $submissions;
    }

    /**
     * Get user IP address
     *
     * @return string The user's IP address.
     */
    private function get_user_ip() {
        $ip = '';

        // Check for CloudFlare IP
        if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            // Check for proxy
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            
            // HTTP_X_FORWARDED_FOR can contain multiple IPs, take the first one
            $ips = explode( ',', $ip );
            $ip = trim( $ips[0] );
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        return $ip;
    }

    /**
     * Reset submission status for testing purposes
     *
     * This method is only for admin use in testing environments.
     */
    public function reset_submission_status() {
        // Only allow admins to reset
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Clear cookie
        setcookie( $this->cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

        // Clear IP from database if IP tracking is enabled
        if ( $this->is_ip_tracking_enabled() ) {
            $ip_address = $this->get_user_ip();
            $submissions = get_option( 'dsmk_ip_submissions', array() );

            if ( isset( $submissions[ $ip_address ] ) ) {
                unset( $submissions[ $ip_address ] );
                update_option( 'dsmk_ip_submissions', $submissions );
            }
        }
    }
}
