jQuery(document).ready(function($){
     $('.wpmsclose_notification').click(function(){
         var $this = $(this);
         var page = $this.data('page');
         $.ajax({
             url: ajaxurl,
             method: 'POST',
             dataType: 'json',
             data:{
                 action: 'wpms',
                 task: 'setcookie_notification',
                 page: page,
                 wpms_nonce: wpms_localize.wpms_nonce
             },
             success: function(res){
                if(res){
                    $this.closest('.wpms_wrap_notification').remove();
                }
             }
         });
     });
});