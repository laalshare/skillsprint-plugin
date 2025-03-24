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
         */
        constructor() {
            this.initQuizForms();
            this.initQuizResults();
            this.initQuizRetry();
        }
        
        /**
         * Initialize quiz forms
         */
        initQuizForms() {
            const self = this;
            
            $('.skillsprint-quiz-form').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $quiz = $form.closest('.skillsprint-quiz');
                const $submitButton = $form.find('button[type="submit"]');
                
                // Check if user is logged in
                if (!skillsprint.is_user_logged_in) {
                    // Show login modal or message
                    if (typeof showLoginModal === 'function') {
                        showLoginModal();
                    } else {
                        alert(skillsprint.i18n.login_required);
                    }
                    return;
                }
                
                // Disable submit button and show loading state
                $submitButton.prop('disabled', true).html('<i class="dashicons dashicons-update spinning"></i> ' + skillsprint.i18n.submitting);
                
                // Prepare form data
                const formData = {
                    action: 'skillsprint_submit_quiz',
                    nonce: skillsprint.nonce,
                    blueprint_id: $quiz.data('blueprint'),
                    quiz_id: $quiz.data('quiz'),
                    day_number: $quiz.data('day') || 1,
                    answers: {}
                };
                
                // Collect answers based on question type
                $form.find('.skillsprint-question').each(function() {
                    const $question = $(this);
                    const questionId = $question.data('question');
                    const questionType = $question.data('type');
                    
                    switch (questionType) {
                        case 'multiple_choice':
                        case 'true_false':
                            // Get selected radio button value
                            const radioValue = $question.find('input[type="radio"]:checked').val();
                            if (radioValue) {
                                formData.answers[questionId] = radioValue;
                            }
                            break;
                            
                        case 'multiple_answer':
                            // Get all checked checkbox values
                            const checkboxValues = [];
                            $question.find('input[type="checkbox"]:checked').each(function() {
                                checkboxValues.push($(this).val());
                            });
                            formData.answers[questionId] = checkboxValues;
                            break;
                            
                        case 'matching':
                            // Get matching pairs
                            const matchingPairs = {};
                            $question.find('select').each(function() {
                                const $select = $(this);
                                const leftValue = $select.data('left');
                                const rightValue = $select.val();
                                
                                if (leftValue && rightValue) {
                                    matchingPairs[leftValue] = rightValue;
                                }
                            });
                            formData.answers[questionId] = matchingPairs;
                            break;
                            
                        case 'short_answer':
                            // Get text input value
                            const textValue = $question.find('input[type="text"]').val();
                            if (textValue) {
                                formData.answers[questionId] = textValue;
                            }
                            break;
                    }
                });
                
                // Send AJAX request
                $.post(skillsprint.ajax_url, formData, function(response) {
                    if (response.success) {
                        // Process successful quiz submission
                        self.handleQuizSuccess($quiz, $form, response.data);
                    } else {
                        // Handle error
                        self.handleQuizError($quiz, $form, response.data.message);
                    }
                    
                    // Re-enable submit button
                    $submitButton.prop('disabled', false).text(skillsprint.i18n.submit_quiz);
                }).fail(function(xhr, textStatus, errorThrown) {
                    // Handle AJAX failure
                    self.handleQuizError($quiz, $form, 'Failed to submit quiz: ' + errorThrown);
                    $submitButton.prop('disabled', false).text(skillsprint.i18n.submit_quiz);
                });
            });
        }
        
        /**
         * Handle successful quiz submission
         * 
         * @param {jQuery} $quiz  Quiz container element
         * @param {jQuery} $form  Quiz form element
         * @param {Object} data   Response data
         */
        handleQuizSuccess($quiz, $form, data) {
            const score = data.score;
            const questionResults = data.question_results;
            
            // Hide form temporarily
            $form.hide();
            
            // Show result panel
            const $resultPanel = $quiz.find('.skillsprint-quiz-result');
            if ($resultPanel.length > 0) {
                this.updateResultPanel($resultPanel, score, questionResults);
            } else {
                this.createResultPanel($quiz, score, questionResults);
            }
            
            // Update question feedback
            this.updateQuestionFeedback($form, questionResults);
            
            // If quiz is passed, enable completion button if exists
            if (score.passed) {
                $quiz.attr('data-passed', 'true');
                $('.skillsprint-complete-day-button[data-day="' + ($quiz.data('day') || 1) + '"]').prop('disabled', false);
            }
        }
        
        /**
         * Handle quiz submission error
         * 
         * @param {jQuery} $quiz    Quiz container element
         * @param {jQuery} $form    Quiz form element
         * @param {String} message  Error message
         */
        handleQuizError($quiz, $form, message) {
            const $errorMessage = $form.find('.skillsprint-quiz-message');
            
            if ($errorMessage.length > 0) {
                $errorMessage.addClass('error').text(message);
            } else {
                $form.prepend('<div class="skillsprint-quiz-message error">' + message + '</div>');
            }
            
            // Scroll to error message
            $('html, body').animate({
                scrollTop: $errorMessage.offset().top - 100
            }, 300);
        }
        
        /**
         * Create result panel
         * 
         * @param {jQuery} $quiz           Quiz container element
         * @param {Object} score           Score data
         * @param {Object} questionResults Question results data
         */
        createResultPanel($quiz, score, questionResults) {
            // Create panel HTML
            const resultTitle = score.passed ? 'Congratulations!' : 'Quiz Completed';
            const resultFeedback = score.passed 
                ? 'You passed the quiz! You can now mark this day as completed.' 
                : 'You did not reach the passing score. You can review your answers and try again.';
                
            const $resultPanel = $(`
                <div class="skillsprint-quiz-result">
                    <div class="skillsprint-quiz-result-inner">
                        <div class="skillsprint-quiz-result-header">
                            <h4 class="skillsprint-quiz-result-title">${resultTitle}</h4>
                            <div class="skillsprint-quiz-result-score">
                                <div class="skillsprint-quiz-result-percentage">
                                    <span class="percentage">${score.percentage}%</span>
                                </div>
                                <div class="skillsprint-quiz-result-correct">
                                    ${score.correct_count} of ${score.total_questions} correct
                                </div>
                            </div>
                        </div>
                        
                        <div class="skillsprint-quiz-result-feedback">
                            <p>${resultFeedback}</p>
                            <ul class="skillsprint-quiz-score-details">
                                <li>
                                    <span>Points Earned:</span>
                                    <span>${score.earned_points}/${score.total_points}</span>
                                </li>
                                <li>
                                    <span>Passing Score:</span>
                                    <span>${score.passing_score}%</span>
                                </li>
                                <li>
                                    <span>Attempt:</span>
                                    <span>${score.attempt}/${score.max_attempts > 0 ? score.max_attempts : '∞'}</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="skillsprint-quiz-result-actions">
                            <button type="button" class="skillsprint-button skillsprint-continue-button">
                                ${skillsprint.i18n.continue}
                            </button>
                            ${!score.passed && (score.max_attempts === 0 || score.attempt < score.max_attempts) ? 
                                `<button type="button" class="skillsprint-button secondary skillsprint-retry-button">
                                    ${skillsprint.i18n.retry}
                                </button>` : 
                                ''
                            }
                        </div>
                    </div>
                </div>
            `);
            
            // Add result panel to quiz container
            $quiz.append($resultPanel);
            
            // Scroll to result panel
            $('html, body').animate({
                scrollTop: $resultPanel.offset().top - 100
            }, 300);
        }
        
        /**
         * Update existing result panel
         * 
         * @param {jQuery} $resultPanel    Result panel element
         * @param {Object} score           Score data
         * @param {Object} questionResults Question results data
         */
        updateResultPanel($resultPanel, score, questionResults) {
            // Update title and feedback
            const resultTitle = score.passed ? 'Congratulations!' : 'Quiz Completed';
            const resultFeedback = score.passed 
                ? 'You passed the quiz! You can now mark this day as completed.' 
                : 'You did not reach the passing score. You can review your answers and try again.';
                
            $resultPanel.find('.skillsprint-quiz-result-title').text(resultTitle);
            $resultPanel.find('.skillsprint-quiz-result-percentage .percentage').text(score.percentage + '%');
            $resultPanel.find('.skillsprint-quiz-result-correct').text(score.correct_count + ' of ' + score.total_questions + ' correct');
            $resultPanel.find('.skillsprint-quiz-result-feedback p').text(resultFeedback);
            
            // Update score details
            $resultPanel.find('.skillsprint-quiz-score-details li:eq(0) span:eq(1)').text(score.earned_points + '/' + score.total_points);
            $resultPanel.find('.skillsprint-quiz-score-details li:eq(1) span:eq(1)').text(score.passing_score + '%');
            $resultPanel.find('.skillsprint-quiz-score-details li:eq(2) span:eq(1)').text(score.attempt + '/' + (score.max_attempts > 0 ? score.max_attempts : '∞'));
            
            // Update action buttons
            const $actions = $resultPanel.find('.skillsprint-quiz-result-actions');
            $actions.empty();
            
            $actions.append(`
                <button type="button" class="skillsprint-button skillsprint-continue-button">
                    ${skillsprint.i18n.continue}
                </button>
            `);
            
            if (!score.passed && (score.max_attempts === 0 || score.attempt < score.max_attempts)) {
                $actions.append(`
                    <button type="button" class="skillsprint-button secondary skillsprint-retry-button">
                        ${skillsprint.i18n.retry}
                    </button>
                `);
            }
            
            // Show the result panel
            $resultPanel.show();
            
            // Scroll to result panel
            $('html, body').animate({
                scrollTop: $resultPanel.offset().top - 100
            }, 300);
        }
        
        /**
         * Update question feedback
         * 
         * @param {jQuery} $form           Quiz form element
         * @param {Object} questionResults Question results data
         */
        updateQuestionFeedback($form, questionResults) {
            // Process each question
            $form.find('.skillsprint-question').each(function() {
                const $question = $(this);
                const questionId = $question.data('question');
                const questionType = $question.data('type');
                
                // Check if we have results for this question
                if (questionResults && questionResults[questionId]) {
                    const result = questionResults[questionId];
                    
                    // Remove any existing feedback
                    $question.find('.skillsprint-question-feedback').remove();
                    $question.find('.skillsprint-question-option').removeClass('correct incorrect');
                    
                    // Add feedback based on question type
                    switch (questionType) {
                        case 'multiple_choice':
                        case 'true_false':
                            // Highlight correct and incorrect options
                            $question.find('input[type="radio"]').each(function() {
                                const $radio = $(this);
                                const $option = $radio.closest('.skillsprint-question-option');
                                const optionValue = $radio.val();
                                
                                if (optionValue == result.correct_answer) {
                                    $option.addClass('correct');
                                } else if ($radio.is(':checked')) {
                                    $option.addClass('incorrect');
                                }
                            });
                            break;
                            
                        case 'multiple_answer':
                            // Highlight correct and incorrect options
                            $question.find('input[type="checkbox"]').each(function() {
                                const $checkbox = $(this);
                                const $option = $checkbox.closest('.skillsprint-question-option');
                                const optionValue = $checkbox.val();
                                const isCorrectAnswer = Array.isArray(result.correct_answer) && 
                                                      result.correct_answer.indexOf(optionValue) !== -1;
                                const wasChecked = $checkbox.is(':checked');
                                
                                if (isCorrectAnswer) {
                                    $option.addClass('correct');
                                } else if (wasChecked) {
                                    $option.addClass('incorrect');
                                }
                            });
                            break;
                            
                        case 'matching':
                            // Highlight correct and incorrect pairs
                            $question.find('select').each(function() {
                                const $select = $(this);
                                const $option = $select.closest('.skillsprint-question-option');
                                const leftValue = $select.data('left');
                                const selectedValue = $select.val();
                                const correctValue = result.correct_answer[leftValue];
                                
                                if (selectedValue === correctValue) {
                                    $option.addClass('correct');
                                } else {
                                    $option.addClass('incorrect');
                                }
                            });
                            break;
                            
                        case 'short_answer':
                            // Highlight if answer was correct
                            const $input = $question.find('input[type="text"]');
                            const $option = $input.closest('.skillsprint-question-option');
                            
                            if (result.is_correct) {
                                $option.addClass('correct');
                            } else {
                                $option.addClass('incorrect');
                            }
                            break;
                    }
                    
                    // Add feedback text
                    const feedbackClass = result.is_correct ? 'correct' : 'incorrect';
                    let feedbackText = result.is_correct ? 
                        'Correct!' : 
                        'Incorrect. The correct answer is shown above.';
                        
                    // Add explanation if available
                    if (!result.is_correct && result.explanation) {
                        feedbackText += ' ' + result.explanation;
                    }
                    
                    $question.append(`
                        <div class="skillsprint-question-feedback ${feedbackClass}">
                            ${feedbackText}
                        </div>
                    `);
                }
            });
        }
        
        /**
         * Initialize quiz results handling
         */
        initQuizResults() {
            $(document).on('click', '.skillsprint-continue-button', function() {
                const $quiz = $(this).closest('.skillsprint-quiz');
                const $form = $quiz.find('.skillsprint-quiz-form');
                const $result = $quiz.find('.skillsprint-quiz-result');
                
                // Hide result panel
                $result.hide();
                
                // Show form with feedback
                $form.show();
                
                // Disable all form inputs
                $form.find('input, select, textarea, button[type="submit"]').prop('disabled', true);
            });
        }
        
        /**
         * Initialize quiz retry functionality
         */
        initQuizRetry() {
            $(document).on('click', '.skillsprint-retry-button', function() {
                const $quiz = $(this).closest('.skillsprint-quiz');
                const $form = $quiz.find('.skillsprint-quiz-form');
                const $result = $quiz.find('.skillsprint-quiz-result');
                
                // Hide result panel
                $result.hide();
                
                // Reset form
                $form[0].reset();
                
                // Remove feedback
                $form.find('.skillsprint-question-feedback').remove();
                $form.find('.skillsprint-question-option').removeClass('correct incorrect');
                
                // Enable all form inputs
                $form.find('input, select, textarea, button[type="submit"]').prop('disabled', false);
                
                // Show form
                $form.show();
                
                // Scroll to form
                $('html, body').animate({
                    scrollTop: $form.offset().top - 100
                }, 300);
            });
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.skillsprint-quiz').length) {
            new QuizInterface();
        }
    });

})(jQuery);