/**
 * Blueprint builder JavaScript
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/js
 */

(function($) {
    'use strict';

    /**
     * Blueprint Builder class
     */
    class BlueprintBuilder {
        /**
         * Constructor
         */
        constructor() {
            this.init();
        }
        
        /**
         * Initialize the builder
         */
        init() {
            this.initTabs();
            this.initResourceButtons();
            this.initSaveBlueprint();
            this.initMediaUploader();
        }
        
        /**
         * Initialize day tabs
         */
        initTabs() {
            // Show first tab by default
            $('.skillsprint-blueprint-day-tab').first().addClass('active');
            $('.skillsprint-blueprint-day-content').first().addClass('active');
            
            // Tab click handler
            $('.skillsprint-blueprint-day-tab').on('click', function() {
                const dayNumber = $(this).data('day');
                
                // Activate tab
                $('.skillsprint-blueprint-day-tab').removeClass('active');
                $(this).addClass('active');
                
                // Show content
                $('.skillsprint-blueprint-day-content').removeClass('active');
                $(`.skillsprint-blueprint-day-content[data-day="${dayNumber}"]`).addClass('active');
            });
        }
        
        /**
         * Initialize resource buttons
         */
        initResourceButtons() {
            // Add resource button
            $('.skillsprint-add-resource').on('click', function() {
                const dayNumber = $(this).data('day');
                const $container = $(this).prev('.skillsprint-resources-container');
                const resourceIndex = $container.children().length;
                
                const $newResource = $(`
                    <div class="skillsprint-resource-item">
                        <div class="skillsprint-resource-fields">
                            <input type="text" name="skillsprint_days_data[${dayNumber - 1}][resources][${resourceIndex}][title]" placeholder="Resource Title" class="regular-text">
                            <input type="text" name="skillsprint_days_data[${dayNumber - 1}][resources][${resourceIndex}][url]" placeholder="Resource URL" class="regular-text">
                            <select name="skillsprint_days_data[${dayNumber - 1}][resources][${resourceIndex}][type]">
                                <option value="link">Link</option>
                                <option value="file">Document</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <button type="button" class="button button-secondary skillsprint-remove-resource">Remove</button>
                    </div>
                `);
                
                $container.append($newResource);
            });
            
            // Remove resource button
            $(document).on('click', '.skillsprint-remove-resource', function() {
                $(this).closest('.skillsprint-resource-item').remove();
            });
        }
        
        /**
         * Initialize save blueprint
         */
        initSaveBlueprint() {
            // Blueprint days save
            $('#post').on('submit', function() {
                // Save all TinyMCE editors
                if (typeof tinyMCE !== 'undefined') {
                    for (let i = 1; i <= 7; i++) {
                        const editorId = `skillsprint_day_${i}_content`;
                        const editor = tinyMCE.get(editorId);
                        
                        if (editor) {
                            editor.save();
                        }
                    }
                }
                
                return true;
            });
            
            // AJAX save blueprint data
            $('.skillsprint-save-blueprint').on('click', function() {
                const $button = $(this);
                const $status = $button.next('.skillsprint-save-status');
                
                // Disable button and show loading state
                $button.prop('disabled', true).text(skillsprint.i18n.saving);
                $status.removeClass('success error').empty();
                
                // Save all TinyMCE editors
                if (typeof tinyMCE !== 'undefined') {
                    for (let i = 1; i <= 7; i++) {
                        const editorId = `skillsprint_day_${i}_content`;
                        const editor = tinyMCE.get(editorId);
                        
                        if (editor) {
                            editor.save();
                        }
                    }
                }
                
                // Collect all days data
                const daysData = [];
                
                $('.skillsprint-blueprint-day-content').each(function() {
                    const $dayContent = $(this);
                    const dayNumber = $dayContent.data('day');
                    
                    const dayData = {
                        day_number: dayNumber,
                        title: $dayContent.find(`#skillsprint_day_${dayNumber}_title`).val(),
                        learning_objectives: $dayContent.find(`#skillsprint_day_${dayNumber}_learning_objectives`).val(),
                        content: $dayContent.find(`#skillsprint_day_${dayNumber}_content`).val(),
                        resources: [],
                        quiz_id: $dayContent.find('.skillsprint-quiz-id').val()
                    };
                    
                    // Collect resources
                    $dayContent.find('.skillsprint-resource-item').each(function() {
                        const $resource = $(this);
                        
                        dayData.resources.push({
                            title: $resource.find('input[name^="skillsprint_days_data"][name$="[title]"]').val(),
                            url: $resource.find('input[name^="skillsprint_days_data"][name$="[url]"]').val(),
                            type: $resource.find('select[name^="skillsprint_days_data"][name$="[type]"]').val()
                        });
                    });
                    
                    daysData.push(dayData);
                });
                
                // Send AJAX request
                $.post(skillsprint.ajax_url, {
                    action: 'skillsprint_save_blueprint_data',
                    blueprint_id: skillsprint.blueprint_id,
                    days_data: daysData,
                    nonce: skillsprint.nonce
                }, function(response) {
                    if (response.success) {
                        $status.addClass('success').text(response.data.message);
                    } else {
                        $status.addClass('error').text(response.data.message);
                    }
                    
                    $button.prop('disabled', false).text(skillsprint.i18n.save_blueprint);
                    
                    // Hide status after 3 seconds
                    setTimeout(function() {
                        $status.removeClass('success error').empty();
                    }, 3000);
                }).fail(function() {
                    $status.addClass('error').text(skillsprint.i18n.save_error);
                    $button.prop('disabled', false).text(skillsprint.i18n.save_blueprint);
                });
            });
        }
        
        /**
         * Initialize media uploader
         */
        initMediaUploader() {
            // Media upload button
            $(document).on('click', '.skillsprint-media-upload', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const $urlField = $button.siblings('input[type="text"]');
                
                // Create media frame
                const frame = wp.media({
                    title: skillsprint.i18n.choose_file,
                    button: {
                        text: skillsprint.i18n.select_file
                    },
                    multiple: false
                });
                
                // When a file is selected
                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    $urlField.val(attachment.url);
                });
                
                // Open media frame
                frame.open();
            });
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.skillsprint-blueprint-builder').length) {
            new BlueprintBuilder();
        }
    });

})(jQuery);