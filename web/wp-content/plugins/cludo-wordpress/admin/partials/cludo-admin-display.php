<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin options page.
 *
 * @var Cludo_Wordpress_Admin $this
 */
$nav      = '';
$tab_data = '';

$section_titles = $this->settings_tab_titles();
?>
<div class="cludo">
    <div class="cludo__brand">
        <a href="https://cludo.com" class="cludo__logo">
            <img src="<?php echo plugin_dir_url( __DIR__ ); ?>assets/img/cludo-logo.png" alt="Cludo">
        </a>
    </div>
	<?php cludo_api_connected_message($this->api); ?>
	<?php foreach ( $this->get_settings_sections() as $section => $fields ) : ?>
		<?php
		// Create the navigation item.
		$tab_id = 'tab-' . $section;
		$nav    .= '<a href="#' . $tab_id . '" class="nav-tab">' . $section_titles[$section] . '</a>';

		// Add the tab data.
		ob_start(); ?>
        <div id="<?php echo $tab_id; ?>" class="cludo__option_page_tab">
			<?php foreach ( $fields as $key => $field_data ) : ?>
				<?php $field = new CludoSettingsField( $section, $key, $field_data, $this->settings ); ?>
				<?php echo $field->render(); ?>
			<?php endforeach; ?>
        </div>
		<?php
		$tab_data .= ob_get_clean();
	endforeach; ?>
    <div class="cludo__option_page">
        <form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
            <div style="visibility: hidden;margin-left: 0;margin-right:0;" id="cludo__option_page_notice" class="warning notice notice-warning">
                <p><?php _e('Settings have changed.', CLUDO_WP_PLUGIN_NAME); ?></p>
            </div>
            <nav class="nav-tab-wrapper woo-nav-tab-wrapper cludo__option_page_tabs">
				<?php echo $nav; ?>
            </nav>
			<?php settings_fields( CLUDO_WP_PLUGIN_NAME ); ?>
			<?php do_settings_sections( CLUDO_WP_PLUGIN_NAME ); ?>
			<?php echo $tab_data; ?>
			<?php submit_button(); ?>
        </form>
    </div>
</div>