<?php
/**
 * Blueprint functionality for the plugin.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Blueprint functionality for the plugin.
 *
 * Handles all blueprint-related functionality.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_Blueprint {

    /**
     * Get all blueprint data including days.
     *
     * @since    1.0.0
     * @param    int     $blueprint_id The blueprint ID.
     * @return   array   Blueprint data.
     */
    public static function get_blueprint_data( $blueprint_id ) {
        $blueprint = get_post( $blueprint_id );
        
        if ( ! $blueprint || $blueprint->post_type !== 'blueprint' ) {
            return false;
        }
        
        $days_data = SkillSprint_DB::get_blueprint_days_data( $blueprint_id );
        
        // Basic blueprint data
        $data = array(
            'id' => $blueprint_id,
            'title' => $blueprint->post_title,
            'excerpt' => $blueprint->post_excerpt,
            'content' => $blueprint->post_content,
            'author' => get_the_author_meta( 'display_name', $blueprint->post_author ),
            'author_id' => $blueprint->post_author,
            'date_created' => $blueprint->post_date,
            'date_modified' => $blueprint->post_modified,
            'thumbnail' => get_the_post_thumbnail_url( $blueprint_id, 'large' ),
            'permalink' => get_permalink( $blueprint_id ),
            'days' => $days_data,
            'difficulty' => self::get_blueprint_difficulty( $blueprint_id ),
            'categories' => self::get_blueprint_categories( $blueprint_id ),
            'tags' => self::get_blueprint_tags( $blueprint_id ),
            'meta' => self::get_blueprint_meta( $blueprint_id )
        );
        
        return $data;
    }
    
    /**
     * Get blueprint difficulty.
     *
     * @since    1.0.0
     * @param    int     $blueprint_id The blueprint ID.
     * @return   array   Difficulty info.
     */
    public static function get_blueprint_difficulty( $blueprint_id ) {
        $difficulty_terms = wp_get_post_terms( $blueprint_id, 'blueprint_difficulty' );
        
        if ( ! empty( $difficulty_terms ) && ! is_wp_error( $difficulty_terms ) ) {
            $difficulty = $difficulty_terms[0];
            
            return array(
                'id' => $difficulty->term_id,
                'name' => $difficulty->name,
                'slug' => $difficulty->slug,
                'description' => $difficulty->description
            );
        }
        
        return array(
            'id' => 0,
            'name' => __('Not specified', 'skillsprint'),
            'slug' => 'not-specified',
            'description' => ''
        );
    }
    
    /**
     * Get blueprint categories.
     *
     * @since    1.0.0
     * @param    int     $blueprint_id The blueprint ID.
     * @return   array   Categories info.
     */
    public static function get_blueprint_categories( $blueprint_id ) {
        $categories = wp_get_post_terms( $blueprint_id, 'blueprint_category' );
        $result = array();
        
        if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
            foreach ( $categories as $category ) {
                $result[] = array(
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'permalink' => get_term_link( $category )
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Get blueprint tags.
     *
     * @since    1.0.0
     * @param    int     $blueprint_id The blueprint ID.
     * @return   array   Tags info.
     */
    public static function get_blueprint_tags( $blueprint_id ) {
        $tags = wp_get_post_terms( $blueprint_id, 'blueprint_tag' );
        $result = array();
        
        if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
            foreach ( $tags as $tag ) {
                $result[] = array(
                    'id' => $tag->term_id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'description' => $tag->description,
                    'permalink' => get_term_link( $tag )
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Get blueprint meta.
     *
     * @since    1.0.0
     * @param    int     $blueprint_id The blueprint ID.
     * @return   array   Meta info.
     */
    public static function get_blueprint_meta( $blueprint_id ) {
        $meta = array();
        
        // Common meta fields
        $meta_fields = array(
            'estimated_completion_time',
            'recommended_background',
            'prerequisites',
            'what_youll_learn',
            'resources',
        );
        
        foreach ( $meta_fields as $field ) {
            $meta[$field] = get_post_meta( $blueprint_id, '_skillsprint_' . $field, true );
        }
        
        return $meta;
    }
    
    /**
     * Get a specific day's content.
     *
     * @since    1.0.0
     * @param    int     $blueprint_id The blueprint ID.
     * @param    int     $day_number   The day number.
     * @return   string  Day content.
     */
    public static function get_day_content( $blueprint_id, $day_number ) {
        $day_data = SkillSprint_DB::get_blueprint_day_data( $blueprint_id, $day_number );
        
        if ( ! $day_data || ! isset( $day_data['content'] ) ) {
            return '';
        }
        
        return apply_filters( 'the_content', $day_data['content'] );
    }
    
    /**
     * Get a day's quiz data.
     *
     * @since    1.0.0
     * @param    int     $blueprint_id The blueprint ID.
     * @param    int     $day_number   The day number.
     * @return   array   Quiz data.
     */
    public static function get_day_quiz( $blueprint_id, $day_number ) {
        $day_data = SkillSprint_DB::get_blueprint_day_data( $blueprint_id, $day_number );
        
        if ( ! $day_data || ! isset( $day_data['quiz_id'] ) ) {
            return null;
        }
        
        $quiz_id = $day_data['quiz_id'];
        
        if ( empty( $quiz_id ) ) {
            return null;
        }
        
        $quiz_data = get_post_meta( $blueprint_id, '_skillsprint_quiz_' . $quiz_id, true );
        
        if ( ! $quiz_data ) {
            return null;
        }
        
        return $quiz_data;
    }
    
    /**
     * Get all user's accessible blueprints.
     *
     * @since    1.0.0
     * @param    int     $user_id The user ID.
     * @return   array   Accessible blueprints.
     */
    public static function get_user_accessible_blueprints( $user_id ) {
        // Query all blueprints
        $args = array(
            'post_type' => 'blueprint',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        $blueprints = get_posts( $args );
        $accessible = array();
        
        foreach ( $blueprints as $blueprint ) {
            if ( apply_filters( 'skillsprint_can_access_blueprint', true, $user_id, $blueprint->ID ) ) {
                $accessible[] = array(
                    'id' => $blueprint->ID,
                    'title' => $blueprint->post_title,
                    'excerpt' => $blueprint->post_excerpt,
                    'thumbnail' => get_the_post_thumbnail_url( $blueprint->ID, 'thumbnail' ),
                    'permalink' => get_permalink( $blueprint->ID ),
                    'difficulty' => self::get_blueprint_difficulty( $blueprint->ID ),
                    'progress' => $user_id ? SkillSprint_DB::get_blueprint_completion_percentage( $user_id, $blueprint->ID ) : 0
                );
            }
        }
        
        return $accessible;
    }
    
    /**
     * Get user's in-progress blueprints.
     *
     * @since    1.0.0
     * @param    int     $user_id The user ID.
     * @param    int     $limit   Number of blueprints to return.
     * @return   array   In-progress blueprints.
     */
    public static function get_user_in_progress_blueprints( $user_id, $limit = 5 ) {
        global $wpdb;
        
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        
        // Get blueprints with progress
        $blueprint_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT blueprint_id FROM $progress_table WHERE user_id = %d",
                $user_id
            )
        );
        
        if ( empty( $blueprint_ids ) ) {
            return array();
        }
        
        // Get posts for these blueprints
        $args = array(
            'post_type' => 'blueprint',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'post__in' => $blueprint_ids,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        $blueprints = get_posts( $args );
        $result = array();
        
        foreach ( $blueprints as $blueprint ) {
            $progress_percentage = SkillSprint_DB::get_blueprint_completion_percentage( $user_id, $blueprint->ID );
            
            // Only include if not 100% completed
            if ( $progress_percentage < 100 ) {
                $result[] = array(
                    'id' => $blueprint->ID,
                    'title' => $blueprint->post_title,
                    'excerpt' => $blueprint->post_excerpt,
                    'thumbnail' => get_the_post_thumbnail_url( $blueprint->ID, 'thumbnail' ),
                    'permalink' => get_permalink( $blueprint->ID ),
                    'difficulty' => self::get_blueprint_difficulty( $blueprint->ID ),
                    'progress' => $progress_percentage,
                    'last_day_accessed' => self::get_last_day_accessed( $user_id, $blueprint->ID )
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Get user's completed blueprints.
     *
     * @since    1.0.0
     * @param    int     $user_id The user ID.
     * @param    int     $limit   Number of blueprints to return.
     * @return   array   Completed blueprints.
     */
    public static function get_user_completed_blueprints( $user_id, $limit = 5 ) {
        global $wpdb;
        
        $progress_table = $wpdb->prefix . 'skillsprint_progress';
        
        // Get blueprints with progress
        $blueprint_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT blueprint_id FROM $progress_table WHERE user_id = %d",
                $user_id
            )
        );
        
        if ( empty( $blueprint_ids ) ) {
            return array();
        }
        
        // Get posts for these blueprints
        $args = array(
            'post_type' => 'blueprint',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'post__in' => $blueprint_ids,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        $blueprints = get_posts( $args );
        $result = array();
        
        foreach ( $blueprints as $blueprint ) {
            $progress_percentage = SkillSprint_DB::get_blueprint_completion_percentage( $user_id, $blueprint->ID );
            
            // Only include if 100% completed
            if ( $progress_percentage == 100 ) {
                $result[] = array(
                    'id' => $blueprint->ID,
                    'title' => $blueprint->post_title,
                    'excerpt' => $blueprint->post_excerpt,
                    'thumbnail' => get_the_post_thumbnail_url( $blueprint->ID, 'thumbnail' ),
                    'permalink' => get_permalink( $blueprint->ID ),
                    'difficulty' => self::get_blueprint_difficulty( $blueprint->ID ),
                    'completion_date' => self::get_completion_date( $user_id, $blueprint->ID )
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Get recommended blueprints for a user.
     *
     * @since    1.0.0
     * @param    int     $user_id The user ID.
     * @param    int     $limit   Number of blueprints to return.
     * @return   array   Recommended blueprints.
     */
    public static function get_recommended_blueprints( $user_id, $limit = 3 ) {
        // Get user's completed and in-progress blueprint IDs
        $in_progress = self::get_user_in_progress_blueprints( $user_id, -1 );
        $completed = self::get_user_completed_blueprints( $user_id, -1 );
        
        $current_blueprint_ids = array();
        
        foreach ( $in_progress as $blueprint ) {
            $current_blueprint_ids[] = $blueprint['id'];
        }
        
        foreach ( $completed as $blueprint ) {
            $current_blueprint_ids[] = $blueprint['id'];
        }
        
        // Get user's categories of interest based on completed blueprints
        $user_categories = array();
        foreach ( $completed as $blueprint ) {
            $categories = wp_get_post_terms( $blueprint['id'], 'blueprint_category', array( 'fields' => 'ids' ) );
            if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                $user_categories = array_merge( $user_categories, $categories );
            }
        }
        
        // Get user's tags of interest based on completed blueprints
        $user_tags = array();
        foreach ( $completed as $blueprint ) {
            $tags = wp_get_post_terms( $blueprint['id'], 'blueprint_tag', array( 'fields' => 'ids' ) );
            if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
                $user_tags = array_merge( $user_tags, $tags );
            }
        }
        
        // Count frequencies
        $user_categories = array_count_values( $user_categories );
        $user_tags = array_count_values( $user_tags );
        
        // Sort by frequency
        arsort( $user_categories );
        arsort( $user_tags );
        
        // Get top categories and tags
        $top_categories = array_slice( array_keys( $user_categories ), 0, 3 );
        $top_tags = array_slice( array_keys( $user_tags ), 0, 5 );
        
        // Query for recommended blueprints
        $args = array(
            'post_type' => 'blueprint',
            'post_status' => 'publish',
            'posts_per_page' => $limit * 2, // Get a few more to filter
            'post__not_in' => $current_blueprint_ids,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Add tax query if we have user preferences
        if ( ! empty( $top_categories ) || ! empty( $top_tags ) ) {
            $args['tax_query'] = array(
                'relation' => 'OR',
            );
            
            if ( ! empty( $top_categories ) ) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'blueprint_category',
                    'field' => 'term_id',
                    'terms' => $top_categories,
                );
            }
            
            if ( ! empty( $top_tags ) ) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'blueprint_tag',
                    'field' => 'term_id',
                    'terms' => $top_tags,
                );
            }
        }
        
        $recommendations = get_posts( $args );
        
        // If not enough recommendations, get popular ones
        if ( count( $recommendations ) < $limit ) {
            $needed = $limit - count( $recommendations );
            $recommended_ids = wp_list_pluck( $recommendations, 'ID' );
            $exclude_ids = array_merge( $current_blueprint_ids, $recommended_ids );
            
            $popular_args = array(
                'post_type' => 'blueprint',
                'post_status' => 'publish',
                'posts_per_page' => $needed,
                'post__not_in' => $exclude_ids,
                'meta_key' => '_skillsprint_view_count',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
            );
            
            $popular = get_posts( $popular_args );
            
            if ( ! empty( $popular ) ) {
                $recommendations = array_merge( $recommendations, $popular );
            }
        }
        
        // If still not enough, add new ones
        if ( count( $recommendations ) < $limit ) {
            $needed = $limit - count( $recommendations );
            $recommended_ids = wp_list_pluck( $recommendations, 'ID' );
            $exclude_ids = array_merge( $current_blueprint_ids, $recommended_ids );
            
            $new_args = array(
                'post_type' => 'blueprint',
                'post_status' => 'publish',
                'posts_per_page' => $needed,
                'post__not_in' => $exclude_ids,
                'orderby' => 'date',
                'order' => 'DESC',
            );
            
            $new = get_posts( $new_args );
            
            if ( ! empty( $new ) ) {
                $recommendations = array_merge( $recommendations, $new );
            }
        }
        
        // Format recommendations
        $result = array();
        
        foreach ( $recommendations as $blueprint ) {
            if ( count( $result ) >= $limit ) {
                break;
            }
            
            if ( apply_filters( 'skillsprint_can_access_blueprint', true, $user_id, $blueprint->ID ) ) {
                $result[] = array(
                    'id' => $blueprint->ID,
                    'title' => $blueprint->post_title,
                    'excerpt' => $blueprint->post_excerpt,
                    'thumbnail' => get_the_post_thumbnail_url( $blueprint->ID, 'thumbnail' ),
                    'permalink' => get_permalink( $blueprint->ID ),
                    'difficulty' => self::get_blueprint_difficulty( $blueprint->ID ),
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Get the last day accessed for a blueprint.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @return   int     Last day accessed.
     */
    public static function get_last_day_accessed( $user_id, $blueprint_id ) {
        $progress = SkillSprint_DB::get_user_blueprint_progress( $user_id, $blueprint_id );
        
        $last_day = 1;
        
        foreach ( $progress as $day_progress ) {
            if ( $day_progress['progress_status'] != 'not_started' && $day_progress['day_number'] > $last_day ) {
                $last_day = $day_progress['day_number'];
            }
        }
        
        return $last_day;
    }
    
    /**
     * Get the completion date for a blueprint.
     *
     * @since    1.0.0
     * @param    int     $user_id      The user ID.
     * @param    int     $blueprint_id The blueprint ID.
     * @return   string   Completion date.
     */
    public static function get_completion_date( $user_id, $blueprint_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'skillsprint_progress';
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(date_completed) FROM $table_name WHERE user_id = %d AND blueprint_id = %d",
                $user_id,
                $blueprint_id
            )
        );
        
        return $result ? $result : '';
    }
    
    /**
     * Increment the view count for a blueprint.
     *
     * @since    1.0.0
     * @param    int     $blueprint_id The blueprint ID.
     * @return   void
     */
    public static function increment_view_count( $blueprint_id ) {
        $count = get_post_meta( $blueprint_id, '_skillsprint_view_count', true );
        
        if ( empty( $count ) ) {
            $count = 0;
        }
        
        $count++;
        
        update_post_meta( $blueprint_id, '_skillsprint_view_count', $count );
    }
    
    /**
     * Get popular blueprints.
     *
     * @since    1.0.0
     * @param    int     $limit Number of blueprints to return.
     * @return   array   Popular blueprints.
     */
    public static function get_popular_blueprints( $limit = 5 ) {
        $args = array(
            'post_type' => 'blueprint',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_key' => '_skillsprint_view_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
        );
        
        $blueprints = get_posts( $args );
        $result = array();
        
        foreach ( $blueprints as $blueprint ) {
            $result[] = array(
                'id' => $blueprint->ID,
                'title' => $blueprint->post_title,
                'excerpt' => $blueprint->post_excerpt,
                'thumbnail' => get_the_post_thumbnail_url( $blueprint->ID, 'thumbnail' ),
                'permalink' => get_permalink( $blueprint->ID ),
                'difficulty' => self::get_blueprint_difficulty( $blueprint->ID ),
                'view_count' => get_post_meta( $blueprint->ID, '_skillsprint_view_count', true )
            );
        }
        
        return $result;
    }
}