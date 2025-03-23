<?php
/**
 * Custom post types for the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Custom post types for the plugin.
 *
 * Defines and registers custom post types and taxonomies.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Post_Types {

    /**
     * Register custom post types.
     *
     * @since    1.0.0
     */
    public function register_post_types() {
        
        // 7-Day Blueprint Post Type
        $labels = array(
            'name'                  => _x( '7-Day Blueprints', 'Post Type General Name', 'skillsprint' ),
            'singular_name'         => _x( 'Blueprint', 'Post Type Singular Name', 'skillsprint' ),
            'menu_name'             => __( '7-Day Blueprints', 'skillsprint' ),
            'name_admin_bar'        => __( 'Blueprint', 'skillsprint' ),
            'archives'              => __( 'Blueprint Archives', 'skillsprint' ),
            'attributes'            => __( 'Blueprint Attributes', 'skillsprint' ),
            'parent_item_colon'     => __( 'Parent Blueprint:', 'skillsprint' ),
            'all_items'             => __( 'All Blueprints', 'skillsprint' ),
            'add_new_item'          => __( 'Add New Blueprint', 'skillsprint' ),
            'add_new'               => __( 'Add New', 'skillsprint' ),
            'new_item'              => __( 'New Blueprint', 'skillsprint' ),
            'edit_item'             => __( 'Edit Blueprint', 'skillsprint' ),
            'update_item'           => __( 'Update Blueprint', 'skillsprint' ),
            'view_item'             => __( 'View Blueprint', 'skillsprint' ),
            'view_items'            => __( 'View Blueprints', 'skillsprint' ),
            'search_items'          => __( 'Search Blueprint', 'skillsprint' ),
            'not_found'             => __( 'Not found', 'skillsprint' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'skillsprint' ),
            'featured_image'        => __( 'Featured Image', 'skillsprint' ),
            'set_featured_image'    => __( 'Set featured image', 'skillsprint' ),
            'remove_featured_image' => __( 'Remove featured image', 'skillsprint' ),
            'use_featured_image'    => __( 'Use as featured image', 'skillsprint' ),
            'insert_into_item'      => __( 'Insert into blueprint', 'skillsprint' ),
            'uploaded_to_this_item' => __( 'Uploaded to this blueprint', 'skillsprint' ),
            'items_list'            => __( 'Blueprints list', 'skillsprint' ),
            'items_list_navigation' => __( 'Blueprints list navigation', 'skillsprint' ),
            'filter_items_list'     => __( 'Filter blueprints list', 'skillsprint' ),
        );
        
        $args = array(
            'label'                 => __( 'Blueprint', 'skillsprint' ),
            'description'           => __( '7-Day Learning Blueprint', 'skillsprint' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ),
            'taxonomies'            => array( 'blueprint_category', 'blueprint_tag', 'blueprint_difficulty' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-welcome-learn-more',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => array('blueprint', 'blueprints'),
            'map_meta_cap'          => true,
            'show_in_rest'          => true,
            'rest_base'             => 'blueprints',
            'rewrite'               => array(
                'slug'              => 'blueprint',
                'with_front'        => false,
                'pages'             => true,
                'feeds'             => true,
            ),
        );
        
        register_post_type( 'blueprint', $args );
    }

    /**
     * Register custom taxonomies.
     *
     * @since    1.0.0
     */
    public function register_taxonomies() {
        
        // Blueprint Category Taxonomy
        $labels = array(
            'name'                       => _x( 'Blueprint Categories', 'Taxonomy General Name', 'skillsprint' ),
            'singular_name'              => _x( 'Blueprint Category', 'Taxonomy Singular Name', 'skillsprint' ),
            'menu_name'                  => __( 'Categories', 'skillsprint' ),
            'all_items'                  => __( 'All Categories', 'skillsprint' ),
            'parent_item'                => __( 'Parent Category', 'skillsprint' ),
            'parent_item_colon'          => __( 'Parent Category:', 'skillsprint' ),
            'new_item_name'              => __( 'New Category Name', 'skillsprint' ),
            'add_new_item'               => __( 'Add New Category', 'skillsprint' ),
            'edit_item'                  => __( 'Edit Category', 'skillsprint' ),
            'update_item'                => __( 'Update Category', 'skillsprint' ),
            'view_item'                  => __( 'View Category', 'skillsprint' ),
            'separate_items_with_commas' => __( 'Separate categories with commas', 'skillsprint' ),
            'add_or_remove_items'        => __( 'Add or remove categories', 'skillsprint' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'skillsprint' ),
            'popular_items'              => __( 'Popular Categories', 'skillsprint' ),
            'search_items'               => __( 'Search Categories', 'skillsprint' ),
            'not_found'                  => __( 'Not Found', 'skillsprint' ),
            'no_terms'                   => __( 'No categories', 'skillsprint' ),
            'items_list'                 => __( 'Categories list', 'skillsprint' ),
            'items_list_navigation'      => __( 'Categories list navigation', 'skillsprint' ),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rewrite'                    => array(
                'slug'                   => 'blueprint-category',
                'with_front'             => false,
                'hierarchical'           => true,
            ),
        );
        
        register_taxonomy( 'blueprint_category', array( 'blueprint' ), $args );
        
        // Blueprint Tag Taxonomy
        $labels = array(
            'name'                       => _x( 'Blueprint Tags', 'Taxonomy General Name', 'skillsprint' ),
            'singular_name'              => _x( 'Blueprint Tag', 'Taxonomy Singular Name', 'skillsprint' ),
            'menu_name'                  => __( 'Tags', 'skillsprint' ),
            'all_items'                  => __( 'All Tags', 'skillsprint' ),
            'parent_item'                => __( 'Parent Tag', 'skillsprint' ),
            'parent_item_colon'          => __( 'Parent Tag:', 'skillsprint' ),
            'new_item_name'              => __( 'New Tag Name', 'skillsprint' ),
            'add_new_item'               => __( 'Add New Tag', 'skillsprint' ),
            'edit_item'                  => __( 'Edit Tag', 'skillsprint' ),
            'update_item'                => __( 'Update Tag', 'skillsprint' ),
            'view_item'                  => __( 'View Tag', 'skillsprint' ),
            'separate_items_with_commas' => __( 'Separate tags with commas', 'skillsprint' ),
            'add_or_remove_items'        => __( 'Add or remove tags', 'skillsprint' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'skillsprint' ),
            'popular_items'              => __( 'Popular Tags', 'skillsprint' ),
            'search_items'               => __( 'Search Tags', 'skillsprint' ),
            'not_found'                  => __( 'Not Found', 'skillsprint' ),
            'no_terms'                   => __( 'No tags', 'skillsprint' ),
            'items_list'                 => __( 'Tags list', 'skillsprint' ),
            'items_list_navigation'      => __( 'Tags list navigation', 'skillsprint' ),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rewrite'                    => array(
                'slug'                   => 'blueprint-tag',
                'with_front'             => false,
            ),
        );
        
        register_taxonomy( 'blueprint_tag', array( 'blueprint' ), $args );
        
        // Blueprint Difficulty Taxonomy
        $labels = array(
            'name'                       => _x( 'Difficulty Levels', 'Taxonomy General Name', 'skillsprint' ),
            'singular_name'              => _x( 'Difficulty Level', 'Taxonomy Singular Name', 'skillsprint' ),
            'menu_name'                  => __( 'Difficulty', 'skillsprint' ),
            'all_items'                  => __( 'All Difficulty Levels', 'skillsprint' ),
            'parent_item'                => __( 'Parent Difficulty Level', 'skillsprint' ),
            'parent_item_colon'          => __( 'Parent Difficulty Level:', 'skillsprint' ),
            'new_item_name'              => __( 'New Difficulty Level Name', 'skillsprint' ),
            'add_new_item'               => __( 'Add New Difficulty Level', 'skillsprint' ),
            'edit_item'                  => __( 'Edit Difficulty Level', 'skillsprint' ),
            'update_item'                => __( 'Update Difficulty Level', 'skillsprint' ),
            'view_item'                  => __( 'View Difficulty Level', 'skillsprint' ),
            'separate_items_with_commas' => __( 'Separate difficulty levels with commas', 'skillsprint' ),
            'add_or_remove_items'        => __( 'Add or remove difficulty levels', 'skillsprint' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'skillsprint' ),
            'popular_items'              => __( 'Popular Difficulty Levels', 'skillsprint' ),
            'search_items'               => __( 'Search Difficulty Levels', 'skillsprint' ),
            'not_found'                  => __( 'Not Found', 'skillsprint' ),
            'no_terms'                   => __( 'No difficulty levels', 'skillsprint' ),
            'items_list'                 => __( 'Difficulty Levels list', 'skillsprint' ),
            'items_list_navigation'      => __( 'Difficulty Levels list navigation', 'skillsprint' ),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rewrite'                    => array(
                'slug'                   => 'blueprint-difficulty',
                'with_front'             => false,
            ),
        );
        
        register_taxonomy( 'blueprint_difficulty', array( 'blueprint' ), $args );
        
        // Create default difficulty levels if they don't exist
        $difficulty_levels = array(
            'beginner' => array(
                'name' => __('Beginner', 'skillsprint'),
                'slug' => 'beginner',
                'description' => __('For those new to the subject', 'skillsprint')
            ),
            'intermediate' => array(
                'name' => __('Intermediate', 'skillsprint'),
                'slug' => 'intermediate',
                'description' => __('For those with some experience', 'skillsprint')
            ),
            'advanced' => array(
                'name' => __('Advanced', 'skillsprint'),
                'slug' => 'advanced',
                'description' => __('For those with significant experience', 'skillsprint')
            ),
            'expert' => array(
                'name' => __('Expert', 'skillsprint'),
                'slug' => 'expert',
                'description' => __('For those with deep expertise', 'skillsprint')
            ),
        );
        
        foreach ($difficulty_levels as $difficulty) {
            if (!term_exists($difficulty['name'], 'blueprint_difficulty')) {
                wp_insert_term(
                    $difficulty['name'],
                    'blueprint_difficulty',
                    array(
                        'description' => $difficulty['description'],
                        'slug' => $difficulty['slug']
                    )
                );
            }
        }
    }

}