<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Modified by Joomunited
 */

/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$domaindata = WpmsGaTools::getRootDomain(esc_html(get_option('siteurl')));
?>
<script type="text/javascript">
    (function ($) {
        $(window).load(function () {
            <?php if ($this->ga_tracking['wpmsga_event_tracking']) { ?>

            //Track Downloads
            $('a').filter(function () {
                return this.href.match(/.*\.(<?php echo esc_js($this->ga_tracking['wpmsga_event_downloads']);?>)(\?.*)?$/);
            }).click(function () {
                ga('send', 'event', 'download', 'click', this.href<?php if (isset($this->ga_tracking['wpmsga_event_bouncerate']) && $this->ga_tracking['wpmsga_event_bouncerate']) {
                    echo ", {'nonInteraction': 1}";
                }?>);
            });

            //Track Mailto
            $('a[href^="mailto"]').click(function () {
                ga('send', 'event', 'email', 'send', this.href<?php if (isset($this->ga_tracking['wpmsga_event_bouncerate']) && $this->ga_tracking['wpmsga_event_bouncerate']) {
                    echo ", {'nonInteraction': 1}";
                }?>);
            });
            <?php if (isset ($domaindata ['domain']) && $domaindata ['domain']) { ?>

            //Track Outbound Links
            $('a[href^="http"]').filter(function () {
                if (!this.href.match(/.*\.(<?php echo esc_js($this->ga_tracking['wpmsga_event_downloads']);?>)(\?.*)?$/)) {
                    if (this.href.indexOf('<?php echo $domaindata['domain']; ?>') === -1) return this.href;
                }
            }).click(function () {
                ga('send', 'event', 'outbound', 'click', this.href<?php if (isset($this->ga_tracking['wpmsga_event_bouncerate']) && $this->ga_tracking['wpmsga_event_bouncerate']) {
                    echo ", {'nonInteraction': 1}";
                }?>);
            });
            <?php } ?>
            <?php } ?>
            <?php if ($this->ga_tracking['wpmsga_event_affiliates'] && $this->ga_tracking['wpmsga_aff_tracking']){ ?>

            //Track Affiliates
            $('a').filter(function () {
                if ('<?php echo esc_js($this->ga_tracking['wpmsga_event_affiliates']);?>' !== '') {
                    return this.href.match(/(<?php echo str_replace('/', '\/', (esc_js($this->ga_tracking['wpmsga_event_affiliates'])));?>)/);
                }
            }).click(function () {
                ga('send', 'event', 'affiliates', 'click', this.href<?php if (isset($this->ga_tracking['wpmsga_event_bouncerate']) && $this->ga_tracking['wpmsga_event_bouncerate']) {
                    echo ", {'nonInteraction': 1}";
                }?>);
            });
            <?php } ?>
            <?php if (isset ($domaindata ['domain']) && $domaindata ['domain'] && $this->ga_tracking ['wpmsga_hash_tracking']) { ?>

            //Track Hashmarks
            $('a').filter(function () {
                if (this.href.indexOf('<?php echo $domaindata['domain']; ?>') !== -1 || this.href.indexOf('://') === -1) return this.hash;
            }).click(function () {
                ga('send', 'event', 'hashmark', 'click', this.href<?php if (isset($this->ga_tracking['wpmsga_event_bouncerate']) && $this->ga_tracking['wpmsga_event_bouncerate']) {
                    echo ", {'nonInteraction': 1}";
                }?>);
            });

            <?php } ?>
        });
    })(jQuery);
</script>
