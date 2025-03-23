<?php
/**
 * Define the internationalization functionality.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/includes
 */
class SkillSprint_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'skillsprint',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}