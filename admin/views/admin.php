<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <form method="POST" action="options.php">
        <?php settings_fields('pricemesh-settings-group');	//pass slug name of page, also referred
        //to in Settings API as option group name
        do_settings_sections( $this->plugin_slug ); 	//pass slug name of page
        submit_button();
        ?>
    </form>

</div>