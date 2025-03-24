<?php
/**
 * Debug file for SkillSprint
 */

// Include WordPress
require_once '../../../wp-load.php';

echo '<h1>SkillSprint Debug</h1>';

try {
    echo '<p>Testing file includes...</p>';
    
    // Try including files one by one
    echo '<ul>';
    
    // Test includes
    $files_to_test = array(
        'includes/class-skillsprint-loader.php',
        'includes/class-skillsprint-i18n.php',
        'includes/class-skillsprint-post-types.php',
        'includes/class-skillsprint-db.php',
        'includes/class-skillsprint-blueprint.php',
        'includes/class-skillsprint-quiz.php',
        'includes/class-skillsprint-progress.php',
        'includes/class-skillsprint-gamification.php',
        'includes/class-skillsprint-access.php',
        'includes/class-skillsprint-dashboard.php',
        'includes/class-skillsprint-activator.php',
        'includes/class-skillsprint-deactivator.php',
        'admin/class-skillsprint-admin.php',
        'public/class-skillsprint-public.php'
    );
    
    foreach ($files_to_test as $file) {
        echo '<li>Testing: ' . $file . ' ... ';
        $full_path = SKILLSPRINT_PLUGIN_DIR . $file;
        
        if (file_exists($full_path)) {
            include_once $full_path;
            echo '<span style="color:green">SUCCESS</span>';
        } else {
            echo '<span style="color:red">FILE NOT FOUND</span>';
        }
        echo '</li>';
    }
    
    echo '</ul>';
    
    echo '<p>Testing complete!</p>';
    
} catch (Exception $e) {
    echo '<p style="color:red">ERROR: ' . $e->getMessage() . '</p>';
}