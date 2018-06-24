<?php


class WS_CDN_Url {

    public function __construct() {

        add_action('admin_init', array($this, 'cdn_field_setup'));
        add_filter('wp_get_attachment_url', array($this, 'rewrite_cdn_url') );

    }

    /**
     * Admin setup registers additional settings on the global options page for us.
     *
     * TODO: Need to update the `register_setting` function to take an array in the third parameter – once we're able to update to 4.7.3
     * That API is not available in 4.6.3
     */
    public function cdn_field_setup() {
        register_setting(
            'general',
            'cdn_url'
        );

        add_settings_field(
            'cdn_url',
            'CDN Address (URL)',
            array( $this, 'render_settings_field' ),
            'general',
            'default',
            array( 'cdn_url', get_option('cdn_url') )
        );
    }

    /**
     * Callback function to render the CDN URL field in the options.
     *
     * @param $args array the array of value arguments
     *
     */
    public function render_settings_field( $args ) {
        echo "<input aria-describedby='cdn-description' name='cdn_url' class='regular-text code' type='text' id='" . $args[0] . "' value='" . $args[1] . "'/>";
        echo "<p id='cdn-description' class='description'>Input the url of the CDN, starting with https://, to use with this site or leave this field blank to bypass the CDN.";
    }

    /**
     * Rewrite attachment URL from the base CMS form to the desired CDN form.
     *
     * @filter 'wp_get_attachment_url'
     * @param $original string the original attachment URL
     * @return String the updated CDN url.
     */
    public function rewrite_cdn_url( $original ) {

        $trailing_string = '/wp-content/uploads/';
        $cms_url =  get_option( 'siteurl' );
        $cdn_url = get_option('cdn_url');


        if ( ! empty( $cdn_url ) ) {

            return str_replace( $cms_url . $trailing_string, $cdn_url . '/', $original );

        } else {

            return $original;

        }

    }

}

?>
