
    
    
<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin area.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin
 */
class SkillSprint_Admin {

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
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        
        // Load on blueprint edit screens
        if ( 'blueprint' === $screen->post_type || 'toplevel_page_skillsprint' === $screen->id ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_style( 'jquery-ui-datepicker' );
            wp_enqueue_style( 'select2', SKILLSPRINT_PLUGIN_URL . 'admin/css/select2.min.css', array(), '4.1.0', 'all' );
            wp_enqueue_style( $this->plugin_name, SKILLSPRINT_PLUGIN_URL . 'admin/css/skillsprint-admin.css', array(), $this->version, 'all' );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        
        // Load on blueprint edit screens
        if ( 'blueprint' === $screen->post_type || 'toplevel_page_skillsprint' === $screen->id ) {
            wp_enqueue_media();
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'select2', SKILLSPRINT_PLUGIN_URL . 'admin/js/select2.min.js', array( 'jquery' ), '4.1.0', true );
            
            wp_enqueue_script( 'skillsprint-admin', SKILLSPRINT_PLUGIN_URL . 'admin/js/skillsprint-admin.js', array( 'jquery', 'jquery-ui-sortable' ), $this->version, true );
            wp_enqueue_script( 'blueprint-builder', SKILLSPRINT_PLUGIN_URL . 'admin/js/blueprint-builder.js', array( 'jquery', 'jquery-ui-sortable', 'wp-editor' ), $this->version, true );
            wp_enqueue_script( 'quiz-builder', SKILLSPRINT_PLUGIN_URL . 'admin/js/quiz-builder.js', array( 'jquery', 'jquery-ui-sortable' ), $this->version, true );
            
            // Pass ajax url to script
            wp_localize_script( 'skillsprint-admin', 'skillsprint', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'skillsprint_admin_nonce' ),
                'blueprint_id' => get_the_ID(),
                'i18n' => array(
                    'confirm_delete' => __( 'Are you sure you want to delete this? This action cannot be undone.', 'skillsprint' ),
                    'save_success' => __( 'Data saved successfully!', 'skillsprint' ),
                    'save_error' => __( 'Error saving data. Please try again.', 'skillsprint' ),
                    'choose_image' => __( 'Choose Image', 'skillsprint' ),
                    'select_file' => __( 'Select File', 'skillsprint' ),
                    'add_new' => __( 'Add New', 'skillsprint' ),
                    'remove' => __( 'Remove', 'skillsprint' ),
                )
            ) );
        }
    }

    /**
     * Add admin menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Main menu
        add_menu_page(
            __( 'SkillSprint', 'skillsprint' ),
            __( 'SkillSprint', 'skillsprint' ),
            'manage_skillsprint',
            'skillsprint',
            array( $this, 'display_plugin_admin_dashboard' ),
            'dashicons-welcome-learn-more',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'skillsprint',
            __( 'Dashboard', 'skillsprint' ),
            __( 'Dashboard', 'skillsprint' ),
            'manage_skillsprint',
            'skillsprint',
            array( $this, 'display_plugin_admin_dashboard' )
        );
        
        // Blueprints submenu
        add_submenu_page(
            'skillsprint',
            __( 'All Blueprints', 'skillsprint' ),
            __( 'All Blueprints', 'skillsprint' ),
            'edit_blueprints',
            'edit.php?post_type=blueprint'
        );
        
        // Add new blueprint submenu
        add_submenu_page(
            'skillsprint',
            __( 'Add New Blueprint', 'skillsprint' ),
            __( 'Add New Blueprint', 'skillsprint' ),
            'publish_blueprints',
            'post-new.php?post_type=blueprint'
        );
        
        // User progress submenu
        add_submenu_page(
            'skillsprint',
            __( 'User Progress', 'skillsprint' ),
            __( 'User Progress', 'skillsprint' ),
            'manage_skillsprint',
            'skillsprint-user-progress',
            array( $this, 'display_user_progress_page' )
        );
        
        // Blueprint categories submenu
        add_submenu_page(
            'skillsprint',
            __( 'Categories', 'skillsprint' ),
            __( 'Categories', 'skillsprint' ),
            'manage_categories',
            'edit-tags.php?taxonomy=blueprint_category&post_type=blueprint'
        );
        
        // Blueprint tags submenu
        add_submenu_page(
            'skillsprint',
            __( 'Tags', 'skillsprint' ),
            __( 'Tags', 'skillsprint' ),
            'manage_categories',
            'edit-tags.php?taxonomy=blueprint_tag&post_type=blueprint'
        );
        
        // Blueprint difficulty levels submenu
        add_submenu_page(
            'skillsprint',
            __( 'Difficulty Levels', 'skillsprint' ),
            __( 'Difficulty Levels', 'skillsprint' ),
            'manage_categories',
            'edit-tags.php?taxonomy=blueprint_difficulty&post_type=blueprint'
        );
        
        // Settings submenu
        add_submenu_page(
            'skillsprint',
            __( 'Settings', 'skillsprint' ),
            __( 'Settings', 'skillsprint' ),
            'manage_options',
            'skillsprint-settings',
            array( $this, 'display_plugin_settings_page' )
        );
    }
    
    /**
     * Display admin dashboard page.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_dashboard() {
        include_once SKILLSPRINT_PLUGIN_DIR . 'admin/partials/admin-dashboard.php';
    }
    
    /**
     * Display user progress page.
     *
     * @since    1.0.0
     */
    public function display_user_progress_page() {
        include_once SKILLSPRINT_PLUGIN_DIR . 'admin/partials/user-progress-page.php';
    }
    
    /**
     * Display plugin settings page.
     *
     * @since    1.0.0
     */
    public function display_plugin_settings_page() {
        include_once SKILLSPRINT_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    /**
     * Display admin notices.
     *
     * @since    1.0.0
     */
    public function display_admin_notices() {
        if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'blueprint' && isset( $_GET['blueprint_updated'] ) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Blueprint updated successfully!', 'skillsprint' ); ?></p>
            </div>
            <?php
        }
    }
    
    /**
     * Add meta boxes.
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        // Blueprint Days Metabox
        add_meta_box(
            'skillsprint_blueprint_days',
            __( '7-Day Blueprint Builder', 'skillsprint' ),
            array( $this, 'render_blueprint_days_metabox' ),
            'blueprint',
            'normal',
            'high'
        );
        
        // Blueprint Settings Metabox
        add_meta_box(
            'skillsprint_blueprint_settings',
            __( 'Blueprint Settings', 'skillsprint' ),
            array( $this, 'render_blueprint_settings_metabox' ),
            'blueprint',
            'side',
            'default'
        );
    }
    
    /**
     * Render blueprint days metabox.
     *
     * @since    1.0.0
     * @param    WP_Post $post The post object.
     */
    public function render_blueprint_days_metabox( $post ) {
        wp_nonce_field( 'skillsprint_blueprint_days_metabox', 'skillsprint_blueprint_days_nonce' );
        
        $days_data = SkillSprint_DB::get_blueprint_days_data( $post->ID );
        
        // Ensure we have 7 days
        if ( count( $days_data ) < 7 ) {
            for ( $i = count( $days_data ) + 1; $i <= 7; $i++ ) {
                $days_data[] = array(
                    'day_number' => $i,
                    'title' => sprintf( __( 'Day %d', 'skillsprint' ), $i ),
                    'learning_objectives' => '',
                    'content' => '',
                    'resources' => array(),
                    'quiz_id' => ''
                );
            }
        }
        
        include SKILLSPRINT_PLUGIN_DIR . 'admin/partials/blueprint-metabox.php';
    }
    
    /**
     * Render blueprint settings metabox.
     *
     * @since    1.0.0
     * @param    WP_Post $post The post object.
     */
    public function render_blueprint_settings_metabox($post) {
        // Add nonce for security
        wp_nonce_field('skillsprint_blueprint_settings', 'skillsprint_nonce');
        
        // Get saved values
        $estimated_time = get_post_meta($post->ID, '_skillsprint_estimated_time', true);
        $prerequisites = get_post_meta($post->ID, '_skillsprint_prerequisites', true);
        
        // Output fields
        ?>
        <div class="skillsprint-meta-field">
            <label for="skillsprint_estimated_time"><?php _e('Estimated Time', 'skillsprint'); ?></label>
            <input type="text" id="skillsprint_estimated_time" name="skillsprint_estimated_time" value="<?php echo esc_attr($estimated_time); ?>">
            <p class="description"><?php _e('Estimated time to complete this blueprint (e.g., "7 days, 30 minutes per day")', 'skillsprint'); ?></p>
        </div>
        
        <div class="skillsprint-meta-field">
            <label for="skillsprint_prerequisites"><?php _e('Prerequisites', 'skillsprint'); ?></label>
            <textarea id="skillsprint_prerequisites" name="skillsprint_prerequisites" rows="3"><?php echo esc_textarea($prerequisites); ?></textarea>
            <p class="description"><?php _e('What knowledge or tools are required before starting this blueprint', 'skillsprint'); ?></p>
        </div>
        <?php
    }
    
