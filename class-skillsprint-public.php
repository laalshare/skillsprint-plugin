<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing side of the site.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public
 */
class SkillSprint_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Use only on blueprint pages or when our shortcodes are present
        if ( is_singular( 'blueprint' ) || 
             is_post_type_archive( 'blueprint' ) || 
             is_tax( 'blueprint_category' ) || 
             is_tax( 'blueprint_tag' ) || 
             is_tax( 'blueprint_difficulty' ) || 
             $this->has_skillsprint_shortcode() ) {
            
            wp_enqueue_style( 'dashicons' );
            wp_enqueue_style( 'select2', SKILLSPRINT_PLUGIN_URL . 'public/css/select2.min.css', array(), '4.1.0', 'all' );
            wp_enqueue_style( $this->plugin_name, SKILLSPRINT_PLUGIN_URL . 'public/css/skillsprint-public.css', array(), $this->version, 'all' );
            wp_enqueue_style( 'skillsprint-dashboard', SKILLSPRINT_PLUGIN_URL . 'public/css/skillsprint-dashboard.css', array(), $this->version, 'all' );
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Use only on blueprint pages or when our shortcodes are present
        if ( is_singular( 'blueprint' ) || 
             is_post_type_archive( 'blueprint' ) || 
             is_tax( 'blueprint_category' ) || 
             is_tax( 'blueprint_tag' ) || 
             is_tax( 'blueprint_difficulty' ) || 
             $this->has_skillsprint_shortcode() ) {
            
            wp_enqueue_script( 'chartjs', SKILLSPRINT_PLUGIN_URL . 'public/js/chart.min.js', array(), '3.7.0', true );
            wp_enqueue_script( 'select2', SKILLSPRINT_PLUGIN_URL . 'public/js/select2.min.js', array( 'jquery' ), '4.1.0', true );
            wp_enqueue_script( $this->plugin_name, SKILLSPRINT_PLUGIN_URL . 'public/js/skillsprint-public.js', array( 'jquery' ), $this->version, true );
            wp_enqueue_script( 'blueprint-viewer', SKILLSPRINT_PLUGIN_URL . 'public/js/blueprint-viewer.js', array( 'jquery' ), $this->version, true );
            wp_enqueue_script( 'quiz-interface', SKILLSPRINT_PLUGIN_URL . 'public/js/quiz-interface.js', array( 'jquery' ), $this->version, true );
            
            // Pass ajax url and user info to script
            wp_localize_script( $this->plugin_name, 'skillsprint', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'skillsprint_nonce' ),
                'is_user_logged_in' => is_user_logged_in(),
                'user_id' => get_current_user_id(),
                'login_url' => wp_login_url( get_permalink() ),
                'register_url' => wp_registration_url(),
                'settings' => get_option( 'skillsprint_settings' ),
                'i18n' => array(
                    'login_required' => __( 'Login required', 'skillsprint' ),
                    'login_message' => __( 'You need to log in to access this content.', 'skillsprint' ),
                    'please_login' => __( 'Please log in to continue your learning journey.', 'skillsprint' ),
                    'day_locked' => __( 'Day locked', 'skillsprint' ),
                    'complete_previous' => __( 'You need to complete the previous days before accessing this one.', 'skillsprint' ),
                    'quiz_submitted' => __( 'Quiz submitted successfully!', 'skillsprint' ),
                    'quiz_error' => __( 'Error submitting quiz. Please try again.', 'skillsprint' ),
                    'day_completed' => __( 'Day completed!', 'skillsprint' ),
                    'congratulations' => __( 'Congratulations!', 'skillsprint' ),
                    'blueprint_completed' => __( 'You have completed this blueprint!', 'skillsprint' ),
                    'correct' => __( 'Correct', 'skillsprint' ),
                    'incorrect' => __( 'Incorrect', 'skillsprint' ),
                    'retry' => __( 'Retry', 'skillsprint' ),
                    'continue' => __( 'Continue', 'skillsprint' ),
                    'next_day' => __( 'Next Day', 'skillsprint' ),
                    'previous_day' => __( 'Previous Day', 'skillsprint' ),
                    'submit' => __( 'Submit', 'skillsprint' ),
                    'saving' => __( 'Saving...', 'skillsprint' ),
                    'loading' => __( 'Loading...', 'skillsprint' ),
                )
            ) );
        }
    }

    /**
     * Check if current page contains any of our shortcodes.
     *
     * @since    1.0.0
     * @return   boolean   True if page has skillsprint shortcode.
     */
    private function has_skillsprint_shortcode() {
        global $post;
        
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'skillsprint_dashboard' ) ||
             has_shortcode( $post->post_content, 'skillsprint_blueprints' ) ||
             has_shortcode( $post->post_content, 'skillsprint_leaderboard' ) ) {
            return true;
        }
        
        return false;
    }

    /**
     * Register shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode( 'skillsprint_dashboard', array( $this, 'dashboard_shortcode' ) );
        add_shortcode( 'skillsprint_blueprints', array( $this, 'blueprints_shortcode' ) );
        add_shortcode( 'skillsprint_leaderboard', array( $this, 'leaderboard_shortcode' ) );
    }

    /**
     * Dashboard shortcode callback.
     *
     * @since    1.0.0
     * @param    array $atts Shortcode attributes.
     * @return   string Shortcode output.
     */
    public function dashboard_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'show_progress' => 'yes',
            'show_achievements' => 'yes',
            'show_stats' => 'yes',
            'show_recommendations' => 'yes',
        ), $atts, 'skillsprint_dashboard' );
        
        ob_start();
        
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $in_progress = SkillSprint_Blueprint::get_user_in_progress_blueprints( $user_id, 5 );
            $completed = SkillSprint_Blueprint::get_user_completed_blueprints( $user_id, 5 );
            $recommended = SkillSprint_Blueprint::get_recommended_blueprints( $user_id, 3 );
            $achievements = SkillSprint_DB::get_user_achievements( $user_id );
            $total_points = SkillSprint_DB::get_user_total_points( $user_id );
            $streak_info = SkillSprint_DB::get_user_streak( $user_id );
            
            include SKILLSPRINT_PLUGIN_DIR . 'public/partials/user-dashboard.php';
        } else {
            include SKILLSPRINT_PLUGIN_DIR . 'public/partials/login-required.php';
        }
        
        return ob_get_clean();
    }

    /**
     * Blueprints shortcode callback.
     *
     * @since    1.0.0
     * @param    array $atts Shortcode attributes.
     * @return   string Shortcode output.
     */
    public function blueprints_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'category' => '',
            'tag' => '',
            'difficulty' => '',
            'limit' => 12,
            'columns' => 3,
            'show_filters' => 'yes',
            'layout' => 'grid', // grid or list
        ), $atts, 'skillsprint_blueprints' );
        
        // Parse attributes
        $limit = intval( $atts['limit'] );
        $columns = intval( $atts['columns'] );
        $show_filters = $atts['show_filters'] === 'yes';
        $layout = in_array( $atts['layout'], array( 'grid', 'list' ) ) ? $atts['layout'] : 'grid';
        
        // Build query args
        $args = array(
            'post_type' => 'blueprint',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        // Add taxonomy filters
        $tax_query = array();
        
        if ( ! empty( $atts['category'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'blueprint_category',
                'field' => 'slug',
                'terms' => explode( ',', $atts['category'] ),
            );
        }
        
        if ( ! empty( $atts['tag'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'blueprint_tag',
                'field' => 'slug',
                'terms' => explode( ',', $atts['tag'] ),
            );
        }
        
        if ( ! empty( $atts['difficulty'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'blueprint_difficulty',
                'field' => 'slug',
                'terms' => explode( ',', $atts['difficulty'] ),
            );
        }
        
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }
        
        // Handle URL parameters for filtering
        if ( isset( $_GET['blueprint_category'] ) ) {
            if ( ! isset( $args['tax_query'] ) ) {
                $args['tax_query'] = array();
            }
            
            $args['tax_query'][] = array(
                'taxonomy' => 'blueprint_category',
                'field' => 'slug',
                'terms' => sanitize_text_field( $_GET['blueprint_category'] ),
            );
        }
        
        if ( isset( $_GET['blueprint_tag'] ) ) {
            if ( ! isset( $args['tax_query'] ) ) {
                $args['tax_query'] = array();
            }
            
            $args['tax_query'][] = array(
                'taxonomy' => 'blueprint_tag',
                'field' => 'slug',
                'terms' => sanitize_text_field( $_GET['blueprint_tag'] ),
            );
        }
        
        if ( isset( $_GET['blueprint_difficulty'] ) ) {
            if ( ! isset( $args['tax_query'] ) ) {
                $args['tax_query'] = array();
            }
            
            $args['tax_query'][] = array(
                'taxonomy' => 'blueprint_difficulty',
                'field' => 'slug',
                'terms' => sanitize_text_field( $_GET['blueprint_difficulty'] ),
            );
        }
        
        // Handle search
        if ( isset( $_GET['blueprint_search'] ) ) {
            $args['s'] = sanitize_text_field( $_GET['blueprint_search'] );
        }
        
        // Get blueprints
        $blueprints_query = new WP_Query( $args );
        
        // Get filter options
        $categories = get_terms( array(
            'taxonomy' => 'blueprint_category',
            'hide_empty' => true,
        ) );
        
        $tags = get_terms( array(
            'taxonomy' => 'blueprint_tag',
            'hide_empty' => true,
        ) );
        
        $difficulties = get_terms( array(
            'taxonomy' => 'blueprint_difficulty',
            'hide_empty' => true,
        ) );
        
        ob_start();
        include SKILLSPRINT_PLUGIN_DIR . 'public/partials/blueprints-list.php';
        return ob_get_clean();
    }

    /**
     * Leaderboard shortcode callback.
     *
     * @since    1.0.0
     * @param    array $atts Shortcode attributes.
     * @return   string Shortcode output.
     */
    public function leaderboard_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'limit' => 10,
            'show_avatars' => 'yes',
            'show_streaks' => 'yes',
        ), $atts, 'skillsprint_leaderboard' );
        
        // Get settings
        $settings = get_option( 'skillsprint_settings' );
        
        // Check if leaderboard is enabled
        if ( ! isset( $settings['leaderboard_enabled'] ) || ! $settings['leaderboard_enabled'] ) {
            return '<div class="skillsprint-message">' . __( 'Leaderboard is currently disabled.', 'skillsprint' ) . '</div>';
        }
        
        // Parse attributes
        $limit = intval( $atts['limit'] );
        $show_avatars = $atts['show_avatars'] === 'yes';
        $show_streaks = $atts['show_streaks'] === 'yes';
        
        // Get leaderboard data
        $leaderboard = SkillSprint_DB::get_leaderboard( $limit );
        
        ob_start();
        include SKILLSPRINT_PLUGIN_DIR . 'public/partials/leaderboard.php';
        return ob_get_clean();
    }

    /**
     * Filter blueprint content.
     *
     * @since    1.0.0
     * @param    string $content The content.
     * @return   string The filtered content.
     */
    public function filter_blueprint_content( $content ) {
        global $post;
        
        if ( is_singular( 'blueprint' ) && $post->post_type === 'blueprint' ) {
            // Increment view count
            SkillSprint_Blueprint::increment_view_count( $post->ID );
            
            // Get settings
            $settings = get_option( 'skillsprint_settings' );
            $days_free_access = isset( $settings['days_free_access'] ) ? intval( $settings['days_free_access'] ) : 2;
            
            // Get days data
            $days_data = SkillSprint_DB::get_blueprint_days_data( $post->ID );
            
            // Get user progress if logged in
            $user_id = get_current_user_id();
            $user_progress = array();
            
            if ( $user_id ) {
                $user_progress = SkillSprint_DB::get_user_blueprint_progress( $user_id, $post->ID );
            }
            
            ob_start();
            include SKILLSPRINT_PLUGIN_DIR . 'public/partials/blueprint-content.php';
            return ob_get_clean();
        }
        
        return $content;
    }

    /**
     * Custom blueprint single template.
     *
     * @since    1.0.0
     * @param    string $template The template.
     * @return   string The new template.
     */
    public function blueprint_single_template( $template ) {
        global $post;
        
        if ( $post->post_type === 'blueprint' ) {
            // Check if theme has a custom template
            $theme_template = locate_template( array( 'single-blueprint.php' ) );
            
            if ( $theme_template ) {
                return $theme_template;
            }
            
            // Use plugin template
            $plugin_template = SKILLSPRINT_PLUGIN_DIR . 'public/templates/single-blueprint.php';
            
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        
        return $template;
    }

    /**
     * Custom blueprint archive template.
     *
     * @since    1.0.0
     * @param    string $template The template.
     * @return   string The new template.
     */
    public function blueprint_archive_template( $template ) {
        if ( is_post_type_archive( 'blueprint' ) || is_tax( 'blueprint_category' ) || is_tax( 'blueprint_tag' ) || is_tax( 'blueprint_difficulty' ) ) {
            // Check if theme has a custom template
            $theme_template = locate_template( array( 'archive-blueprint.php' ) );
            
            if ( $theme_template ) {
                return $theme_template;
            }
            
            // Use plugin template
            $plugin_template = SKILLSPRINT_PLUGIN_DIR . 'public/templates/archive-blueprint.php';
            
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        
        return $template;
    }

    /**
     * Add rewrite endpoints for user dashboard.
     *
     * @since    1.0.0
     */
    public function add_rewrite_endpoints() {
        add_rewrite_endpoint( 'skillsprint-dashboard', EP_PAGES );
    }

    /**
     * Add query vars.
     *
     * @since    1.0.0
     * @param    array $vars The query vars.
     * @return   array The new query vars.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'skillsprint-dashboard';
        return $vars;
    }

    /**
     * Dashboard template redirect.
     *
     * @since    1.0.0
     */
    public function dashboard_template_redirect() {
        global $wp_query;
        
        // Only proceed if we're on the dashboard endpoint
        if ( ! isset( $wp_query->query_vars['skillsprint-dashboard'] ) ) {
            return;
        }
        
        // Ensure user is logged in
        if ( ! is_user_logged_in() ) {
            auth_redirect();
            exit;
        }
        
        // Load template
        include SKILLSPRINT_PLUGIN_DIR . 'public/templates/dashboard.php';
        exit;
    }

    /**
     * AJAX handler for user registration.
     *
     * @since    1.0.0
     */
    public function ajax_register_user() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check if registration is enabled
        if ( ! get_option( 'users_can_register' ) ) {
            wp_send_json_error( array( 'message' => __( 'Registration is currently disabled.', 'skillsprint' ) ) );
        }
        
        // Get form data
        $username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
        $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
        
        // Validate data
        if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'skillsprint' ) ) );
        }
        
        // Check if username exists
        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => __( 'Username already exists.', 'skillsprint' ) ) );
        }
        
        // Check if email exists
        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Email already exists.', 'skillsprint' ) ) );
        }
        
        // Create user
        $user_id = wp_create_user( $username, $password, $email );
        
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        }
        
        // Set role
        $user = new WP_User( $user_id );
        $user->set_role( 'blueprint_learner' );
        
        // Log user in
        wp_set_auth_cookie( $user_id, true );
        
        // Send success response
        wp_send_json_success( array(
            'message' => __( 'Registration successful! You are now logged in.', 'skillsprint' ),
            'redirect_url' => isset( $_POST['redirect_url'] ) ? esc_url_raw( $_POST['redirect_url'] ) : home_url()
        ) );
    }

    /**
     * AJAX handler for user login.
     *
     * @since    1.0.0
     */
    public function ajax_login_user() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Get form data
        $username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
        $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $remember = isset( $_POST['remember'] ) ? (bool) $_POST['remember'] : false;
        
        // Validate data
        if ( empty( $username ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'skillsprint' ) ) );
        }
        
        // Authenticate user
        $user = wp_signon( array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember
        ), is_ssl() );
        
        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => $user->get_error_message() ) );
        }
        
        // Send success response
        wp_send_json_success( array(
            'message' => __( 'Login successful!', 'skillsprint' ),
            'redirect_url' => isset( $_POST['redirect_url'] ) ? esc_url_raw( $_POST['redirect_url'] ) : home_url()
        ) );
    }
}