<?php
$geral_screen = (!isset($_GET['action']) || isset($_GET['action']) && 'geral' == $_GET['action']) ? true : false;
$styles_screen = (isset($_GET['action']) && 'styles' == $_GET['action']) ? true : false;
$shortcode_screen = (isset($_GET['action']) && 'shortcode' == $_GET['action']) ? true : false;
?>
<div class="wrap">

    <h1><?php _e('APG Map Pins', 'apgmappins'); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url(add_query_arg(array('action' => 'geral'), admin_url('options-general.php?page=apgmappins'))); ?>" class="nav-tab<?php if ($geral_screen) echo ' nav-tab-active'; ?>"><?php esc_html_e('Geral', 'apgmappins'); ?></a>
        <a href="<?php echo esc_url(add_query_arg(array('action' => 'styles'), admin_url('options-general.php?page=apgmappins'))); ?>" class="nav-tab<?php if ($styles_screen) echo ' nav-tab-active'; ?>"><?php esc_html_e('Estilos', 'apgmappins'); ?></a>
        <a href="<?php echo esc_url(add_query_arg(array('action' => 'shortcode'), admin_url('options-general.php?page=apgmappins'))); ?>" class="nav-tab<?php if ($shortcode_screen) echo ' nav-tab-active'; ?>"><?php esc_html_e('Shortcode', 'apgmappins'); ?></a>
    </h2>

    <form method="post" action="options.php">

        <?php
        if ($geral_screen) {
            settings_fields('apgmappins_geral');
            do_settings_sections('apgmappins-settings-geral');
        }

        if ($styles_screen) {
            settings_fields('apgmappins_styles');
            do_settings_sections('apgmappins-settings-styles');
        }

        if ($shortcode_screen) {
            settings_fields('apgmappins_shortcode');
            do_settings_sections('apgmappins-settings-shortcode');
        }

        submit_button(__('Salvar configurações', 'apgmappins'));
        ?>

    </form>

</div>