/**
 * Quiz interface JavaScript
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/public/js
 */

(function($) {
    'use strict';

    /**
     * Quiz Interface class
     */
    class QuizInterface {
        /**
         * Constructor
         * 
         * @param {jQuery} $container The quiz container element
         */
        constructor($container) {
            this.$container = $container;
            this.quizId = $container.data('quiz');
            this.blueprintId = $container.data('blueprint');
            this.$form = $container.find('.skillsprint-quiz-form');
            this.$submitButton = this.$form.find('button[type="submit"]');
            this.$message = this.$form.find('.skillsprint-quiz-message');
            
            this.init();
        }
        
        /**
         * Initialize the quiz interface
         */
        init() {
            // Initialize form submission
            this.initFormSubmission();
            
            // Initialize special question types
            this.initSpecialQuestionTypes();
            
            // Check if user has already attempted the quiz
            this.checkPreviousAttempts();
        }
        
        /**
         * Initialize form submission
         */
        initFormSubmission() {
            const self = this;
            
            this.$form.on('submit', function(e) {
                e.preventDefault();
                
                // Check if user is logged in
                if (!skillsprint.is_user_logged_in) {
                    self.showLoginRequired();
                    return;
                }
                
                // Validate form
                if (!self.validateForm()) {
                    return;
                }
                
                // Disable button and show loading state
                self.$submitButton.prop('disabled', true).text(skillsprint.i18n.saving);
                self.$message.removeClass('success danger').empty();
                
                // Get form data
                const formData = {
                    blueprint_id: self.blueprintId,
                    quiz_id: self.quizId,
                    answers: self.collectAnswers(),
                    nonce: skillsprint.nonce,
                    action: 'skillsprint_submit_quiz'
                };
                
                // Send AJAX request
                $.post(skillsprint.ajax_url, formData, function(response) {
                    if (response.success) {
                        // Display results
                        self.displayResults(response.data);
                        
                        // Enable complete day button if quiz passed
                        if (response.data.score.passed) {
                            self.$container.data('passed', true);
                            $('.skillsprint-complete-day-button[data-quiz="' + self.quizId + '"]').prop('disabled', false);
                        }
                    } else {
                        self.$message.addClass('danger').text(response.data.message);
                        self.$submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                    }
                }).fail(function() {
                    self.$message.addClass('danger').text('An error occurred. Please try again.');
                    self.$submitButton.prop('disabled', false).text(skillsprint.i18n.submit);
                });
            });
        }
        
        /**
         * Initialize special question types
         */
        initSpecialQuestionTypes() {
            // Initialize matching questions
            this.$form.find('.skillsprint-question[data-type="matching"]').each(function() {
                const $question = $(this);
                const $options = $question.find('.skillsprint-question-option');
                
                // Sort left column options
                $options.find('select').each(function() {
                    const $select = $(this);
                    const $options = $select.find('option');
                    
                    // Skip the first "Select" option
                    const $first = $options.first();
                    const $rest = $options.slice(1);
                    
                    // Sort options alphabetically
                    $rest.sort(function(a, b) {
                        return $(a).text().localeCompare($(b).text());
                    });
                    
                    // Reappend options
                    $select.empty().append($first).append($rest);
                });
            });
        }
        
        /**
         * Check if user has previously attempted the quiz
         */
        checkPreviousAttempts() {
            // Only check if user is logged in
            if (!skillsprint.is_user_logged_in) {
                return;
            }
            
            const self = this;
            
            // Send AJAX request to get previous quiz results
            $.post(skillsprint.ajax_url, {
                action: 'skillsprint_get_quiz_results',
                blueprint_id: this.blueprintId,
                quiz_id: this.quizId,
                nonce: skillsprint.nonce
            }, function(response) {
                if (response.success && response.data.has_attempts) {
                    // Display previous results
                    self.displayPreviousResults(response.data);
                }
            });
        }
        
        /**
         * Display previous quiz results
         * 
         * @param {object} data Quiz results data
         */
        displayPreviousResults(data) {
            const self = this;
            
            // Create info message
            const $infoMessage = $(`
                <div class="skillsprint-alert">
                    <h4 class="skillsprint-alert-title">Previous Attempt</h4>
                    <p class="skillsprint-alert-message">
                        You have previously attempted this quiz and scored ${data.score.percentage}%.
                        ${data.score.passed ? 'You passed the quiz!' : 'You did not pass the quiz.'}
                    </p>
                    <div class="skillsprint-quiz-actions">
                        <button class="skillsprint-button small view-results">View Results</button>
                        ${!data.score.passed && data.score.attempt < data.score.max_attempts ? 
                            `<button class="skillsprint-button small secondary new-attempt">Start New Attempt</button>` : 
                            ''
                        }
                    </div>
                </div>
            `);
            
            this.$form.before($infoMessage);
            
            // View results button
            $infoMessage.find('.view-results').on('click', function() {
                self.displayResults(data);
            });
            
            // New attempt button
            $infoMessage.find('.new-attempt').on('click', function() {
                $infoMessage.remove();
                self.$form.show();
            });
            
            // If quiz is passed, hide form and update container
            if (data.score.passed) {
                this.$form.hide();
                this.$container.data('passed', true);
                $('.skillsprint-complete-day-button[data-quiz="' + this.quizId + '"]').prop('disabled', false);
            }
        }
        
        /**
         * Validate the quiz form
         * 
         * @return {boolean} Whether the form is valid
         */
        validateForm() {
            let isValid = true;
            
            // Check each question
            this.$form.find('.skillsprint-question').each(function() {
                const $question = $(this);
                const questionType = $question.data('type');
                let questionAnswered = false;
                
                switch (questionType) {
                    case 'multiple_choice':
                    case 'true_false':
                        questionAnswered = $question.find('input[type="radio"]:checked').length > 0;
                        break;
                        
                    case 'multiple_answer':
                        questionAnswered = $question.find('input[type="checkbox"]:checked').length > 0;
                        break;
                        
                    case 'matching':
                        questionAnswered = true;
                        $question.find('select').each(function() {
                            if (!$(this).val()) {
                                questionAnswered = false;
                            }
                        });
                        break;
                        
                    case 'short_answer':
                        questionAnswered = $question.find('input[type="text"]').val().trim().length > 0;
                        break;
                }
                
                // Highlight unanswered questions
                if (!questionAnswered) {
                    $question.addClass('unanswered');
                    isValid = false;
                } else {
                    $question.removeClass('unanswered');
                }
            });
            
            // Scroll to first unanswered question
            if (!isValid) {
                const $firstUnanswered = this.$form.find('.skillsprint-question.unanswered').first();
                $('html, body').animate({
                    scrollTop: $firstUnanswered.offset().top - 50
                }, 300);
                
                this.$message.addClass('danger').text('Please answer all questions before submitting.');
            }
            
            return isValid;
        }
        
        /**
         * Collect answers from the form
         * 
         * @return {object} Answers object
         */
        collectAnswers() {
            const answers = {};
            
            // Collect answers based on question type
            this.$form.find('.skillsprint-question').each(function() {
                const $question = $(this);
                const questionId = $question.data('question');
                const questionType = $question.data('type');
                
                switch (questionType) {
                    case 'multiple_choice':
                    case 'true_false':
                        answers[questionId] = $question.find('input[type="radio"]:checked').val();
                        break;
                        
                    case 'multiple_answer':
                        answers[questionId] = [];
                        $question.find('input[type="checkbox"]:checked').each(function() {
                            answers[questionId].push($(this).val());
                        });
                        break;
                        
                    case 'matching':
                        answers[questionId] = {};
                        $question.find('select').each(function() {
                            const $select = $(this);
                            answers[questionId][$select.data('left')] = $select.val();
                        });
                        break;
                        
                    case 'short_answer':
                        answers[questionId] = $question.find('input[type="text"]').val().trim();
                        break;
                }
            });
            
            return answers;
        }
        
        /**
         * Display quiz results
         * 
         * @param {object} data Quiz results data
         */
        displayResults(data) {
            // Hide form
            this.$form.hide();
            
            // Create results container
            let $results = this.$container.find('.skillsprint-quiz-results');
            if ($results.length) {
                $results.empty();
            } else {
                $results = $('<div>', {
                    class: 'skillsprint-quiz-results'
                });
                this.$container.append($results);
            }
            
            // Create results content
            $results.append(`
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
            `);
            
            // Create detailed feedback
            const $feedback = $('<div>', {
                class: 'skillsprint-quiz-feedback',
                css: { display: 'none' }
            });
            $results.append($feedback);
            
            // Add event handlers
            $results.find('.retry-quiz').on('click', () => {
                this.retryQuiz();
            });
            
            $results.find('.review-quiz').on('click', () => {
                this.reviewQuiz(data, $feedback);
            });
        }
        
        /**
         * Retry the quiz
         */
        retryQuiz() {
            // Reset form
            this.$form.trigger('reset');
            this.$form.find('.skillsprint-question').removeClass('unanswered');
            this.$message.removeClass('success danger').empty();
            
            // Show form
            this.$form.show();
            
            // Hide results
            this.$container.find('.skillsprint-quiz-results').hide();
        }
        
        /**
         * Review the quiz
         * 
         * @param {object} data     Quiz results data
         * @param {jQuery} $feedback Feedback container
         */
        reviewQuiz(data, $feedback) {
            // Create feedback content
            $feedback.empty();
            
            // Add each question with feedback
            this.$form.find('.skillsprint-question').each(function() {
                const $question = $(this);
                const questionId = $question.data('question');
                const questionType = $question.data('type');
                const questionText = $question.find('.skillsprint-question-text').text();
                const result = data.question_results[questionId];
                
                if (!result) {
                    return;
                }
                
                // Create question feedback
                const $questionFeedback = $(`
                    <div class="skillsprint-question-review">
                        <h4>${questionText}</h4>
                        <div class="skillsprint-question-status ${result.is_correct ? 'correct' : 'incorrect'}">
                            ${result.is_correct ? 'Correct' : 'Incorrect'}
                        </div>
                    </div>
                `);
                
                // Add explanation if available
                if (result.explanation) {
                    $questionFeedback.append(`
                        <div class="skillsprint-question-explanation">
                            <strong>Explanation:</strong> ${result.explanation}
                        </div>
                    `);
                }
                
                // Add question-specific feedback
                switch (questionType) {
                    case 'multiple_choice':
                    case 'true_false':
                        $questionFeedback.append(`
                            <div class="skillsprint-question-answer">
                                <strong>Your answer:</strong> ${$question.find(`input[value="${data.answers[questionId]}"]`).next().text()}
                            </div>
                            <div class="skillsprint-question-correct-answer">
                                <strong>Correct answer:</strong> ${$question.find(`input[value="${result.correct_answer}"]`).next().text()}
                            </div>
                        `);
                        break;
                        
                    case 'multiple_answer':
                        // Get answer labels
                        const userAnswerLabels = [];
                        const correctAnswerLabels = [];
                        
                        $question.find('input[type="checkbox"]').each(function() {
                            const $input = $(this);
                            const label = $input.next().text();
                            
                            if (data.answers[questionId] && data.answers[questionId].includes($input.val())) {
                                userAnswerLabels.push(label);
                            }
                            
                            if (result.correct_answer.includes($input.val())) {
                                correctAnswerLabels.push(label);
                            }
                        });
                        
                        $questionFeedback.append(`
                            <div class="skillsprint-question-answer">
                                <strong>Your answer:</strong> ${userAnswerLabels.join(', ') || 'None selected'}
                            </div>
                            <div class="skillsprint-question-correct-answer">
                                <strong>Correct answer:</strong> ${correctAnswerLabels.join(', ')}
                            </div>
                        `);
                        break;
                        
                    case 'matching':
                        // Get answer pairs
                        const userAnswerPairs = [];
                        const correctAnswerPairs = [];
                        
                        $question.find('select').each(function() {
                            const $select = $(this);
                            const leftValue = $select.data('left');
                            const leftLabel = $select.prev().text();
                            
                            // User answer
                            if (data.answers[questionId] && data.answers[questionId][leftValue]) {
                                const userRightValue = data.answers[questionId][leftValue];
                                const userRightLabel = $select.find(`option[value="${userRightValue}"]`).text();
                                userAnswerPairs.push(`${leftLabel} → ${userRightLabel}`);
                            }
                            
                            // Correct answer
                            if (result.correct_answer[leftValue]) {
                                const correctRightValue = result.correct_answer[leftValue];
                                const correctRightLabel = $select.find(`option[value="${correctRightValue}"]`).text();
                                correctAnswerPairs.push(`${leftLabel} → ${correctRightLabel}`);
                            }
                        });
                        
                        $questionFeedback.append(`
                            <div class="skillsprint-question-answer">
                                <strong>Your answers:</strong>
                                <ul>${userAnswerPairs.map(pair => `<li>${pair}</li>`).join('')}</ul>
                            </div>
                            <div class="skillsprint-question-correct-answer">
                                <strong>Correct answers:</strong>
                                <ul>${correctAnswerPairs.map(pair => `<li>${pair}</li>`).join('')}</ul>
                            </div>
                        `);
                        break;
                        
                    case 'short_answer':
                        $questionFeedback.append(`
                            <div class="skillsprint-question-answer">
                                <strong>Your answer:</strong> ${data.answers[questionId] || 'No answer provided'}
                            </div>
                            <div class="skillsprint-question-correct-answer">
                                <strong>Acceptable answers:</strong> ${Array.isArray(result.correct_answer) ? result.correct_answer.join(', ') : result.correct_answer}
                            </div>
                        `);
                        break;
                }
                
                $feedback.append($questionFeedback);
            });
            
            // Show feedback
            $feedback.show();
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
    }
    
    // Initialize quiz interfaces when document is ready
    $(document).ready(function() {
        $('.skillsprint-quiz').each(function() {
            new QuizInterface($(this));
        });
    });

})(jQuery);