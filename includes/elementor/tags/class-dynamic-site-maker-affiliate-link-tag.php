<?php
/**
 * Dynamic Site Maker Affiliate Link Tag
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Affiliate_Link_Tag
 *
 * Elementor dynamic tag for displaying the submitter's affiliate link.
 */
class DSMK_Affiliate_Link_Tag extends \ElementorPro\Modules\DynamicTags\Tags\Base\Tag {

    /**
     * Get tag name
     *
     * @return string The tag name.
     */
    public function get_name() {
        return 'dsmk-affiliate-link';
    }

    /**
     * Get tag title
     *
     * @return string The tag title.
     */
    public function get_title() {
        return __( 'Affiliate Link', 'dynamic-site-maker' );
    }

    /**
     * Get tag groups
     *
     * @return array The tag groups.
     */
    public function get_group() {
        return array( 'dsmk-tags' );
    }

    /**
     * Get tag categories
     *
     * @return array The tag categories.
     */
    public function get_categories() {
        return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
    }

    /**
     * Render the tag output
     */
    public function render() {
        global $post;
        
        if ( ! $post ) {
            return;
        }

        $affiliate_link = get_post_meta( $post->ID, '_dsmk_affiliate_link', true );
        
        if ( empty( $affiliate_link ) ) {
            return;
        }

        echo esc_url( $affiliate_link );
    }
}
