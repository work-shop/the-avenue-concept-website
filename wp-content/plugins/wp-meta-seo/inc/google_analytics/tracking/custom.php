<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<script type="text/javascript">
    <?php
    if (!empty($this->ga_tracking['wpmsga_code_tracking'])) {
        echo $this->ga_tracking['wpmsga_code_tracking'];
    }

    ?>
</script>