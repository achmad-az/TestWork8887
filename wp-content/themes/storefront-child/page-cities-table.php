<?php
/**
 * Template Name: Cities Table
 */

get_header(); 

?>

<div class="cities-table-container">
    <h1><?php _e('Countries, Cities & Temperatures', 'textdomain'); ?></h1>

    <?php do_action('before_cities_table'); ?>

    <table id="cities-table">
        <thead>
            <tr>
                <th><?php _e('Country', 'textdomain'); ?></th>
                <th><?php _e('City', 'textdomain'); ?></th>
                <th><?php _e('Temperature', 'textdomain'); ?></th>
            </tr>
        </thead>
        <tbody>
            <!-- Results will be loaded here by AJAX -->
        </tbody>
    </table>
    
    <?php do_action('after_cities_table'); ?>
</div>

<?php get_footer(); ?>