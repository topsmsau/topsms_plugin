<?php

/**
 * Provide an admin setup view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://eux.com.au
 * @since      1.0.0
 *
 * @package    Topsms
 * @subpackage Topsms/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap topsms-setup-wrapper">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <!-- Setup steps -->
    <div class="topsms-setup-steps">
        <ul class="wc-setup-steps">
            <?php foreach ( $setup_steps as $step_key => $step ) : ?>
                <li class="<?php echo $step_key === $current_step ? 'active' : ( array_search( $current_step, array_keys( $setup_steps ) ) > array_search( $step_key, array_keys( $setup_steps ) ) ? 'done' : '' ); ?>">
                    <?php echo esc_html( $step['name'] ); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Setup steps content -->
    <div class="topsms-setup-content">
        <?php
        // Include the current step template
        $step_file = plugin_dir_path( dirname( __FILE__ ) ) . 'partials/setup-steps/' . $current_step . '.php';
        error_log("step file:" . print_r($step_file, true));
        if ( file_exists( $step_file ) ) {
            include $step_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Step template not found.', 'topsms' ) . '</p></div>';
        }
        ?>
    </div>
</div>

