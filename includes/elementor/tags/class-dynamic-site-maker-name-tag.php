<?php
/**
 * Dynamic Site Maker Name Tag
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Name_Tag
 *
 * Elementor dynamic tag for displaying the submitter's name.
 */
class DSMK_Name_Tag extends \ElementorPro\Modules\DynamicTags\Tags\Base\Tag {

    /**
     * Get tag name
     *
     * @return string The tag name.
     */
    public function get_name() {
        return 'dsmk-name';
    }

    /**
     * Get tag title
     *
     * @return string The tag title.
     */
    public function get_title() {
        return __( 'User Name', 'dynamic-site-maker' );
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
        return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
    }

    /**
     * Render the tag output
     */
    public function render() {
        global $post;
        
        if ( ! $post ) {
            return;
        }

        $name = get_post_meta( $post->ID, '_dsmk_name', true );
        
        if ( empty( $name ) ) {
            return;
        }

        echo esc_html( $name );
    }
}