/**
 * Save meta box data.
 */
public function save_meta_box_data($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['skillsprint_nonce'])) {
        return;
    }
    
    // Verify the nonce
    if (!wp_verify_nonce($_POST['skillsprint_nonce'], 'skillsprint_blueprint_settings')) {
        return;
    }
    
    // If this is an autosave, our form has not been submitted, so we don't want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check the user's permissions
    if (isset($_POST['post_type']) && 'blueprint' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    
    // Save data
    if (isset($_POST['skillsprint_estimated_time'])) {
        update_post_meta($post_id, '_skillsprint_estimated_time', sanitize_text_field($_POST['skillsprint_estimated_time']));
    }
    
    if (isset($_POST['skillsprint_prerequisites'])) {
        update_post_meta($post_id, '_skillsprint_prerequisites', sanitize_textarea_field($_POST['skillsprint_prerequisites']));
    }
}
    
    /**
     * AJAX handler for saving blueprint data.
     *
     * @since    1.0.0
     */
    public function ajax_save_blueprint_data() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_admin_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check permissions
        $blueprint_id = isset( $_POST['blueprint_id'] ) ? intval( $_POST['blueprint_id'] ) : 0;
        
        if ( ! $blueprint_id || ! current_user_can( 'edit_post', $blueprint_id ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this blueprint.', 'skillsprint' ) ) );
        }
        
        // Get days data
        $days_data = isset( $_POST['days_data'] ) ? $_POST['days_data'] : array();
        
        if ( empty( $days_data ) || ! is_array( $days_data ) ) {
            wp_send_json_error( array( 'message' => __( 'No days data received.', 'skillsprint' ) ) );
        }
        
        // Sanitize days data
        $sanitized_days = array();
        
        foreach ( $days_data as $day ) {
            $sanitized_day = array(
                'day_number' => isset( $day['day_number'] ) ? intval( $day['day_number'] ) : 0,
                'title' => isset( $day['title'] ) ? sanitize_text_field( $day['title'] ) : '',
                'learning_objectives' => isset( $day['learning_objectives'] ) ? sanitize_textarea_field( $day['learning_objectives'] ) : '',
                'content' => isset( $day['content'] ) ? wp_kses_post( $day['content'] ) : '',
                'resources' => array(),
                'quiz_id' => isset( $day['quiz_id'] ) ? sanitize_text_field( $day['quiz_id'] ) : ''
            );
            
            // Sanitize resources
            if ( isset( $day['resources'] ) && is_array( $day['resources'] ) ) {
                foreach ( $day['resources'] as $resource ) {
                    $sanitized_day['resources'][] = array(
                        'title' => isset( $resource['title'] ) ? sanitize_text_field( $resource['title'] ) : '',
                        'url' => isset( $resource['url'] ) ? esc_url_raw( $resource['url'] ) : '',
                        'type' => isset( $resource['type'] ) ? sanitize_text_field( $resource['type'] ) : 'link'
                    );
                }
            }
            
            $sanitized_days[] = $sanitized_day;
        }
        
        // Save days data
        update_post_meta( $blueprint_id, '_skillsprint_days_data', $sanitized_days );
        
        // Return success
        wp_send_json_success( array( 
            'message' => __( 'Blueprint days data saved successfully!', 'skillsprint' ),
            'days_data' => $sanitized_days
        ) );
    }




    /**
     * AJAX handler for saving quiz data.
     *
     * @since    1.0.0
     */
    public function ajax_save_quiz_data() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_admin_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check permissions
        $blueprint_id = isset( $_POST['blueprint_id'] ) ? intval( $_POST['blueprint_id'] ) : 0;
        
        if ( ! $blueprint_id || ! current_user_can( 'edit_post', $blueprint_id ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this blueprint.', 'skillsprint' ) ) );
        }
        
        // Get quiz data
        $quiz_id = isset( $_POST['quiz_id'] ) ? sanitize_text_field( $_POST['quiz_id'] ) : '';
        $quiz_data = isset( $_POST['quiz_data'] ) ? $_POST['quiz_data'] : array();
        
        if ( empty( $quiz_id ) || empty( $quiz_data ) || ! is_array( $quiz_data ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid quiz data received.', 'skillsprint' ) ) );
        }
        
        // Sanitize quiz data
        $sanitized_quiz = array(
            'title' => isset( $quiz_data['title'] ) ? sanitize_text_field( $quiz_data['title'] ) : '',
            'description' => isset( $quiz_data['description'] ) ? sanitize_textarea_field( $quiz_data['description'] ) : '',
            'passing_score' => isset( $quiz_data['passing_score'] ) ? intval( $quiz_data['passing_score'] ) : 70,
            'max_attempts' => isset( $quiz_data['max_attempts'] ) ? intval( $quiz_data['max_attempts'] ) : 3,
            'questions' => array()
        );
        
        // Sanitize questions
        if ( isset( $quiz_data['questions'] ) && is_array( $quiz_data['questions'] ) ) {
            foreach ( $quiz_data['questions'] as $question ) {
                $sanitized_question = array(
                    'id' => isset( $question['id'] ) ? sanitize_text_field( $question['id'] ) : uniqid( 'q_' ),
                    'type' => isset( $question['type'] ) ? sanitize_text_field( $question['type'] ) : 'multiple_choice',
                    'text' => isset( $question['text'] ) ? sanitize_textarea_field( $question['text'] ) : '',
                    'points' => isset( $question['points'] ) ? intval( $question['points'] ) : 1,
                    'options' => array(),
                    'correct_answer' => isset( $question['correct_answer'] ) ? $question['correct_answer'] : '',
                    'explanation' => isset( $question['explanation'] ) ? sanitize_textarea_field( $question['explanation'] ) : ''
                );
                
                // Sanitize options for multiple choice and matching questions
                if ( isset( $question['options'] ) && is_array( $question['options'] ) ) {
                    foreach ( $question['options'] as $option ) {
                        // Handle different option formats based on question type
                        if ( $question['type'] === 'matching' && is_array( $option ) ) {
                            $sanitized_question['options'][] = array(
                                'left' => isset( $option['left'] ) ? sanitize_text_field( $option['left'] ) : '',
                                'right' => isset( $option['right'] ) ? sanitize_text_field( $option['right'] ) : ''
                            );
                        } else {
                            $sanitized_question['options'][] = sanitize_text_field( $option );
                        }
                    }
                }
                
                // Sanitize correct answer based on question type
                if ( $question['type'] === 'multiple_choice' || $question['type'] === 'true_false' ) {
                    $sanitized_question['correct_answer'] = sanitize_text_field( $question['correct_answer'] );
                } elseif ( $question['type'] === 'multiple_answer' && is_array( $question['correct_answer'] ) ) {
                    $sanitized_correct = array();
                    foreach ( $question['correct_answer'] as $answer ) {
                        $sanitized_correct[] = sanitize_text_field( $answer );
                    }
                    $sanitized_question['correct_answer'] = $sanitized_correct;
                } elseif ( $question['type'] === 'matching' && is_array( $question['correct_answer'] ) ) {
                    $sanitized_matches = array();
                    foreach ( $question['correct_answer'] as $key => $value ) {
                        $sanitized_matches[sanitize_text_field( $key )] = sanitize_text_field( $value );
                    }
                    $sanitized_question['correct_answer'] = $sanitized_matches;
                } elseif ( $question['type'] === 'short_answer' ) {
                    if ( is_array( $question['correct_answer'] ) ) {
                        $sanitized_answers = array();
                        foreach ( $question['correct_answer'] as $answer ) {
                            $sanitized_answers[] = sanitize_text_field( $answer );
                        }
                        $sanitized_question['correct_answer'] = $sanitized_answers;
                    } else {
                        $sanitized_question['correct_answer'] = array( sanitize_text_field( $question['correct_answer'] ) );
                    }
                }
                
                $sanitized_quiz['questions'][] = $sanitized_question;
            }
        }
        
        // Save quiz data
        update_post_meta( $blueprint_id, '_skillsprint_quiz_' . $quiz_id, $sanitized_quiz );
        
        // Return success
        wp_send_json_success( array( 
            'message' => __( 'Quiz data saved successfully!', 'skillsprint' ),
            'quiz_data' => $sanitized_quiz
        ) );
    }
    
    /**
     * AJAX handler for getting user progress data.
     *
     * @since    1.0.0
     */
    public function ajax_get_user_progress() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skillsprint_admin_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillsprint' ) ) );
        }
        
        // Check permissions
        if ( ! current_user_can( 'manage_skillsprint' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to view user progress.', 'skillsprint' ) ) );
        }
        
        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $blueprint_id = isset( $_POST['blueprint_id'] ) ? intval( $_POST['blueprint_id'] ) : 0;
        
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid user ID.', 'skillsprint' ) ) );
        }
        
        $user = get_user_by( 'id', $user_id );
        
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => __( 'User not found.', 'skillsprint' ) ) );
        }
        
        // Get progress data
        if ( $blueprint_id ) {
            // Get progress for specific blueprint
            $progress = SkillSprint_DB::get_user_blueprint_progress( $user_id, $blueprint_id );
            $completion_percentage = SkillSprint_DB::get_blueprint_completion_percentage( $user_id, $blueprint_id );
            $blueprint = get_post( $blueprint_id );
            
            if ( ! $blueprint ) {
                wp_send_json_error( array( 'message' => __( 'Blueprint not found.', 'skillsprint' ) ) );
            }
            
            $data = array(
                'user' => array(
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'email' => $user->user_email,
                    'avatar' => get_avatar_url( $user->ID, array( 'size' => 96 ) )
                ),
                'blueprint' => array(
                    'id' => $blueprint->ID,
                    'title' => $blueprint->post_title,
                    'permalink' => get_permalink( $blueprint->ID )
                ),
                'progress' => $progress,
                'completion_percentage' => $completion_percentage
            );
        } else {
            // Get progress overview for all blueprints
            $in_progress_blueprints = SkillSprint_Blueprint::get_user_in_progress_blueprints( $user_id, -1 );
            $completed_blueprints = SkillSprint_Blueprint::get_user_completed_blueprints( $user_id, -1 );
            
            $data = array(
                'user' => array(
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'email' => $user->user_email,
                    'avatar' => get_avatar_url( $user->ID, array( 'size' => 96 ) )
                ),
                'in_progress' => $in_progress_blueprints,
                'completed' => $completed_blueprints,
                'total_points' => SkillSprint_DB::get_user_total_points( $user_id ),
                'achievements' => SkillSprint_DB::get_user_achievements( $user_id ),
                'streak_info' => SkillSprint_DB::get_user_streak( $user_id )
            );
        }
        
        wp_send_json_success( $data );
    }
    
    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'skillsprint_settings',
            'skillsprint_settings',
            array( $this, 'sanitize_settings' )
        );
        
        // General Settings
        add_settings_section(
            'skillsprint_general_settings',
            __( 'General Settings', 'skillsprint' ),
            array( $this, 'general_settings_section_callback' ),
            'skillsprint_settings'
        );
        
        add_settings_field(
            'days_free_access',
            __( 'Days With Free Access', 'skillsprint' ),
            array( $this, 'days_free_access_callback' ),
            'skillsprint_settings',
            'skillsprint_general_settings'
        );
        
        add_settings_field(
            'strict_progression',
            __( 'Strict Day Progression', 'skillsprint' ),
            array( $this, 'strict_progression_callback' ),
            'skillsprint_settings',
            'skillsprint_general_settings'
        );
        
        // Gamification Settings
        add_settings_section(
            'skillsprint_gamification_settings',
            __( 'Gamification Settings', 'skillsprint' ),
            array( $this, 'gamification_settings_section_callback' ),
            'skillsprint_settings'
        );
        
        add_settings_field(
            'gamification_enabled',
            __( 'Enable Gamification', 'skillsprint' ),
            array( $this, 'gamification_enabled_callback' ),
            'skillsprint_settings',
            'skillsprint_gamification_settings'
        );
        
        add_settings_field(
            'leaderboard_enabled',
            __( 'Enable Leaderboard', 'skillsprint' ),
            array( $this, 'leaderboard_enabled_callback' ),
            'skillsprint_settings',
            'skillsprint_gamification_settings'
        );
        
        add_settings_field(
            'points_per_day_completion',
            __( 'Points Per Day Completion', 'skillsprint' ),
            array( $this, 'points_per_day_completion_callback' ),
            'skillsprint_settings',
            'skillsprint_gamification_settings'
        );
        
        add_settings_field(
            'points_per_quiz_correct',
            __( 'Points Per Correct Quiz Answer', 'skillsprint' ),
            array( $this, 'points_per_quiz_correct_callback' ),
            'skillsprint_settings',
            'skillsprint_gamification_settings'
        );
        
        add_settings_field(
            'points_per_blueprint_completion',
            __( 'Points Per Blueprint Completion', 'skillsprint' ),
            array( $this, 'points_per_blueprint_completion_callback' ),
            'skillsprint_settings',
            'skillsprint_gamification_settings'
        );
        
        add_settings_field(
            'streak_bonus_multiplier',
            __( 'Streak Bonus Multiplier', 'skillsprint' ),
            array( $this, 'streak_bonus_multiplier_callback' ),
            'skillsprint_settings',
            'skillsprint_gamification_settings'
        );
        
        // Quiz Settings
        add_settings_section(
            'skillsprint_quiz_settings',
            __( 'Quiz Settings', 'skillsprint' ),
            array( $this, 'quiz_settings_section_callback' ),
            'skillsprint_settings'
        );
        
        add_settings_field(
            'default_quiz_pass_score',
            __( 'Default Quiz Pass Score (%)', 'skillsprint' ),
            array( $this, 'default_quiz_pass_score_callback' ),
            'skillsprint_settings',
            'skillsprint_quiz_settings'
        );
        
        add_settings_field(
            'max_quiz_attempts',
            __( 'Default Max Quiz Attempts', 'skillsprint' ),
            array( $this, 'max_quiz_attempts_callback' ),
            'skillsprint_settings',
            'skillsprint_quiz_settings'
        );
    }
    
    /**
     * Sanitize settings.
     *
     * @since    1.0.0
     * @param    array $input The settings input.
     * @return   array The sanitized settings.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();
        
        // General Settings
        $sanitized['days_free_access'] = isset( $input['days_free_access'] ) ? intval( $input['days_free_access'] ) : 2;
        $sanitized['strict_progression'] = isset( $input['strict_progression'] ) ? (bool) $input['strict_progression'] : true;
        
        // Gamification Settings
        $sanitized['gamification_enabled'] = isset( $input['gamification_enabled'] ) ? (bool) $input['gamification_enabled'] : true;
        $sanitized['leaderboard_enabled'] = isset( $input['leaderboard_enabled'] ) ? (bool) $input['leaderboard_enabled'] : true;
        $sanitized['points_per_day_completion'] = isset( $input['points_per_day_completion'] ) ? intval( $input['points_per_day_completion'] ) : 10;
        $sanitized['points_per_quiz_correct'] = isset( $input['points_per_quiz_correct'] ) ? intval( $input['points_per_quiz_correct'] ) : 5;
        $sanitized['points_per_blueprint_completion'] = isset( $input['points_per_blueprint_completion'] ) ? intval( $input['points_per_blueprint_completion'] ) : 50;
        $sanitized['streak_bonus_multiplier'] = isset( $input['streak_bonus_multiplier'] ) ? floatval( $input['streak_bonus_multiplier'] ) : 1.5;
        
        // Quiz Settings
        $sanitized['default_quiz_pass_score'] = isset( $input['default_quiz_pass_score'] ) ? intval( $input['default_quiz_pass_score'] ) : 70;
        $sanitized['max_quiz_attempts'] = isset( $input['max_quiz_attempts'] ) ? intval( $input['max_quiz_attempts'] ) : 3;
        
        return $sanitized;
    }
    
    /**
     * General settings section callback.
     *
     * @since    1.0.0
     */
    public function general_settings_section_callback() {
        echo '<p>' . __( 'Configure general settings for the SkillSprint 7-Day Learning Platform.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Days with free access callback.
     *
     * @since    1.0.0
     */
    public function days_free_access_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['days_free_access'] ) ? $settings['days_free_access'] : 2;
        echo '<input type="number" min="0" max="7" name="skillsprint_settings[days_free_access]" value="' . esc_attr( $value ) . '" /> ';
        echo '<p class="description">' . __( 'Number of days that will be freely accessible without requiring user registration (0-7).', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Strict progression callback.
     *
     * @since    1.0.0
     */
    public function strict_progression_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['strict_progression'] ) ? $settings['strict_progression'] : true;
        echo '<input type="checkbox" name="skillsprint_settings[strict_progression]" value="1" ' . checked( $value, true, false ) . ' /> ';
        echo '<p class="description">' . __( 'If enabled, users must complete each day before accessing the next one.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Gamification settings section callback.
     *
     * @since    1.0.0
     */
    public function gamification_settings_section_callback() {
        echo '<p>' . __( 'Configure gamification features like points, streaks, and achievements.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Gamification enabled callback.
     *
     * @since    1.0.0
     */
    public function gamification_enabled_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['gamification_enabled'] ) ? $settings['gamification_enabled'] : true;
        echo '<input type="checkbox" name="skillsprint_settings[gamification_enabled]" value="1" ' . checked( $value, true, false ) . ' /> ';
        echo '<p class="description">' . __( 'Enable gamification features like points, streaks, and achievements.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Leaderboard enabled callback.
     *
     * @since    1.0.0
     */
    public function leaderboard_enabled_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['leaderboard_enabled'] ) ? $settings['leaderboard_enabled'] : true;
        echo '<input type="checkbox" name="skillsprint_settings[leaderboard_enabled]" value="1" ' . checked( $value, true, false ) . ' /> ';
        echo '<p class="description">' . __( 'Enable the leaderboard to show top learners.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Points per day completion callback.
     *
     * @since    1.0.0
     */
    public function points_per_day_completion_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['points_per_day_completion'] ) ? $settings['points_per_day_completion'] : 10;
        echo '<input type="number" min="0" name="skillsprint_settings[points_per_day_completion]" value="' . esc_attr( $value ) . '" /> ';
        echo '<p class="description">' . __( 'Points awarded for completing a day.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Points per quiz correct callback.
     *
     * @since    1.0.0
     */
    public function points_per_quiz_correct_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['points_per_quiz_correct'] ) ? $settings['points_per_quiz_correct'] : 5;
        echo '<input type="number" min="0" name="skillsprint_settings[points_per_quiz_correct]" value="' . esc_attr( $value ) . '" /> ';
        echo '<p class="description">' . __( 'Points awarded for each correct quiz answer.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Points per blueprint completion callback.
     *
     * @since    1.0.0
     */
    public function points_per_blueprint_completion_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['points_per_blueprint_completion'] ) ? $settings['points_per_blueprint_completion'] : 50;
        echo '<input type="number" min="0" name="skillsprint_settings[points_per_blueprint_completion]" value="' . esc_attr( $value ) . '" /> ';
        echo '<p class="description">' . __( 'Points awarded for completing an entire blueprint.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Streak bonus multiplier callback.
     *
     * @since    1.0.0
     */
    public function streak_bonus_multiplier_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['streak_bonus_multiplier'] ) ? $settings['streak_bonus_multiplier'] : 1.5;
        echo '<input type="number" min="1" step="0.1" name="skillsprint_settings[streak_bonus_multiplier]" value="' . esc_attr( $value ) . '" /> ';
        echo '<p class="description">' . __( 'Multiplier for points when user has an active streak (e.g., 1.5 means 50% bonus).', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Quiz settings section callback.
     *
     * @since    1.0.0
     */
    public function quiz_settings_section_callback() {
        echo '<p>' . __( 'Configure default quiz and assessment settings.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Default quiz pass score callback.
     *
     * @since    1.0.0
     */
    public function default_quiz_pass_score_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['default_quiz_pass_score'] ) ? $settings['default_quiz_pass_score'] : 70;
        echo '<input type="number" min="0" max="100" name="skillsprint_settings[default_quiz_pass_score]" value="' . esc_attr( $value ) . '" /> ';
        echo '<p class="description">' . __( 'Default passing score percentage for quizzes.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Max quiz attempts callback.
     *
     * @since    1.0.0
     */
    public function max_quiz_attempts_callback() {
        $settings = get_option( 'skillsprint_settings' );
        $value = isset( $settings['max_quiz_attempts'] ) ? $settings['max_quiz_attempts'] : 3;
        echo '<input type="number" min="1" name="skillsprint_settings[max_quiz_attempts]" value="' . esc_attr( $value ) . '" /> ';
        echo '<p class="description">' . __( 'Default maximum number of attempts for quizzes.', 'skillsprint' ) . '</p>';
    }
    
    /**
     * Welcome page redirect after activation.
     *
     * @since    1.0.0
     */
    public function welcome_page_redirect() {
        // Check if activation flag is set
        if ( get_transient( 'skillsprint_activation_redirect' ) ) {
            delete_transient( 'skillsprint_activation_redirect' );
            
            // Only redirect if not doing an ajax call
            if ( ! wp_doing_ajax() ) {
                wp_safe_redirect( admin_url( 'admin.php?page=skillsprint' ) );
                exit;
            }
        }
    }
    
    /**
     * Add dashboard widgets.
     *
     * @since    1.0.0
     */
    public function add_dashboard_widgets() {
        if ( current_user_can( 'manage_skillsprint' ) ) {
            wp_add_dashboard_widget(
                'skillsprint_stats_widget',
                __( 'SkillSprint Stats', 'skillsprint' ),
                array( $this, 'render_stats_dashboard_widget' )
            );
        }
    }
    
    /**
     * Render stats dashboard widget.
     *
     * @since    1.0.0
     */
    public function render_stats_dashboard_widget() {
        global $wpdb;
        
        // Count blueprints
        $blueprint_count = wp_count_posts( 'blueprint' )->publish;
        
        // Count learners (users who have progress records)
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        $learner_count = $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM $progress_table" );
        
        // Count completed blueprints
        $completed_blueprint_count = $wpdb->get_var( 
            $wpdb->prepare(
                "SELECT COUNT(*) FROM (
                    SELECT blueprint_id, user_id, COUNT(*) as days_completed, 
                    (SELECT COUNT(*) FROM %i as t2 WHERE t2.blueprint_id = t1.blueprint_id AND progress_status = 'completed' GROUP BY blueprint_id) as total_days
                    FROM %i as t1 
                    WHERE progress_status = 'completed'
                    GROUP BY blueprint_id, user_id
                    HAVING days_completed = total_days
                ) as completed_blueprints",
                $progress_table,
                $progress_table
            )
        );
        
        // Get recent activity
        $recent_activity = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.user_id, p.blueprint_id, p.day_number, p.progress_status, p.date_completed
                FROM %i as p
                WHERE p.date_completed IS NOT NULL
                ORDER BY p.date_completed DESC
                LIMIT 5",
                $progress_table
            )
        );
        
        include SKILLSPRINT_PLUGIN_DIR . 'admin/partials/dashboard-widget.php';
    }
    
    /**
     * Add custom columns to blueprint list.
     *
     * @since    1.0.0
     * @param    array $columns The existing columns.
     * @return   array The modified columns.
     */
    public function add_blueprint_columns( $columns ) {
        $new_columns = array();
        
        foreach ( $columns as $key => $value ) {
            if ( $key === 'date' ) {
                $new_columns['difficulty'] = __( 'Difficulty', 'skillsprint' );
                $new_columns['learners'] = __( 'Learners', 'skillsprint' );
                $new_columns['completion_rate'] = __( 'Completion Rate', 'skillsprint' );
            }
            
            $new_columns[$key] = $value;
        }
        
        return $new_columns;
    }
    
    /**
     * Display blueprint column content.
     *
     * @since    1.0.0
     * @param    string $column  The column name.
     * @param    int    $post_id The post ID.
     */
    public function display_blueprint_column_content( $column, $post_id ) {
        global $wpdb;
        
        switch ( $column ) {
            case 'difficulty':
                $difficulty = SkillSprint_Blueprint::get_blueprint_difficulty( $post_id );
                echo '<span class="difficulty-badge difficulty-' . esc_attr( $difficulty['slug'] ) . '">' . esc_html( $difficulty['name'] ) . '</span>';
                break;
            
            case 'learners':
                $progress_table = $wpdb->prefix . 'skillsprint_progress';
                $count = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(DISTINCT user_id) FROM $progress_table WHERE blueprint_id = %d",
                        $post_id
                    )
                );
                echo intval($count);
                break;
            
            case 'completion_rate':
                $progress_table = $wpdb->prefix . 'skillsprint_progress';
                
                // Get total users who started this blueprint
                $total_users = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(DISTINCT user_id) FROM $progress_table WHERE blueprint_id = %d",
                        $post_id
                    )
                );
                
                if ( $total_users > 0 ) {
                    // Get number of days in this blueprint
                    $days_data = SkillSprint_DB::get_blueprint_days_data( $post_id );
                    $total_days = count( $days_data );
                    
                    if ( $total_days > 0 ) {
                        // Get users who completed all days
                        $completed_users = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM (
                                    SELECT user_id, COUNT(*) as days_completed 
                                    FROM $progress_table 
                                    WHERE blueprint_id = %d AND progress_status = 'completed'
                                    GROUP BY user_id
                                    HAVING days_completed = %d
                                ) as completed_users",
                                $post_id,
                                $total_days
                            )
                        );
                        
                        $completion_rate = round( ( $completed_users / $total_users ) * 100 );
                        echo $completion_rate . '%';
                    } else {
                        echo '0%';
                    }
                } else {
                    echo '0%';
                }
                break;
        }
    }

}