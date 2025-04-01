<?php
/**
 * Dynamic Site Maker Logo Tag
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Logo_Tag
 *
 * Elementor dynamic tag for displaying the submitter's logo.
 */
class DSMK_Logo_Tag extends \ElementorPro\Modules\DynamicTags\Tags\Base\Tag {

    /**
     * Get tag name
     *
     * @return string The tag name.
     */
    public function get_name() {
        return 'dsmk-logo';
    }

    /**
     * Get tag title
     *
     * @return string The tag title.
     */
    public function get_title() {
        return __( 'User Logo', 'dynamic-site-maker' );
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
        return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
    }

    /**
     * Render the tag output
     */
    public function render() {
        global $post;
        
        if ( ! $post ) {
            return;
        }

        $logo_id = get_post_meta( $post->ID, '_dsmk_logo_id', true );
        
        if ( empty( $logo_id ) ) {
            return;
        }

        $image_data = array(
            'id'  => $logo_id,
            'url' => wp_get_attachment_url( $logo_id ),
        );

        echo wp_json_encode( $image_data );
    }
}
