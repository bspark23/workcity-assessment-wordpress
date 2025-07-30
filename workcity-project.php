<?php
/**
 * Plugin Name: Workcity Projects
 * Description: A custom plugin for managing projects (for Workcity Assessment).
 * Version: 1.0
 * Author: Blessing Mbata
 */

// . Register custom post type
function workcity_register_project_post_type() {
    register_post_type('project', [
        'labels' => [
            'name' => 'Projects',
            'singular_name' => 'Project',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Project',
            'edit_item' => 'Edit Project',
            'new_item' => 'New Project',
            'view_item' => 'View Project',
            'search_items' => 'Search Projects',
        ],
        'public' => true,
        'menu_icon' => 'dashicons-portfolio',
        'supports' => ['title', 'editor', 'custom-fields'],
        'has_archive' => true,
        'show_in_rest' => true,
    ]);
}
add_action('init', 'workcity_register_project_post_type');


// . Add custom meta boxes (for client name, status, deadline)
function workcity_add_project_meta_boxes() {
    add_meta_box('project_meta', 'Project Details', 'workcity_project_meta_box_callback', 'project', 'normal', 'high');
}
add_action('add_meta_boxes', 'workcity_add_project_meta_boxes');

function workcity_project_meta_box_callback($post) {
    $client = get_post_meta($post->ID, '_project_client', true);
    $status = get_post_meta($post->ID, '_project_status', true);
    $deadline = get_post_meta($post->ID, '_project_deadline', true);
    ?>
    <p>
        <label>Client Name:</label><br />
        <input type="text" name="project_client" value="<?php echo esc_attr($client); ?>" />
    </p>
    <p>
        <label>Status:</label><br />
        <select name="project_status">
            <option value="Pending" <?php selected($status, 'Pending'); ?>>Pending</option>
            <option value="In Progress" <?php selected($status, 'In Progress'); ?>>In Progress</option>
            <option value="Completed" <?php selected($status, 'Completed'); ?>>Completed</option>
        </select>
    </p>
    <p>
        <label>Deadline:</label><br />
        <input type="date" name="project_deadline" value="<?php echo esc_attr($deadline); ?>" />
    </p>
    <?php
}

function workcity_save_project_meta($post_id) {
    if (array_key_exists('project_client', $_POST)) {
        update_post_meta($post_id, '_project_client', sanitize_text_field($_POST['project_client']));
    }
    if (array_key_exists('project_status', $_POST)) {
        update_post_meta($post_id, '_project_status', sanitize_text_field($_POST['project_status']));
    }
    if (array_key_exists('project_deadline', $_POST)) {
        update_post_meta($post_id, '_project_deadline', sanitize_text_field($_POST['project_deadline']));
    }
}
add_action('save_post', 'workcity_save_project_meta');


//  Shortcode to display projects
function workcity_display_projects_shortcode() {
    $args = [
        'post_type' => 'project',
        'posts_per_page' => -1
    ];
    $projects = new WP_Query($args);
    
    ob_start();
    echo '<div class="projects">';
    while ($projects->have_posts()) {
        $projects->the_post();
        $client = get_post_meta(get_the_ID(), '_project_client', true);
        $status = get_post_meta(get_the_ID(), '_project_status', true);
        $deadline = get_post_meta(get_the_ID(), '_project_deadline', true);

        echo '<div class="project">';
        echo '<h3>' . get_the_title() . '</h3>';
        echo '<p>' . get_the_content() . '</p>';
        echo '<p><strong>Client:</strong> ' . esc_html($client) . '</p>';
        echo '<p><strong>Status:</strong> ' . esc_html($status) . '</p>';
        echo '<p><strong>Deadline:</strong> ' . esc_html($deadline) . '</p>';
        echo '</div>';
    }
    wp_reset_postdata();
    echo '</div>';
    
    return ob_get_clean();
}
add_shortcode('workcity_projects', 'workcity_display_projects_shortcode');
