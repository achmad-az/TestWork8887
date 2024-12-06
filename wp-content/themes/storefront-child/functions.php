<?php
/**
 * Enqueue parent and child theme styles.
 */
function storefront_child_enqueue_styles() {
    wp_enqueue_style('storefront-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('storefront-child-style', get_stylesheet_directory_uri() . '/style.css', array('storefront-parent-style'));
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_styles');

/**
 * Enqueue AJAX scripts on Cities Table page template.
 */
function enqueue_cities_ajax_script() {
    if (is_page_template('page-cities-table.php')) {
        wp_enqueue_script('cities-ajax', get_stylesheet_directory_uri() . '/js/cities-ajax.js', array('jquery'), null, true);
        wp_localize_script('cities-ajax', 'citiesAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cities_search_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_cities_ajax_script');

/**
 * Register 'Cities' Custom Post Type.
 */
function cpt_cities_init() {
    $labels = array(
        'name'               => __('Cities', 'textdomain'),
        'singular_name'      => __('City', 'textdomain'),
        'add_new'            => __('Add New City', 'textdomain'),
        'add_new_item'       => __('Add New City', 'textdomain'),
        'edit_item'          => __('Edit City', 'textdomain'),
        'new_item'           => __('New City', 'textdomain'),
        'view_item'          => __('View City', 'textdomain'),
        'search_items'       => __('Search Cities', 'textdomain'),
        'not_found'          => __('No cities found', 'textdomain'),
        'not_found_in_trash' => __('No cities found in Trash', 'textdomain'),
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'supports'           => array('title', 'editor', 'thumbnail'),
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'cities'),
        'show_in_rest'       => true,
    );
    register_post_type('city', $args);
}
add_action('init', 'cpt_cities_init');

/**
 * Register 'Countries' Taxonomy for 'Cities'.
 */
function taxonomy_countries_init() {
    $labels = array(
        'name'              => __('Countries', 'textdomain'),
        'singular_name'     => __('Country', 'textdomain'),
        'search_items'      => __('Search Countries', 'textdomain'),
        'all_items'         => __('All Countries', 'textdomain'),
        'parent_item'       => __('Parent Country', 'textdomain'),
        'parent_item_colon' => __('Parent Country:', 'textdomain'),
        'edit_item'         => __('Edit Country', 'textdomain'),
        'update_item'       => __('Update Country', 'textdomain'),
        'add_new_item'      => __('Add New Country', 'textdomain'),
        'new_item_name'     => __('New Country Name', 'textdomain'),
        'menu_name'         => __('Countries', 'textdomain'),
    );
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'countries'),
    );
    register_taxonomy('country', 'city', $args);
}
add_action('init', 'taxonomy_countries_init');

/**
 * Add meta boxes for City Coordinates.
 */
function add_city_meta_boxes() {
    add_meta_box(
        'city_coordinates',
        __('City Coordinates', 'textdomain'),
        'render_city_coordinates_meta_box',
        'city',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_city_meta_boxes');

/**
 * Render City Coordinates meta box.
 *
 * @param WP_Post $post The post object.
 */
function render_city_coordinates_meta_box($post) {
    wp_nonce_field('save_city_coordinates_meta_box', 'city_coordinates_nonce');
    $latitude = get_post_meta($post->ID, '_city_latitude', true);
    $longitude = get_post_meta($post->ID, '_city_longitude', true);
    ?>
    <p>
        <label for="city_latitude"><?php _e('Latitude:', 'textdomain'); ?></label>
        <input type="text" name="city_latitude" id="city_latitude" value="<?php echo esc_attr($latitude); ?>" />
    </p>
    <p>
        <label for="city_longitude"><?php _e('Longitude:', 'textdomain'); ?></label>
        <input type="text" name="city_longitude" id="city_longitude" value="<?php echo esc_attr($longitude); ?>" />
    </p>
    <?php
}

/**
 * Save City Coordinates meta box data.
 *
 * @param int $post_id The post ID.
 */
function save_city_coordinates_meta_box($post_id) {
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    // Check nonce
    if (!isset($_POST['city_coordinates_nonce']) || !wp_verify_nonce($_POST['city_coordinates_nonce'], 'save_city_coordinates_meta_box')) return;
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['city_latitude'])) {
        update_post_meta($post_id, '_city_latitude', sanitize_text_field($_POST['city_latitude']));
    }
    if (isset($_POST['city_longitude'])) {
        update_post_meta($post_id, '_city_longitude', sanitize_text_field($_POST['city_longitude']));
    }
}
add_action('save_post_city', 'save_city_coordinates_meta_box');

/**
 * Add OpenWeatherMap settings page.
 */
function openweathermap_settings_page() {
    add_options_page(
        'OpenWeatherMap Settings',
        'OpenWeatherMap',
        'manage_options',
        'openweathermap-settings',
        'openweathermap_settings_page_html'
    );
}
add_action('admin_menu', 'openweathermap_settings_page');

/**
 * Render OpenWeatherMap settings page.
 */
function openweathermap_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['submit'])) {
        check_admin_referer('openweathermap_settings');
        $new_api_key = sanitize_text_field($_POST['openweathermap_api_key']);
        if ($new_api_key !== '************') { // Only update if the input is not masked
            update_option('openweathermap_api_key', $new_api_key);
        }
        echo '<div class="updated"><p>' . esc_html__('Settings saved', 'textdomain') . '</p></div>';
    }

    $api_key = get_option('openweathermap_api_key');
    $masked_api_key = $api_key ? str_repeat('*', strlen($api_key) - 4) . substr($api_key, -4) : '';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('OpenWeatherMap Settings', 'textdomain'); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field('openweathermap_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('API Key', 'textdomain'); ?></th>
                    <td><input type="text" name="openweathermap_api_key" value="<?php echo esc_attr($masked_api_key); ?>" style="width: 300px;" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * City Weather Widget class.
 */
class City_Weather_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'city_weather_widget',
            __('City Weather Widget', 'textdomain'),
            array('description' => __('Displays a city and its current temperature', 'textdomain'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];

        $selected_city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        if ($selected_city_id) {
            $city_name = get_the_title($selected_city_id);
            $latitude = get_post_meta($selected_city_id, '_city_latitude', true);
            $longitude = get_post_meta($selected_city_id, '_city_longitude', true);

            if ($latitude && $longitude) {
                $api_key = get_option('openweathermap_api_key');
                if (!$api_key) {
                    echo esc_html__('API key not set.', 'textdomain');
                    return;
                }
                $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&units=metric&appid={$api_key}";
                $response = wp_remote_get($url);

                if (is_wp_error($response)) {
                    echo esc_html__('Unable to retrieve weather data.', 'textdomain');
                } else {
                    $body = wp_remote_retrieve_body($response);
                    $data = json_decode($body);
                    if (isset($data->main->temp)) {
                        $temp = $data->main->temp;
                        echo '<h3>' . esc_html($city_name) . ': ' . esc_html($temp) . ' °C</h3>';
                    } else {
                        echo esc_html__('Weather data not available.', 'textdomain');
                    }
                }
            } else {
                echo esc_html__('City coordinates not set.', 'textdomain');
            }
        } else {
            echo esc_html__('No city selected.', 'textdomain');
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $selected_city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        $cities = get_posts(array('post_type' => 'city', 'numberposts' => -1));
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('city_id'); ?>"><?php esc_html_e('Select City:', 'textdomain'); ?></label>
            <select id="<?php echo $this->get_field_id('city_id'); ?>" name="<?php echo $this->get_field_name('city_id'); ?>">
                <option value=""><?php esc_html_e('--Select City--', 'textdomain'); ?></option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?php echo esc_attr($city->ID); ?>" <?php selected($selected_city_id, $city->ID); ?>>
                        <?php echo esc_html($city->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['city_id'] = !empty($new_instance['city_id']) ? sanitize_text_field($new_instance['city_id']) : '';
        return $instance;
    }
}

/**
 * Register City Weather Widget.
 */
function register_city_weather_widget() {
    register_widget('City_Weather_Widget');
}
add_action('widgets_init', 'register_city_weather_widget');

/**
 * Add search and pagination hooks for Cities Table.
 */
add_action('before_cities_table', function() {
    echo '<div class="cities-search" style="margin-bottom: 20px;">
        <input type="text" id="cities-search-input" placeholder="' . esc_attr__('Search cities...', 'textdomain') . '" />
        <button id="cities-search-button">' . esc_html__('Search', 'textdomain') . '</button>
    </div>';
});

add_action('after_cities_table', function() {
    echo '<div id="cities-pagination" style="margin-bottom: 20px;"></div>';
});

/**
 * Get cities table data.
 *
 * @param string $search Search term.
 * @param int $page Current page number.
 * @param int $per_page Number of items per page.
 * @return array List of cities.
 */
function get_cities_table_data($search = '', $page = 1, $per_page = 20) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $offset = ($page - 1) * $per_page;

    $query = "
    SELECT p.ID, p.post_title AS city_name, tm.name AS country_name, pm_lat.meta_value AS latitude, pm_lon.meta_value AS longitude
    FROM {$prefix}posts p
    INNER JOIN {$prefix}term_relationships tr ON (p.ID = tr.object_id)
    INNER JOIN {$prefix}term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
    INNER JOIN {$prefix}terms tm ON (tt.term_id = tm.term_id)
    LEFT JOIN {$prefix}postmeta pm_lat ON (p.ID = pm_lat.post_id AND pm_lat.meta_key = '_city_latitude')
    LEFT JOIN {$prefix}postmeta pm_lon ON (p.ID = pm_lon.post_id AND pm_lon.meta_key = '_city_longitude')
    WHERE p.post_type = 'city' AND p.post_status = 'publish' AND tt.taxonomy = 'country'
    ";

    if (!empty($search)) {
        $search = '%' . $wpdb->esc_like($search) . '%';
        $query .= $wpdb->prepare(" AND p.post_title LIKE %s", $search);
    }

    $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, $offset);

    return $wpdb->get_results($query);
}

