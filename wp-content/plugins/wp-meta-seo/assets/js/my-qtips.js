jQuery(document).ready(function ($) {
    jQuery('.label-dash-widgets').qtip({
        content: {
            attr: 'data-alt'
        },
        position: {
            my: 'bottom left',
            at: 'top center'
        },
        style: {
            tip: {
                corner: true
            },
            classes: 'wpms-widgets-qtip'
        },
        show: 'hover',
        hide: {
            fixed: true,
            delay: 10
        }
    });

    jQuery('.wpms_dash_widgets').qtip({
        content: {
            attr: 'data-alt'
        },
        position: {
            my: 'bottom center',
            at: 'top center'
        },
        style: {
            tip: {
                corner: true
            },
            classes: 'wpms-widgets-qtip'
        },
        show: 'hover',
        hide: {
            fixed: true,
            delay: 10
        }
    });
});