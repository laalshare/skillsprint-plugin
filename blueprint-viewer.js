/**
 * Blueprint viewer JavaScript
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/js
 */

(function($) {
    'use strict';

    /**
     * Blueprint Viewer class
     */
    class BlueprintViewer {
        /**
         * Constructor
         * 
         * @param {jQuery} $container The blueprint container element
         */
        constructor($container) {
            this.$container = $container;
            this.blueprintId = $container.data('blueprint');
            this.$dayTabs = $container.find('.skillsprint-day-tab');
            this.$dayContents = $container.find('.skillsprint-day-content');
            this.$progressBar = $container.find('.skillsprint-progress-bar');
            this.$progressPercentage = $container.find('.skillsprint-progress-percentage');
            
            this.init();
        }
        
        /**
         * Initialize the viewer
         */
        init() {
            this.initTabs();
            this.initDayNavigation();
            this.initCompleteButtons();
            this.loadUserProgress();
        }
        
        /**
         * Initialize day tabs
         */
        initTabs() {
            const self = this;
            
            this.$dayTabs.on('click', function(e) {
                e.preventDefault();
                
                const $tab = $(this);
                const dayNumber = $tab.data('day');
                
                // Check if tab is locked
                if ($tab.hasClass('locked')) {
                    if (skillsprint.is_user_logged_in) {
                        self.showLockedDayMessage();
                    } else {
                        self.showLoginRequired();
                    }
                    return;
                }
                
                // Activate tab and content
                self.$dayTabs.removeClass('active');
                $tab.addClass('active');
                
                self.$dayContents.removeClass('active');
                self.$container.find(`.skillsprint-day-content[data-day="${dayNumber}"]`).addClass('active');
                
                // Update URL hash
                window.location.hash = `day-${dayNumber}`;
                
                // Mark day as started if user is logged in
                if (skillsprint.is_user_logged_in && !$tab.hasClass('completed')) {
                    self.markDayStarted(dayNumber);
                }
                
                // Scroll to content
                $('html, body').animate({
                    scrollTop: self.$container.find('.skillsprint-days-nav').offset().top - 50
                }, 300);
            });
            
            // Check if URL has day hash
            const hash = window.location.hash;
            if (hash && hash.startsWith('#day-')) {
                const dayNumber = parseInt(hash.replace('#day-', ''));
                const $tab = this.$dayTabs.filter(`[data-day="${dayNumber}"]`);
                
                if ($tab.length && !$tab.hasClass('locked')) {
                    $tab.trigger('click');
                    return;
                }
            }
            
            // Activate first accessible tab by default
            let $firstAccessible = this.$dayTabs.not('.locked').first();
            if ($firstAccessible.length) {
                $firstAccessible.trigger('click');
            } else {
                this.$dayTabs.first().addClass('active');
                this.$dayContents.first().addClass('active');
            }
        }
        
        /**
         * Initialize day navigation
         */
        initDayNavigation() {
            const self = this;
            
            this.$container.find('.skillsprint-day-nav-prev, .skillsprint-day-nav-next').on('click', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const currentDay = self.$dayTabs.filter('.active').data('day');
                let targetDay;
                
                if ($button.hasClass('skillsprint-day-nav-prev')) {
                    targetDay = currentDay - 1;
                } else {
                    targetDay = currentDay + 1;
                }
                
                const $targetTab = self.$dayTabs.filter(`[data-day="${targetDay}"]`);
                
                if ($targetTab.length) {
                    $targetTab.trigger('click');
                }
            });
            
            // Update navigation button states on tab change
            this.$dayTabs.on('click', function() {
                const dayNumber = $(this).data('day');
                const $prevButton = self.$container.find('.skillsprint-day-nav-prev');
                const $nextButton = self.$container.find('.skillsprint-day-nav-next');
                
                // Disable prev button on first day
                if (dayNumber === 1) {
                    $prevButton.prop('disabled', true);
                } else {
                    $prevButton.prop('disabled', false);
                }
                
                // Disable next button on last day or if next day is locked
                const $nextTab = self.$dayTabs.filter(`[data-day="${dayNumber + 1}"]`);
                if (!$nextTab.length || $nextTab.hasClass('locked')) {
                    $nextButton.prop('disabled', true);
                } else {
                    $nextButton.prop('disabled', false);
                }
            });
        }
        
        /**
         * Initialize complete day buttons
         */
        initCompleteButtons() {
            const self = this;
            
            this.$container.find('.skillsprint-complete-day-button').on('click', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const dayNumber = $button.data('day');
                
                // Check if user is logged in
                if (!skillsprint.is_user_logged_in) {
                    self.showLoginRequired();
                    return;
                }
                
                // Check if there's a quiz that needs to be completed
                const $dayContent = self.$container.find(`.skillsprint-day-content[data-day="${dayNumber}"]`);
                const $quiz = $dayContent.find('.skillsprint-quiz');
                
                if ($quiz.length && !$quiz.data('passed')) {
                    alert(skillsprint.i18n.quiz_needed);
                    return;
                }
                
                // Disable button and show loading state
                $button.prop('disabled', true).text(skillsprint.i18n.saving);
                
                // Mark day as completed
                self.markDayCompleted(dayNumber, $button);
            });
        }
        
        /**
         * Load user progress
         */
        loadUserProgress() {
            // Only load progress if user is logged in
            if (!skillsprint.is_user_logged_in) {
                return;
            }
            
            const self = this;
            
            // Send AJAX request
            $.post(skillsprint.ajax_url, {
                action: 'skillsprint_get_user_blueprint_progress',
                blueprint_id: this.blueprintId,
                nonce: skillsprint.nonce
            }, function(response) {
                if (response.success) {
                    // Update progress bar
                    self.updateProgressBar(response.data.completion_percentage);
                    
                    // Update day tabs and buttons based on progress
                    response.data.progress.forEach(function(dayProgress) {
                        const dayNumber = dayProgress.day_number;
                        const $tab = self.$dayTabs.filter(`[data-day="${dayNumber}"]`);
                        const $button = self.$container.find(`.skillsprint-complete-day-button[data-day="${dayNumber}"]`);
                        
                        if (dayProgress.progress_status === 'completed') {
                            $tab.addClass('completed');
                            $button.text(skillsprint.i18n.day_completed).addClass('success').prop('disabled', true);
                            
                            // Unlock next day if strict progression
                            const $nextTab = self.$dayTabs.filter(`[data-day="${dayNumber + 1}"]`);
                            if ($nextTab.length && $nextTab.hasClass('locked')) {
                                $nextTab.removeClass('locked');
                            }
                        } else if (dayProgress.progress_status === 'in_progress') {
                            // Nothing to do for in-progress days
                        }
                    });
                }
            });
        }
        
        /**
         * Mark a day as started
         * 
         * @param {number} dayNumber Day number
         */
        markDayStarted(dayNumber) {
            // Send AJAX request
            $.post(skillsprint.ajax_url, {
                action: 'skillsprint_mark_day_started',
                blueprint_id: this.blueprintId,
                day_number: dayNumber,
                nonce: skillsprint.nonce
            });
        }
        
        /**
         * Mark a day as completed
         * 
         * @param {number} dayNumber Day number
         * @param {jQuery} $button   Button element
         */
        markDayCompleted(dayNumber, $button) {
            const self = this;
            
            // Send AJAX request
            $.post(skillsprint.ajax_url, {
                action: 'skillsprint_mark_day_completed',
                blueprint_id: this.blueprintId,
                day_number: dayNumber,
                nonce: skillsprint.nonce
            }, function(response) {
                if (response.success) {
                    // Update UI
                    $button.text(skillsprint.i18n.day_completed).addClass('success');
                    
                    // Update tab status
                    self.$dayTabs.filter(`[data-day="${dayNumber}"]`).addClass('completed');
                    
                    // Update progress bar
                    self.updateProgressBar(response.data.completion_percentage);
                    
                    // Show completion message
                    if (response.data.blueprint_completed) {
                        self.showBlueprintCompletedMessage();
                    } else if (response.data.next_day) {
                        // Enable next day tab
                        self.$dayTabs.filter(`[data-day="${response.data.next_day}"]`).removeClass('locked');
                        
                        // Show next day button
                        const $nextButton = $('<button>', {
                            class: 'skillsprint-button secondary skillsprint-next-day-button',
                            text: skillsprint.i18n.next_day,
                            'data-day': response.data.next_day
                        });
                        
                        $button.after($nextButton);
                        
                        $nextButton.on('click', function() {
                            self.$dayTabs.filter(`[data-day="${response.data.next_day}"]`).trigger('click');
                        });
                    }
                } else {
                    alert(response.data.message);
                    $button.prop('disabled', false).text(skillsprint.i18n.complete);
                }
            }).fail(function() {
                alert('An error occurred. Please try again.');
                $button.prop('disabled', false).text(skillsprint.i18n.complete);
            });
        }
        
        /**
         * Update progress bar
         * 
         * @param {number} percentage Completion percentage
         */
        updateProgressBar(percentage) {
            this.$progressBar.css('width', percentage + '%');
            this.$progressPercentage.text(percentage + '%');
        }
        
        /**
         * Show login required message
         */
        showLoginRequired() {
            // Create login modal
            const $modal = $(`
                <div class="skillsprint-modal-overlay active">
                    <div class="skillsprint-modal">
                        <div class="skillsprint-modal-header">
                            <h3 class="skillsprint-modal-title">${skillsprint.i18n.login_required}</h3>
                            <button class="skillsprint-modal-close">&times;</button>
                        </div>
                        <div class="skillsprint-modal-body">
                            <p>${skillsprint.i18n.login_message}</p>
                            <p>${skillsprint.i18n.please_login}</p>
                        </div>
                        <div class="skillsprint-modal-footer">
                            <button class="skillsprint-button login-button">Log In</button>
                            <button class="skillsprint-button secondary register-button">Register</button>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append($modal);
            
            // Close modal when clicking overlay or close button
            $modal.on('click', function(e) {
                if ($(e.target).hasClass('skillsprint-modal-overlay')) {
                    $modal.remove();
                }
            });
            
            $modal.find('.skillsprint-modal-close').on('click', function() {
                $modal.remove();
            });
            
            // Login button
            $modal.find('.login-button').on('click', function() {
                $modal.remove();
                $('#skillsprint-login-modal').addClass('active');
            });
            
            // Register button
            $modal.find('.register-button').on('click', function() {
                $modal.remove();
                $('#skillsprint-register-modal').addClass('active');
            });
        }
        
        /**
         * Show locked day message
         */
        showLockedDayMessage() {
            // Create locked message modal
            const $modal = $(`
                <div class="skillsprint-modal-overlay active">
                    <div class="skillsprint-modal">
                        <div class="skillsprint-modal-header">
                            <h3 class="skillsprint-modal-title">${skillsprint.i18n.day_locked}</h3>
                            <button class="skillsprint-modal-close">&times;</button>
                        </div>
                        <div class="skillsprint-modal-body">
                            <p>${skillsprint.i18n.complete_previous}</p>
                        </div>
                        <div class="skillsprint-modal-footer">
                            <button class="skillsprint-button skillsprint-modal-close">OK</button>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append($modal);
            
            // Close modal when clicking overlay or close button
            $modal.on('click', function(e) {
                if ($(e.target).hasClass('skillsprint-modal-overlay')) {
                    $modal.remove();
                }
            });
            
            $modal.find('.skillsprint-modal-close').on('click', function() {
                $modal.remove();
            });
        }
        
        /**
         * Show blueprint completed message
         */
        showBlueprintCompletedMessage() {
            // Create completion modal
            const $modal = $(`
                <div class="skillsprint-modal-overlay active">
                    <div class="skillsprint-modal">
                        <div class="skillsprint-modal-header">
                            <h3 class="skillsprint-modal-title">${skillsprint.i18n.congratulations}</h3>
                            <button class="skillsprint-modal-close">&times;</button>
                        </div>
                        <div class="skillsprint-modal-body">
                            <div class="skillsprint-alert success">
                                <h4 class="skillsprint-alert-title">${skillsprint.i18n.blueprint_completed}</h4>
                                <p class="skillsprint-alert-message">You have successfully completed all 7 days of this blueprint. Great job!</p>
                            </div>
                            <p>You've reached a significant milestone in your learning journey. Your progress has been saved, and you'll receive points and achievements for your accomplishment.</p>
                        </div>
                        <div class="skillsprint-modal-footer">
                            <a href="/skillsprint-dashboard" class="skillsprint-button">View Dashboard</a>
                            <button class="skillsprint-button secondary skillsprint-modal-close">Close</button>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append($modal);
            
            // Close modal when clicking overlay or close button
            $modal.on('click', function(e) {
                if ($(e.target).hasClass('skillsprint-modal-overlay')) {
                    $modal.remove();
                }
            });
            
            $modal.find('.skillsprint-modal-close').on('click', function() {
                $modal.remove();
            });
        }
    }
    
    // Initialize blueprint viewer when document is ready
    $(document).ready(function() {
        $('.skillsprint-blueprint').each(function() {
            new BlueprintViewer($(this));
        });
    });

})(jQuery);