/**
 * Get total count of cities.
 *
 * @param string $search Search term.
 * @return int Total count of cities.
 */
function get_cities_total_count($search = '') {
    global $wpdb;
    $prefix = $wpdb->prefix;

    $query = "
    SELECT COUNT(*)
    FROM {$prefix}posts p
    INNER JOIN {$prefix}term_relationships tr ON (p.ID = tr.object_id)
    INNER JOIN {$prefix}term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
    INNER JOIN {$prefix}terms tm ON (tt.term_id = tm.term_id)
    WHERE p.post_type = 'city' AND p.post_status = 'publish' AND tt.taxonomy = 'country'
    ";

    if (!empty($search)) {
        $search = '%' . $wpdb->esc_like($search) . '%';
        $query .= $wpdb->prepare(" AND p.post_title LIKE %s", $search);
    }

    return $wpdb->get_var($query);
}

/**
 * Handle AJAX request to load cities.
 */
function ajax_load_cities() {
    check_ajax_referer('cities_search_nonce', 'nonce');

    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = 20;

    $cities = get_cities_table_data($search, $page, $per_page);
    $total_count = get_cities_total_count($search);
    $total_pages = ceil($total_count / $per_page);

    $response_data = array(
        'cities' => array(),
        'total_pages' => $total_pages,
        'current_page' => $page
    );

    if ($cities) {
        foreach ($cities as $city) {
            $temp = '';
            if ($city->latitude && $city->longitude) {
                $api_key = get_option('openweathermap_api_key');
                $url = "https://api.openweathermap.org/data/2.5/weather?lat={$city->latitude}&lon={$city->longitude}&units=metric&appid={$api_key}";
                $res = wp_remote_get($url);
                if (!is_wp_error($res)) {
                    $body = wp_remote_retrieve_body($res);
                    $data = json_decode($body);
                    if (isset($data->main->temp)) {
                        $temp = $data->main->temp;
                    }
                }
            }
            $response_data['cities'][] = array(
                'country' => esc_html($city->country_name),
                'city' => esc_html($city->city_name),
                'temp' => $temp !== '' ? esc_html($temp) : esc_html__('N/A', 'textdomain')
            );
        }
    }

    wp_send_json_success($response_data);
}
add_action('wp_ajax_load_cities', 'ajax_load_cities');
add_action('wp_ajax_nopriv_load_cities', 'ajax_load_cities');