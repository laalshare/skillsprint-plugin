/**
 * Admin scripts for the plugin
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/js
 */

(function($) {
    'use strict';

    /**
     * Initialize admin functionality
     */
    function init() {
        initUserProgressPage();
        initBlueprints();
    }

    /**
     * Initialize user progress page functionality
     */
    function initUserProgressPage() {
        // Check if we're on the user progress page
        if ($('#skillsprint-user-progress-page').length) {
            // Load user progress on user selection
            $('#skillsprint-user-select').on('change', function() {
                const userId = $(this).val();
                const blueprintId = $('#skillsprint-blueprint-select').val();
                
                if (userId) {
                    loadUserProgress(userId, blueprintId);
                } else {
                    $('#skillsprint-user-progress-container').empty();
                }
            });
            
            // Load blueprint progress on blueprint selection
            $('#skillsprint-blueprint-select').on('change', function() {
                const userId = $('#skillsprint-user-select').val();
                const blueprintId = $(this).val();
                
                if (userId) {
                    loadUserProgress(userId, blueprintId);
                }
            });
            
            // Reset progress confirmation
            $(document).on('click', '.skillsprint-reset-progress-button', function(e) {
                e.preventDefault();
                
                const userId = $(this).data('user');
                const blueprintId = $(this).data('blueprint');
                
                // Confirm reset
                if (confirm(skillsprint.i18n.confirm_reset_progress)) {
                    resetUserProgress(userId, blueprintId);
                }
            });
        }
    }
    
    /**
     * Initialize blueprint list functionality
     */
    function initBlueprints() {
        // Initialize select2 on blueprint difficulty selector
        if ($('.skillsprint-blueprint-difficulty-select').length) {
            $('.skillsprint-blueprint-difficulty-select').select2({
                placeholder: skillsprint.i18n.select_difficulty,
                allowClear: true
            });
        }
    }
    
    /**
     * Load user progress
     * 
     * @param {number} userId User ID
     * @param {number} blueprintId Blueprint ID (optional)
     */
    function loadUserProgress(userId, blueprintId) {
        // Show loading indicator
        $('#skillsprint-user-progress-container').html('<div class="spinner is-active"></div>');
        
        // Send AJAX request
        $.post(ajaxurl, {
            action: 'skillsprint_get_user_progress',
            user_id: userId,
            blueprint_id: blueprintId || '',
            nonce: skillsprint.nonce
        }, function(response) {
            if (response.success) {
                renderUserProgress(response.data);
            } else {
                $('#skillsprint-user-progress-container').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
            }
        }).fail(function() {
            $('#skillsprint-user-progress-container').html('<div class="notice notice-error"><p>' + skillsprint.i18n.ajax_error + '</p></div>');
        });
    }
    
    /**
     * Render user progress
     * 
     * @param {object} data Progress data
     */
    function renderUserProgress(data) {
        const $container = $('#skillsprint-user-progress-container');
        $container.empty();
        
        // User details
        const $userDetails = $('<div class="skillsprint-user-details"></div>');
        
        $userDetails.append(`
            <div class="skillsprint-user-avatar">
                <img src="${data.user.avatar}" alt="${data.user.name}">
            </div>
            <div class="skillsprint-user-info">
                <h2 class="skillsprint-user-name">${data.user.name}</h2>
                <div class="skillsprint-user-meta">${data.user.email}</div>
                <div class="skillsprint-user-stats">
                    <div class="skillsprint-user-stat">
                        <span class="skillsprint-user-stat-label">Total Points:</span>
                        <span class="skillsprint-user-stat-value">${data.total_points || 0}</span>
                    </div>
                    <div class="skillsprint-user-stat">
                        <span class="skillsprint-user-stat-label">Current Streak:</span>
                        <span class="skillsprint-user-stat-value">${data.streak_info.current_streak || 0} days</span>
                    </div>
                    <div class="skillsprint-user-stat">
                        <span class="skillsprint-user-stat-label">Best Streak:</span>
                        <span class="skillsprint-user-stat-value">${data.streak_info.longest_streak || 0} days</span>
                    </div>
                </div>
            </div>
        `);
        
        $container.append($userDetails);
        
        // If specific blueprint is selected
        if (data.blueprint) {
            const $blueprintProgress = $('<div class="skillsprint-blueprint-progress-container"></div>');
            
            $blueprintProgress.append(`
                <div class="skillsprint-blueprint-progress-header">
                    <h3 class="skillsprint-blueprint-progress-title">${data.blueprint.title}</h3>
                    <div class="skillsprint-blueprint-progress-meta">
                        <a href="${data.blueprint.permalink}" target="_blank">View Blueprint</a>
                        <span class="skillsprint-separator">|</span>
                        Completion: ${data.completion_percentage}%
                    </div>
                </div>
                <div class="skillsprint-blueprint-days-progress">
                    ${renderDaysProgress(data.progress)}
                </div>
                <a href="#" class="skillsprint-reset-progress-button" data-user="${data.user.id}" data-blueprint="${data.blueprint.id}">
                    Reset Progress for this Blueprint
                </a>
            `);
            
            $container.append($blueprintProgress);
        } else {
            // Progress overview for all blueprints
            const $inProgressBlueprintsContainer = $('<div class="skillsprint-blueprints-container"></div>');
            
            $inProgressBlueprintsContainer.append('<h3 class="skillsprint-blueprints-title">In-Progress Blueprints</h3>');
            
            if (data.in_progress && data.in_progress.length) {
                const $blueprintsList = $('<div class="skillsprint-blueprints-list"></div>');
                
                data.in_progress.forEach(function(blueprint) {
                    $blueprintsList.append(`
                        <div class="skillsprint-blueprint-item">
                            <div class="skillsprint-blueprint-item-header">
                                <h4 class="skillsprint-blueprint-item-title">
                                    <a href="${blueprint.permalink}" target="_blank">${blueprint.title}</a>
                                </h4>
                                <div class="skillsprint-blueprint-progress">
                                    <div class="skillsprint-progress-bar-container">
                                        <div class="skillsprint-progress-bar" style="width: ${blueprint.progress}%"></div>
                                    </div>
                                    <span class="skillsprint-progress-text">${blueprint.progress}%</span>
                                </div>
                            </div>
                            <div class="skillsprint-blueprint-item-footer">
                                <span class="difficulty-badge difficulty-${blueprint.difficulty.slug}">${blueprint.difficulty.name}</span>
                                <a href="#" class="skillsprint-view-progress-button" data-user="${data.user.id}" data-blueprint="${blueprint.id}">
                                    View Detailed Progress
                                </a>
                                <a href="#" class="skillsprint-reset-progress-button" data-user="${data.user.id}" data-blueprint="${blueprint.id}">
                                    Reset Progress
                                </a>
                            </div>
                        </div>
                    `);
                });
                
                $inProgressBlueprintsContainer.append($blueprintsList);
            } else {
                $inProgressBlueprintsContainer.append('<p class="skillsprint-no-data">No blueprints in progress.</p>');
            }
            
            $container.append($inProgressBlueprintsContainer);
            
            // Completed blueprints
            const $completedBlueprintsContainer = $('<div class="skillsprint-blueprints-container"></div>');
            
            $completedBlueprintsContainer.append('<h3 class="skillsprint-blueprints-title">Completed Blueprints</h3>');
            
            if (data.completed && data.completed.length) {
                const $blueprintsList = $('<div class="skillsprint-blueprints-list"></div>');
                
                data.completed.forEach(function(blueprint) {
                    $blueprintsList.append(`
                        <div class="skillsprint-blueprint-item">
                            <div class="skillsprint-blueprint-item-header">
                                <h4 class="skillsprint-blueprint-item-title">
                                    <a href="${blueprint.permalink}" target="_blank">${blueprint.title}</a>
                                </h4>
                                <div class="skillsprint-blueprint-completion-date">
                                    Completed on: ${new Date(blueprint.completion_date).toLocaleDateString()}
                                </div>
                            </div>
                            <div class="skillsprint-blueprint-item-footer">
                                <span class="difficulty-badge difficulty-${blueprint.difficulty.slug}">${blueprint.difficulty.name}</span>
                                <a href="#" class="skillsprint-view-progress-button" data-user="${data.user.id}" data-blueprint="${blueprint.id}">
                                    View Detailed Progress
                                </a>
                                <a href="#" class="skillsprint-reset-progress-button" data-user="${data.user.id}" data-blueprint="${blueprint.id}">
                                    Reset Progress
                                </a>
                            </div>
                        </div>
                    `);
                });
                
                $completedBlueprintsContainer.append($blueprintsList);
            } else {
                $completedBlueprintsContainer.append('<p class="skillsprint-no-data">No completed blueprints.</p>');
            }
            
            $container.append($completedBlueprintsContainer);
            
            // View progress button handler
            $('.skillsprint-view-progress-button').on('click', function(e) {
                e.preventDefault();
                
                const userId = $(this).data('user');
                const blueprintId = $(this).data('blueprint');
                
                // Update select value
                $('#skillsprint-blueprint-select').val(blueprintId).trigger('change');
            });
        }
    }
    
    /**
     * Render days progress
     * 
     * @param {array} progress Progress data
     * @return {string} HTML
     */
    function renderDaysProgress(progress) {
        let html = '';
        
        if (progress && progress.length) {
            progress.forEach(function(day) {
                const dayNumber = day.day_number;
                const status = day.progress_status;
                const statusLabel = {
                    'not_started': 'Not Started',
                    'in_progress': 'In Progress',
                    'completed': 'Completed'
                }[status] || 'Unknown';
                
                const date = status === 'completed' ? day.date_completed : (status === 'in_progress' ? day.date_started : '');
                let dateText = '';
                
                if (date) {
                    dateText = new Date(date).toLocaleDateString();
                }
                
                html += `
                    <div class="skillsprint-day-progress-item">
                        <div class="skillsprint-day-number">Day ${dayNumber}</div>
                        <div class="skillsprint-day-status ${status}">${statusLabel}</div>
                        ${dateText ? `<div class="skillsprint-day-date">${dateText}</div>` : ''}
                    </div>
                `;
            });
        } else {
            html = '<p class="skillsprint-no-data">No progress data available.</p>';
        }
        
        return html;
    }
    
    /**
     * Reset user progress
     * 
     * @param {number} userId User ID
     * @param {number} blueprintId Blueprint ID
     */
    function resetUserProgress(userId, blueprintId) {
        // Show loading indicator
        $('#skillsprint-user-progress-container').html('<div class="spinner is-active"></div>');
        
        // Send AJAX request
        $.post(ajaxurl, {
            action: 'skillsprint_reset_user_progress',
            user_id: userId,
            blueprint_id: blueprintId,
            nonce: skillsprint.nonce
        }, function(response) {
            if (response.success) {
                // Reload progress
                loadUserProgress(userId, blueprintId);
            } else {
                $('#skillsprint-user-progress-container').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
            }
        }).fail(function() {
            $('#skillsprint-user-progress-container').html('<div class="notice notice-error"><p>' + skillsprint.i18n.ajax_error + '</p></div>');
        });
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);