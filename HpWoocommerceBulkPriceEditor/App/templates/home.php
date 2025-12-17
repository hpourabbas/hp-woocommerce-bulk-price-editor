<style>
    <?php if ($rtl) { ?>
    #adminmenuwrap, #adminmenuback {
        right: 0;
    }

    html, body, #q-app {
        direction: rtl !important;
    }

    <?php } ?>
</style>
<script>
  window.HP = {
    i18n: JSON.parse('<?php echo json_encode($i18n); ?>'),
    baseURL: '<?php echo esc_url($home); ?>',
    rtl: '<?php echo $rtl; ?>'
  };
</script>
<div id=q-app></div>