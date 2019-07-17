(function ($) {
    $(document).ready(function(){
        $("ul.wpmstabs .tab a").on('click', function(e) {
            $(this).unbind('click').trigger('click');
        });
    });
}( jQuery ));
