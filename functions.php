<?php
/* Adds page name to classes for page */
/* Modified is_single to is_singular, which targets both single archive and pages */
/* Commented out echo statement, as it was showing up on the page output */
add_filter('body_class','page_class');
function page_class($classes) {
   global $wp_query;
   $page = '';
   $page = $wp_query->query_vars['pagename'];
   // add 'pagename' to the $classes array
   $classes[] = $page;
   // return the $classes array
   return $classes;
}
// JS for responsive tables
// Enqueue a script
function myprefix_enqueue_scripts() {
	wp_register_script('table-resp', get_stylesheet_directory_uri() . '/js/table.js', array() );
    wp_enqueue_script( 'table-resp', get_stylesheet_directory_uri() . '/js/table.js', array(), true );
}
add_action( 'wp_enqueue_scripts', 'myprefix_enqueue_scripts' );
// 1. Add the column labels to the admin screen
add_filter( 'manage_your_cpt_slug_posts_columns', 'add_custom_cpt_columns' );
function add_custom_cpt_columns( $column ) {
    // Add a new column with slug and label
    $column['ai4-date'] = __( 'AI4 Date', 'text_domain' );
    return $column;
}
// 2. Populate the custom columns with data
add_action( 'manage_your_cpt_slug_posts_custom_column', 'render_custom_cpt_columns', 10, 2 );
function render_custom_cpt_columns( $column, $post_id ) {
    if ( '$column_name' == 'ai4-date' ) {
        // Retrieve the saved custom field value
        $value = get_post_meta( $post_id, 'ai4-date', true );
        echo ! empty( $value ) ? esc_html( $value ) : '—';
    }
}
// Sort and only show current and future dates
add_filter( 'generateblocks_query_loop_args', function( $query_args, $attributes ) {
    $meta_key = 'ai4-date';
    $today = date( 'Ymd' );

    if ( empty( $attributes['className'] ) ) {
        return $query_args;
    }

    $class_name = $attributes['className'];

    // TOEKOMSTIG: >= vandaag (alleen gepubliceerd)
    if ( strpos( $class_name, 'order-by-date' ) !== false ) {
        $query_args['post_type'] = 'ai4-events';
        $query_args['post_status'] = 'publish';  // ← DIT!
        $query_args['meta_key'] = $meta_key;
        $query_args['orderby'] = 'meta_value';
        $query_args['order'] = 'ASC';
        $query_args['meta_query'] = array(
            array(
                'key'     => $meta_key,
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        );
    }
    // GESPEELD: < vandaag (alleen gepubliceerd)
    elseif ( strpos( $class_name, 'order-by-date' ) !== false ) {
        $query_args['post_type'] = 'ai4-events';
        $query_args['post_status'] = 'publish';  // ← DIT!
        $query_args['meta_key'] = $meta_key;
        $query_args['orderby'] = 'meta_value';
        $query_args['order'] = 'DESC';  // Nieuwste gespeeld bovenaan
        $query_args['meta_query'] = array(
            array(
                'key'     => $meta_key,
                'value'   => $today,
                'compare' => '<',
                'type'    => 'DATE',
            ),
        );
    }

    return $query_args;
}, 10, 2 );
// Format date
add_filter( 'generateblocks_dynamic_tag_output', function( $output, $options ) {
    if ( empty( $options['customdateformat'] ) ) {
        return $output;
    }

    $date_value = get_field('ai4-date'); // Replace with your actual ACF date field name

    if ( $date_value ) {
        // Since the date is returned as "Y/m/d", adjust the createFromFormat accordingly.
        $formatted_date = DateTime::createFromFormat('Y/m/d', $date_value);
        if ( $formatted_date ) {
            $output = $formatted_date->format('n/j/Y'); // Example: "22. February 2025"
        }
    }

    return $output;
}, 10, 2 );
// Add columns to admin
add_filter('manage_ai4-events_posts_columns', 'my_custom_columns');
function my_custom_columns($columns) {
//    $columns['ai4-date'] = __('AI4 Date'),
	$columns = array (
	  'cb' => $columns['cb'],
	  'title' => __('Title'),
	  'ai4-date' => __('AI4 Date'),
    );
    return $columns;
}
add_action('manage_ai4-events_posts_custom_column', 'my_custom_column_content', 10, 2);
function my_custom_column_content($column, $post_id) {
    if ($column === 'ai4-date') {
        echo get_post_meta($post_id,'ai4-date', true);
    }
}
add_filter('manage_ai4-events_sortable_columns', 'my_sortable_columns');
function my_sortable_columns($columns) {
    $columns['ai4-date'] = 'AI4 Date';
    return $columns;
}
?>