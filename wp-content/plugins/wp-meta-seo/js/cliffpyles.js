jQuery(document).ready(function ($) {
    var $pc = $('#progressController');
    var $pCaption = $('.metaseo-progress-bar p');
    var iProgress = document.getElementById('inactiveProgress');
    if (iProgress === null) {
        return;
    }
    var aProgress = document.getElementById('activeProgress');
    var iProgressCTX = iProgress.getContext('2d');
    var mcheck = 0;
    if (typeof wpmscliffpyles.use_validate !== "undefined" && parseInt(wpmscliffpyles.use_validate) === 1) {
        wpms_validate_analysis();
    }

    function reload_analysis() {
        $('.wpmseotab .spinner').css({'visibility': ' inherit'}).show();
        $('.metaseo_right').html('');
        var title = $('#title').val();
        var meta_title = $('#metaseo_wpmseo_title').val();
        var mpageurl = $('#editable-post-name-full').text();
        var meta_desc = $('#metaseo_wpmseo_desc').val();
        var mcontent = '';

        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content') !== null) {
            mcontent = tinyMCE.editors.content.getContent();
        } else {
            mcontent = $('#content').val();
        }

        var data = {'post_id': jQuery('.metaseo-progress-bar').data('post_id'), 'title': title, 'meta_title': meta_title, 'mpageurl': mpageurl, 'meta_desc': meta_desc, 'content': mcontent};
        $.ajax({
            dataType: 'json',
            method: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpms',
                'task': 'reload_analysis',
                'datas': data
            },
            success: function (res) {
                if (res) {
                    $('.wpmseotab .spinner').hide();
                    $('.metaseo_right').html(res.output);
                    mcheck = res.check;
                    $('#progressController').val(res.circliful).change();
                    jQuery('.metaseo_tool').qtip({
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
                            classes: 'metaseo-qtip qtip-rounded'
                        },
                        show: 'hover',
                        hide: {
                            fixed: true,
                            delay: 100
                        }

                    });
                }
            }
        });

        drawInactive(iProgressCTX);
    }

    // init load analysis
    reload_analysis();
    // reload analysis
    $('#reload_analysis').on('click', function () {
        reload_analysis();
    });

    drawInactive(iProgressCTX);
    $pc.on('change', function () {
        var percentage = $(this).val() / 100;
        drawProgress(aProgress, percentage, $pCaption);
    });

    function drawInactive(iProgressCTX) {
        iProgressCTX.lineCap = 'square';

        //outer ring
        iProgressCTX.beginPath();
        iProgressCTX.lineWidth = 15;
        iProgressCTX.strokeStyle = '#e1e1e1';
        iProgressCTX.arc(137.5, 137.5, 129, 0, 2 * Math.PI);
        iProgressCTX.stroke();

        //progress bar
        iProgressCTX.beginPath();
        iProgressCTX.lineWidth = 0;
        iProgressCTX.fillStyle = '#e6e6e6';
        iProgressCTX.arc(137.5, 137.5, 121, 0, 2 * Math.PI);
        iProgressCTX.fill();

        //progressbar caption
        iProgressCTX.beginPath();
        iProgressCTX.lineWidth = 0;
        iProgressCTX.fillStyle = '#fff';
        iProgressCTX.arc(137.5, 137.5, 100, 0, 2 * Math.PI);
        iProgressCTX.fill();

    }
    function drawProgress(bar, percentage, $pCaption) {
        var barCTX = bar.getContext("2d");
        var quarterTurn = Math.PI / 2;
        var endingAngle = ((2 * percentage) * Math.PI) - quarterTurn;
        var startingAngle = 0 - quarterTurn;

        bar.width = bar.width;
        barCTX.lineCap = 'square';

        barCTX.beginPath();
        barCTX.lineWidth = 20;
        barCTX.strokeStyle = '#76e1e5';
        barCTX.arc(137.5, 137.5, 111, startingAngle, endingAngle);
        barCTX.stroke();

        $pCaption.text((parseInt(percentage * 100, 10)) + '%');
    }

    var percentage = $pc.val() / 100;
    drawProgress(aProgress, percentage, $pCaption);

    function wpms_validate_analysis() {
        jQuery(document).on('click', '.metaseo-dashicons.icons-mwarning', function () {
            var $this = $(this);
            jQuery(this).removeClass('icons-mwarning').addClass('dashicons-yes');
            if (mcheck == 0) {
                mcheck = jQuery('#metaseo_alanysis_ok').val();
                mcheck++;
            } else {
                mcheck++;
            }
            var circliful = Math.ceil((mcheck * 100) / 7);
            jQuery.ajax({
                dataType: 'json',
                method: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'wpms',
                    'task': 'validate_analysis',
                    'post_id': jQuery('.metaseo-progress-bar').data('post_id'),
                    'field': $this.parent('.metaseo_analysis').data('title')
                },
                success: function (res) {
                    if (res != false) {
                        $('#progressController').val(circliful).change();
                    }
                }
            });

        });
    }


});