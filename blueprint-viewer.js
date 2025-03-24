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
            this.initQuizHandling();
            this.initCompleteButtons();
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
                
                if ($targetTab.length && !$targetTab.hasClass('locked')) {
                    $targetTab.trigger('click');
                }
            });
        }
        
        /**
         * Initialize quiz handling
         */
        initQuizHandling() {
            const self = this;
            
            // Quiz form submission
            this.$container.find('.skillsprint-quiz-form').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $quiz = $form.closest('.skillsprint-quiz');
                const quizId = $quiz.data('quiz');
                const blueprintId = $quiz.data('blueprint');
                const $submitButton = $form.find('button[type="submit"]');
                const $message = $form.find('.skillsprint-quiz-message');
                
                // Validate form
                let isValid = true;
                $form.find('.skillsprint-question').each(function() {
                    const $question = $(this);
                    const questionType = $question.data('type');
                    
                    if (questionType === 'multiple_choice' || questionType === 'true_false') {
                        if (!$question.find('input[type="radio"]:checked').length) {
                            isValid = false;
                            $question.addClass('error');
                        } else {
                            $question.removeClass('error');
                        }
                    } else if (questionType === 'multiple_answer') {
                        // Multiple answer is optional
                        $question.removeClass('error');
                    } else if (questionType === 'matching') {
                        const $selects = $question.find('select');
                        let allFilled = true;
                        
                        $selects.each(function() {
                            if (!$(this).val()) {
                                allFilled = false;
                            }
                        });
                        
                        if (!allFilled) {
                            isValid = false;
                            $question.addClass('error');
                        } else {
                            $question.removeClass('error');
                        }
                    } else if (questionType === 'short_answer') {
                        if (!$question.find('input[type="text"]').val().trim()) {
                            isValid = false;
                            $question.addClass('error');
                        } else {
                            $question.removeClass('error');
                        }
                    }
                });
                
                if (!isValid) {
                    $message.removeClass('success').addClass('error').html(skillsprint.i18n.quiz_validation_error);
                    return;
                }
                
                // Disable button and show loading state
                $submitButton.prop('disabled', true).text(skillsprint.i18n.submitting);
                $message.removeClass('success error').empty();
                
                // Collect answers
                const answers = {};
                
                $form.find('.skillsprint-question').each(function() {
                    const $question = $(this);
                    const questionId = $question.data('question');
                    const questionType = $question.data('type');
                    
                    if (questionType === 'multiple_choice' || questionType === 'true_false') {
                        answers[questionId] = $question.find('input[type="radio"]:checked').val();
                    } else if (questionType === 'multiple_answer') {
                        answers[questionId] = [];
                        $question.find('input[type="checkbox"]:checked').each(function() {
                            answers[questionId].push($(this).val());
                        });
                    } else if (questionType === 'matching') {
                        answers[questionId] = {};
                        $question.find('select').each(function() {
                            const $select = $(this);
                            answers[questionId][$select.data('left')] = $select.val();
                        });
                    } else if (questionType === 'short_answer') {
                        answers[questionId] = $question.find('input[type="text"]').val();
                    }
                });
                
                // Send AJAX request
                $.post(skillsprint.ajax_url, {
                    action: 'skillsprint_submit_quiz',
                    blueprint_id: blueprintId,
                    quiz_id: quizId,
                    answers: answers,
                    nonce: skillsprint.nonce
                }, function(response) {
                    if (response.success) {
                        // Show results
                        self.displayQuizResults($quiz, response.data);
                        
                        // Enable complete day button if quiz passed
                        if (response.data.score.passed) {
                            $quiz.data('passed', true);
                            self.$container.find('.skillsprint-complete-day-button').prop('disabled', false);
                            self.$container.find('.skillsprint-quiz-required-message').hide();
                        }
                    } else {
                        $message.addClass('error').html(response.data.message);
                        $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                    }
                }).fail(function() {
                    $message.addClass('error').text('An error occurred. Please try again.');
                    $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                });
            });
            
            // Retry quiz button
            this.$container.on('click', '.retry-quiz', function() {
                const $button = $(this);
                const $quiz = $button.closest('.skillsprint-quiz');
                const $form = $quiz.find('.skillsprint-quiz-form');
                const $results = $quiz.find('.skillsprint-quiz-results');
                
                // Reset form
                $form[0].reset();
                $form.find('.skillsprint-question').removeClass('correct incorrect error');
                $form.find('.skillsprint-question-feedback').remove();
                
                // Show form and hide results
                $form.show();
                $results.hide();
            });
            
            // Review quiz button
            this.$container.on('click', '.review-quiz', function() {
                const $button = $(this);
                const $quiz = $button.closest('.skillsprint-quiz');
                const $form = $quiz.find('.skillsprint-quiz-form');
                const $results = $quiz.find('.skillsprint-quiz-results');
                
                // Hide results and show form
                $results.hide();
                $form.show();
                
                // Disable form inputs
                $form.find('input, select, button[type="submit"]').prop('disabled', true);
            });
        }
        
        /**
         * Initialize complete day buttons
         */
        initCompleteButtons() {
            const self = this;
            
            this.$container.find('.skillsprint-complete-day-button').on('click', function() {
                if ($(this).prop('disabled')) {
                    return;
                }
                
                const $button = $(this);
                const dayNumber = $button.data('day');
                const blueprintId = $button.data('blueprint');
                
                // Check if user is logged in
                if (!skillsprint.is_user_logged_in) {
                    self.showLoginRequired();
                    return;
                }
                
                // Disable button and show loading state
                $button.prop('disabled', true).text(skillsprint.i18n.saving);
                
                // Mark day as completed
                $.post(skillsprint.ajax_url, {
                    action: 'skillsprint_mark_day_completed',
                    blueprint_id: blueprintId,
                    day_number: dayNumber,
                    nonce: skillsprint.nonce
                }, function(response) {
                    if (response.success) {
                        // Update UI
                        $button.text(skillsprint.i18n.day_completed).addClass('success');
                        
                        // Update tab status
                        self.$dayTabs.filter(`[data-day="${dayNumber}"]`).addClass('completed');
                        
                        // Update progress bar
                        if (response.data.completion_percentage !== undefined) {
                            self.updateProgressBar(response.data.completion_percentage);
                        }
                        
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
         * Update progress bar
         * 
         * @param {number} percentage Completion percentage
         */
        updateProgressBar(percentage) {
            this.$progressBar.css('width', percentage + '%');
            this.$progressPercentage.text(percentage + '%');
        }
        
        /**
         * Display quiz results
         * 
         * @param {jQuery} $quiz Quiz container
         * @param {object} data  Results data
         */
        displayQuizResults($quiz, data) {
            const $form = $quiz.find('.skillsprint-quiz-form');
            const $results = $quiz.find('.skillsprint-quiz-results');
            
            // Update results content
            $results.find('.skillsprint-quiz-score-circle').text(data.score.percentage + '%');
            $results.find('.skillsprint-quiz-score-value').text(
                data.score.passed ? 
                `${data.score.correct_count}/${data.score.total_questions} ${skillsprint.i18n.correct} (${skillsprint.i18n.passed})` : 
                `${data.score.correct_count}/${data.score.total_questions} ${skillsprint.i18n.correct} (${skillsprint.i18n.failed})`
            ).toggleClass('passed', data.score.passed).toggleClass('failed', !data.score.passed);
            
            // Update summary
            const $summary = $results.find('.skillsprint-quiz-summary').empty();
            
            $summary.append(`<li><span>${skillsprint.i18n.points_earned}:</span> <span>${data.score.earned_points}/${data.score.total_points}</span></li>`);
            $summary.append(`<li><span>${skillsprint.i18n.passing_score}:</span> <span>${data.score.passing_score}%</span></li>`);
            $summary.append(`<li><span>${skillsprint.i18n.attempt}:</span> <span>${data.score.attempt}/${data.score.max_attempts === 0 ? '∞' : data.score.max_attempts}</span></li>`);
            
            // Show retry button if attempts remain and quiz was not passed
            $results.find('.retry-quiz').toggle(!data.score.passed && data.score.attempt < data.score.max_attempts);
            
            // Mark questions as correct/incorrect
            $form.find('.skillsprint-question').each(function() {
                const $question = $(this);
                const questionId = $question.data('question');
                const result = data.question_results[questionId];
                
                if (result) {
                    // Add correct/incorrect class
                    $question.addClass(result.is_correct ? 'correct' : 'incorrect');
                    
                    // Add feedback
                    let feedbackText = result.is_correct ? 
                        skillsprint.i18n.correct : 
                        skillsprint.i18n.incorrect;
                    
                    if (!result.is_correct && result.explanation) {
                        feedbackText += `: ${result.explanation}`;
                    }
                    
                    $question.append(`<div class="skillsprint-question-feedback ${result.is_correct ? 'correct' : 'incorrect'}">${feedbackText}</div>`);
                }
            });
            
            // Hide form and show results
            $form.hide();
            $results.show();
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
                            <button class="skillsprint-button login-button">${skillsprint.i18n.login}</button>
                            <button class="skillsprint-button secondary register-button">${skillsprint.i18n.register}</button>
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
                            <button class="skillsprint-button skillsprint-modal-close">${skillsprint.i18n.ok}</button>
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
                                <p class="skillsprint-alert-message">${skillsprint.i18n.blueprint_completed_message}</p>
                            </div>
                            <p>${skillsprint.i18n.blueprint_completed_description}</p>
                        </div>
                        <div class="skillsprint-modal-footer">
                            <a href="${skillsprint.dashboard_url}" class="skillsprint-button">${skillsprint.i18n.view_dashboard}</a>
                            <button class="skillsprint-button secondary skillsprint-modal-close">${skillsprint.i18n.close}</button>
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
    
    /**
     * Modal handling
     */
    function initModals() {
        // Login/register button handlers
        $('.skillsprint-login-button').on('click', function(e) {
            e.preventDefault();
            showLoginModal();
        });
        
        $('.skillsprint-register-button').on('click', function(e) {
            e.preventDefault();
            showRegisterModal();
        });
        
        // Close modal when clicking overlay or close button
        $('.skillsprint-modal-overlay').on('click', function(e) {
            if ($(e.target).hasClass('skillsprint-modal-overlay')) {
                closeAllModals();
            }
        });
        
        $('.skillsprint-modal-close').on('click', function() {
            closeAllModals();
        });
        
        // Login form submission
        $('#skillsprint-login-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitButton = $form.find('button[type="submit"]');
            const $message = $form.find('.skillsprint-form-message');
            
            // Disable button and show loading state
            $submitButton.prop('disabled', true).text(skillsprint.i18n.loading);
            $message.removeClass('success error').empty();
            
            // Get form data
            const formData = {
                username: $form.find('input[name="username"]').val(),
                password: $form.find('input[name="password"]').val(),
                remember: $form.find('input[name="remember"]').is(':checked'),
                redirect_url: window.location.href,
                nonce: skillsprint.nonce,
                action: 'skillsprint_login_user'
            };
            
            // Send AJAX request
            $.post(skillsprint.ajax_url, formData, function(response) {
                if (response.success) {
                    $message.addClass('success').text(response.data.message);
                    
                    // Redirect after successful login
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1000);
                } else {
                    $message.addClass('error').text(response.data.message);
                    $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                }
            }).fail(function() {
                $message.addClass('error').text('An error occurred. Please try again.');
                $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
            });
        });
        
        // Registration form submission
        $('#skillsprint-register-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitButton = $form.find('button[type="submit"]');
            const $message = $form.find('.skillsprint-form-message');
            
            // Disable button and show loading state
            $submitButton.prop('disabled', true).text(skillsprint.i18n.loading);
            $message.removeClass('success error').empty();
            
            // Validate password
            const password = $form.find('input[name="password"]').val();
            const passwordConfirm = $form.find('input[name="password_confirm"]').val();
            
            if (password !== passwordConfirm) {
                $message.addClass('error').text(skillsprint.i18n.passwords_dont_match);
                $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                return;
            }
            
            // Get form data
            const formData = {
                username: $form.find('input[name="username"]').val(),
                email: $form.find('input[name="email"]').val(),
                password: password,
                redirect_url: window.location.href,
                nonce: skillsprint.nonce,
                action: 'skillsprint_register_user'
            };
            
            // Send AJAX request
            $.post(skillsprint.ajax_url, formData, function(response) {
                if (response.success) {
                    $message.addClass('success').text(response.data.message);
                    
                    // Redirect after successful registration
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1000);
                } else {
                    $message.addClass('error').text(response.data.message);
                    $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                }
            }).fail(function() {
                $message.addClass('error').text('An error occurred. Please try again.');
                $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
            });
        });
    }

    /**
     * Show login modal
     */
    function showLoginModal() {
        closeAllModals();
        $('#skillsprint-login-modal').addClass('active');
    }

    /**
     * Show register modal
     */
    function showRegisterModal() {
        closeAllModals();
        $('#skillsprint-register-modal').addClass('active');
    }

    /**
     * Close all modals
     */
    function closeAllModals() {
        $('.skillsprint-modal-overlay').removeClass('active');
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        $('.skillsprint-blueprint').each(function() {
            new BlueprintViewer($(this));
        });
        
        initModals();
    });

})(jQuery);