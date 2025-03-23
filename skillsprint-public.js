/**
 * All public-facing JavaScript for the plugin
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/js
 */

(function($) {
    'use strict';

    /**
     * Main initialization function
     */
    function init() {
        // Initialize day tabs
        initDayTabs();
        
        // Initialize modals
        initModals();
        
        // Initialize progress tracking
        initProgressTracking();
        
        // Initialize dashboards if present
        if ($('.skillsprint-dashboard').length) {
            initDashboard();
        }
    }

    /**
     * Initialize day tabs functionality
     */
    function initDayTabs() {
        const $dayTabs = $('.skillsprint-day-tab');
        const $dayContents = $('.skillsprint-day-content');
        
        $dayTabs.on('click', function(e) {
            e.preventDefault();
            
            const $this = $(this);
            const dayNumber = $this.data('day');
            
            // Check if tab is locked
            if ($this.hasClass('locked')) {
                showLoginModal();
                return;
            }
            
            // Activate tab and content
            $dayTabs.removeClass('active');
            $this.addClass('active');
            
            $dayContents.removeClass('active');
            $(`.skillsprint-day-content[data-day="${dayNumber}"]`).addClass('active');
            
            // Mark day as started if user is logged in
            if (skillsprint.is_user_logged_in && !$this.hasClass('completed')) {
                markDayStarted(dayNumber);
            }
        });
        
        // Activate first accessible tab by default
        let $firstAccessible = $dayTabs.not('.locked').first();
        if ($firstAccessible.length) {
            $firstAccessible.trigger('click');
        } else {
            $dayTabs.first().addClass('active');
            $dayContents.first().addClass('active');
        }
    }

    /**
     * Initialize modal functionality
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
            $message.removeClass('success danger').empty();
            
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
                    $message.addClass('danger').text(response.data.message);
                    $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                }
            }).fail(function() {
                $message.addClass('danger').text('An error occurred. Please try again.');
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
            $message.removeClass('success danger').empty();
            
            // Validate password
            const password = $form.find('input[name="password"]').val();
            const passwordConfirm = $form.find('input[name="password_confirm"]').val();
            
            if (password !== passwordConfirm) {
                $message.addClass('danger').text('Passwords do not match.');
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
                    $message.addClass('danger').text(response.data.message);
                    $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                }
            }).fail(function() {
                $message.addClass('danger').text('An error occurred. Please try again.');
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

    /**
     * Initialize progress tracking functionality
     */
    function initProgressTracking() {
        // Complete day button handler
        $('.skillsprint-complete-day-button').on('click', function() {
            const $button = $(this);
            const dayNumber = $button.data('day');
            const blueprintId = $button.data('blueprint');
            
            // Check if user is logged in
            if (!skillsprint.is_user_logged_in) {
                showLoginModal();
                return;
            }
            
            // Check if there's a quiz that needs to be completed
            const $quiz = $(`.skillsprint-day-content[data-day="${dayNumber}"] .skillsprint-quiz`);
            if ($quiz.length && !$quiz.data('passed')) {
                alert(skillsprint.i18n.quiz_needed);
                return;
            }
            
            // Disable button and show loading state
            $button.prop('disabled', true).text(skillsprint.i18n.saving);
            
            // Mark day as completed
            markDayCompleted(dayNumber, blueprintId, $button);
        });
        
        // Quiz form submission
        $('.skillsprint-quiz-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitButton = $form.find('button[type="submit"]');
            const $message = $form.find('.skillsprint-quiz-message');
            
            // Check if user is logged in
            if (!skillsprint.is_user_logged_in) {
                showLoginModal();
                return;
            }
            
            // Disable button and show loading state
            $submitButton.prop('disabled', true).text(skillsprint.i18n.saving);
            $message.removeClass('success danger').empty();
            
            // Get form data
            const formData = {
                blueprint_id: $form.data('blueprint'),
                quiz_id: $form.data('quiz'),
                answers: {},
                nonce: skillsprint.nonce,
                action: 'skillsprint_submit_quiz'
            };
            
            // Collect answers based on question type
            $form.find('.skillsprint-question').each(function() {
                const $question = $(this);
                const questionId = $question.data('question');
                const questionType = $question.data('type');
                
                switch (questionType) {
                    case 'multiple_choice':
                    case 'true_false':
                        formData.answers[questionId] = $question.find('input[type="radio"]:checked').val();
                        break;
                        
                    case 'multiple_answer':
                        formData.answers[questionId] = [];
                        $question.find('input[type="checkbox"]:checked').each(function() {
                            formData.answers[questionId].push($(this).val());
                        });
                        break;
                        
                    case 'matching':
                        formData.answers[questionId] = {};
                        $question.find('select').each(function() {
                            const $select = $(this);
                            formData.answers[questionId][$select.data('left')] = $select.val();
                        });
                        break;
                        
                    case 'short_answer':
                        formData.answers[questionId] = $question.find('input[type="text"]').val();
                        break;
                }
            });
            
            // Send AJAX request
            $.post(skillsprint.ajax_url, formData, function(response) {
                if (response.success) {
                    // Display results
                    displayQuizResults($form, response.data);
                    
                    // Enable complete day button if quiz passed
                    if (response.data.score.passed) {
                        $form.data('passed', true);
                        $('.skillsprint-complete-day-button').prop('disabled', false);
                    }
                } else {
                    $message.addClass('danger').text(response.data.message);
                    $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                }
            }).fail(function() {
                $message.addClass('danger').text('An error occurred. Please try again.');
                $submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
            });
        });
    }

    /**
     * Mark a day as started
     * 
     * @param {number} dayNumber Day number
     */
    function markDayStarted(dayNumber) {
        const blueprintId = $('.skillsprint-blueprint').data('blueprint');
        
        // Send AJAX request
        $.post(skillsprint.ajax_url, {
            action: 'skillsprint_mark_day_started',
            blueprint_id: blueprintId,
            day_number: dayNumber,
            nonce: skillsprint.nonce
        });
    }

    /**
     * Mark a day as completed
     * 
     * @param {number} dayNumber   Day number
     * @param {number} blueprintId Blueprint ID
     * @param {jQuery} $button     Button element
     */
    function markDayCompleted(dayNumber, blueprintId, $button) {
        // Send AJAX request
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
                $(`.skillsprint-day-tab[data-day="${dayNumber}"]`).addClass('completed');
                
                // Update progress bar
                updateProgressBar(response.data.completion_percentage);
                
                // Show completion message
                if (response.data.blueprint_completed) {
                    showBlueprintCompletedMessage();
                } else if (response.data.next_day) {
                    // Enable next day tab
                    $(`.skillsprint-day-tab[data-day="${response.data.next_day}"]`).removeClass('locked');
                    
                    // Show next day button
                    $button.after(`
                        <button class="skillsprint-button secondary" onclick="$(').skillsprint-day-tab[data-day="${response.data.next_day}"]').trigger('click')">
                            ${skillsprint.i18n.next_day}
                        </button>
                    `);
                }
            } else {
                alert(response.data.message);
                $button.prop('disabled', false).text(skillsprint.i18n.submit);
            }
        }).fail(function() {
            alert('An error occurred. Please try again.');
            $button.prop('disabled', false).text(skillsprint.i18n.submit);
        });
    }

    /**
     * Update progress bar
     * 
     * @param {number} percentage Completion percentage
     */
    function updateProgressBar(percentage) {
        $('.skillsprint-progress-bar').css('width', percentage + '%');
        $('.skillsprint-progress-percentage').text(percentage + '%');
    }

    /**
     * Show blueprint completed message
     */
    function showBlueprintCompletedMessage() {
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
                        <button class="skillsprint-button" onclick="window.location.href='/skillsprint-dashboard'">View Dashboard</button>
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

    /**
     * Display quiz results
     * 
     * @param {jQuery} $form     Quiz form
     * @param {object} data      Quiz results data
     */
    function displayQuizResults($form, data) {
        const $quizContainer = $form.closest('.skillsprint-quiz');
        const $questions = $form.find('.skillsprint-question');
        
        // Hide form and show results
        $form.hide();
        
        // Create result container if it doesn't exist
        let $results = $quizContainer.find('.skillsprint-quiz-results');
        if (!$results.length) {
            $results = $(`
                <div class="skillsprint-quiz-results">
                    <h3 class="skillsprint-quiz-results-title">Quiz Results</h3>
                    <div class="skillsprint-quiz-score">
                        <div class="skillsprint-quiz-score-circle">${data.score.percentage}%</div>
                        <div class="skillsprint-quiz-score-text">
                            <div class="skillsprint-quiz-score-label">Your Score</div>
                            <div class="skillsprint-quiz-score-value ${data.score.passed ? 'passed' : 'failed'}">
                                ${data.score.passed ? 'Passed' : 'Failed'} (${data.score.correct_count}/${data.score.total_questions} correct)
                            </div>
                        </div>
                    </div>
                    <ul class="skillsprint-quiz-summary">
                        <li>
                            <span>Points Earned:</span>
                            <span>${data.score.earned_points}/${data.score.total_points}</span>
                        </li>
                        <li>
                            <span>Passing Score:</span>
                            <span>${data.score.passing_score}%</span>
                        </li>
                        <li>
                            <span>Attempt:</span>
                            <span>${data.score.attempt}/${data.score.max_attempts}</span>
                        </li>
                    </ul>
                    <div class="skillsprint-quiz-actions">
                        ${!data.score.passed && data.score.attempt < data.score.max_attempts ? 
                            `<button class="skillsprint-button retry-quiz">${skillsprint.i18n.retry}</button>` : 
                            ''
                        }
                        <button class="skillsprint-button secondary review-quiz">${skillsprint.i18n.review}</button>
                    </div>
                </div>
            `);
            $quizContainer.append($results);
        }
        
        // Update question feedback
        $questions.each(function() {
            const $question = $(this);
            const questionId = $question.data('question');
            const result = data.question_results[questionId];
            
            if (result) {
                // Mark options as correct/incorrect
                const questionType = $question.data('type');
                
                switch (questionType) {
                    case 'multiple_choice':
                    case 'true_false':
                        $question.find('input[type="radio"]').each(function() {
                            const $input = $(this);
                            const $label = $input.closest('label');
                            
                            if ($input.val() === result.correct_answer) {
                                $label.closest('.skillsprint-question-option').addClass('correct');
                            } else if ($input.is(':checked') && !result.is_correct) {
                                $label.closest('.skillsprint-question-option').addClass('incorrect');
                            }
                        });
                        break;
                        
                    case 'multiple_answer':
                        $question.find('input[type="checkbox"]').each(function() {
                            const $input = $(this);
                            const $label = $input.closest('label');
                            
                            if (result.correct_answer.includes($input.val())) {
                                $label.closest('.skillsprint-question-option').addClass('correct');
                            } else if ($input.is(':checked') && !result.is_correct) {
                                $label.closest('.skillsprint-question-option').addClass('incorrect');
                            }
                        });
                        break;
                        
                    case 'matching':
                        $question.find('select').each(function() {
                            const $select = $(this);
                            const leftValue = $select.data('left');
                            
                            if (result.correct_answer[leftValue] === $select.val()) {
                                $select.closest('.skillsprint-question-option').addClass('correct');
                            } else {
                                $select.closest('.skillsprint-question-option').addClass('incorrect');
                            }
                        });
                        break;
                        
                    case 'short_answer':
                        const $input = $question.find('input[type="text"]');
                        
                        if (result.is_correct) {
                            $input.closest('.skillsprint-question-option').addClass('correct');
                        } else {
                            $input.closest('.skillsprint-question-option').addClass('incorrect');
                        }
                        break;
                }
                
                // Add feedback
                let feedbackClass = result.is_correct ? 'correct' : 'incorrect';
                let feedbackText = result.is_correct ? 
                    skillsprint.i18n.correct : 
                    `${skillsprint.i18n.incorrect}. ${result.explanation}`;
                
                $question.append(`
                    <div class="skillsprint-question-feedback ${feedbackClass}">
                        ${feedbackText}
                    </div>
                `);
            }
        });
        
        // Add event handlers for result actions
        $results.find('.retry-quiz').on('click', function() {
            // Reset and show form
            $form.trigger('reset');
            $form.show();
            
            // Remove feedback and classes
            $questions.find('.skillsprint-question-feedback').remove();
            $questions.find('.skillsprint-question-option').removeClass('correct incorrect');
            
            // Hide results
            $results.hide();
        });
        
        $results.find('.review-quiz').on('click', function() {
            // Show form for review
            $form.show();
            
            // Disable inputs
            $form.find('input, select, button[type="submit"]').prop('disabled', true);
        });
    }

    /**
     * Initialize dashboard functionality
     */
    function initDashboard() {
        // Load dashboard data
        $.post(skillsprint.ajax_url, {
            action: 'skillsprint_get_dashboard_data',
            nonce: skillsprint.nonce
        }, function(response) {
            if (response.success) {
                // Update dashboard widgets with the data
                updateDashboardWidgets(response.data);
            }
        });
    }

    /**
     * Update dashboard widgets with data
     * 
     * @param {object} data Dashboard data
     */
    function updateDashboardWidgets(data) {
        // Update statistics
        $('.skillsprint-stat-value[data-stat="days_completed"]').text(data.stats.days_completed);
        $('.skillsprint-stat-value[data-stat="blueprints_completed"]').text(data.stats.blueprints_completed);
        $('.skillsprint-stat-value[data-stat="blueprints_started"]').text(data.stats.blueprints_started);
        $('.skillsprint-stat-value[data-stat="points"]').text(data.total_points);
        $('.skillsprint-stat-value[data-stat="streak"]').text(data.streak_info.current_streak);
        $('.skillsprint-stat-value[data-stat="accuracy"]').text(data.stats.accuracy + '%');
        
        // Update in-progress blueprints
        const $inProgressList = $('.skillsprint-in-progress-list');
        if ($inProgressList.length && data.in_progress.length) {
            $inProgressList.empty();
            
            data.in_progress.forEach(function(blueprint) {
                $inProgressList.append(`
                    <div class="skillsprint-card">
                        <img src="${blueprint.thumbnail}" alt="${blueprint.title}" class="skillsprint-card-img">
                        <div class="skillsprint-card-content">
                            <h3 class="skillsprint-card-title">${blueprint.title}</h3>
                            <p class="skillsprint-card-text">${blueprint.excerpt}</p>
                            <div class="skillsprint-progress-bar-container">
                                <div class="skillsprint-progress-bar" style="width: ${blueprint.progress}%"></div>
                            </div>
                            <div class="skillsprint-progress-text">
                                <span>Progress: ${blueprint.progress}%</span>
                                <span>Last: Day ${blueprint.last_day_accessed}</span>
                            </div>
                        </div>
                        <div class="skillsprint-card-footer">
                            <span class="skillsprint-badge ${blueprint.difficulty.slug}">${blueprint.difficulty.name}</span>
                            <a href="${blueprint.permalink}" class="skillsprint-button small">Continue</a>
                        </div>
                    </div>
                `);
            });
        } else if ($inProgressList.length) {
            $inProgressList.html('<p>You haven\'t started any blueprints yet. Explore our catalog to begin your learning journey!</p>');
        }
        
        // Update completed blueprints
        const $completedList = $('.skillsprint-completed-list');
        if ($completedList.length && data.completed.length) {
            $completedList.empty();
            
            data.completed.forEach(function(blueprint) {
                $completedList.append(`
                    <div class="skillsprint-card">
                        <img src="${blueprint.thumbnail}" alt="${blueprint.title}" class="skillsprint-card-img">
                        <div class="skillsprint-card-content">
                            <h3 class="skillsprint-card-title">${blueprint.title}</h3>
                            <p class="skillsprint-card-text">${blueprint.excerpt}</p>
                            <p>Completed on: ${new Date(blueprint.completion_date).toLocaleDateString()}</p>
                        </div>
                        <div class="skillsprint-card-footer">
                            <span class="skillsprint-badge ${blueprint.difficulty.slug}">${blueprint.difficulty.name}</span>
                            <a href="${blueprint.permalink}" class="skillsprint-button small">Review</a>
                        </div>
                    </div>
                `);
            });
        } else if ($completedList.length) {
            $completedList.html('<p>You haven\'t completed any blueprints yet. Keep learning to finish your first blueprint!</p>');
        }
        
        // Update recommended blueprints
        const $recommendedList = $('.skillsprint-recommended-list');
        if ($recommendedList.length && data.recommended.length) {
            $recommendedList.empty();
            
            data.recommended.forEach(function(blueprint) {
                $recommendedList.append(`
                    <div class="skillsprint-card">
                        <img src="${blueprint.thumbnail}" alt="${blueprint.title}" class="skillsprint-card-img">
                        <div class="skillsprint-card-content">
                            <h3 class="skillsprint-card-title">${blueprint.title}</h3>
                            <p class="skillsprint-card-text">${blueprint.excerpt}</p>
                        </div>
                        <div class="skillsprint-card-footer">
                            <span class="skillsprint-badge ${blueprint.difficulty.slug}">${blueprint.difficulty.name}</span>
                            <a href="${blueprint.permalink}" class="skillsprint-button small">Start Learning</a>
                        </div>
                    </div>
                `);
            });
        }
        
        // Update achievements
        const $achievementsList = $('.skillsprint-achievements-list');
        if ($achievementsList.length && data.achievements.length) {
            $achievementsList.empty();
            
            data.achievements.forEach(function(achievement) {
                $achievementsList.append(`
                    <div class="skillsprint-achievement">
                        <div class="skillsprint-achievement-icon">
                            <i class="${achievement.icon}"></i>
                        </div>
                        <div class="skillsprint-achievement-content">
                            <div class="skillsprint-achievement-title">${achievement.title}</div>
                            <div class="skillsprint-achievement-description">${achievement.description}</div>
                            <div class="skillsprint-achievement-date">Earned on ${new Date(achievement.date_earned).toLocaleDateString()}</div>
                        </div>
                    </div>
                `);
            });
        } else if ($achievementsList.length) {
            $achievementsList.html('<p>You haven\'t earned any achievements yet. Complete days and blueprints to earn achievements!</p>');
        }
        
        // Update activity feed
        const $activityFeed = $('.skillsprint-activity-feed');
        if ($activityFeed.length && data.recent_activity.length) {
            $activityFeed.empty();
            
            data.recent_activity.forEach(function(activity) {
                let icon = 'dashicons-welcome-learn-more';
                
                if (activity.status === 'completed') {
                    icon = 'dashicons-yes-alt';
                }
                
                $activityFeed.append(`
                    <div class="skillsprint-activity-item">
                        <div class="skillsprint-activity-icon">
                            <i class="dashicons ${icon}"></i>
                        </div>
                        <div class="skillsprint-activity-content">
                            <div class="skillsprint-activity-title">
                                ${activity.status === 'completed' ? 'Completed' : 'Started'} Day ${activity.day_number} of "${activity.blueprint_title}"
                            </div>
                            <div class="skillsprint-activity-time">
                                ${new Date(activity.date).toLocaleString()}
                            </div>
                        </div>
                    </div>
                `);
            });
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);