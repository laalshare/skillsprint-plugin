/**
 * Quiz Builder JavaScript
 *
 * @package    SkillSprint
 * @subpackage SkillSprint/admin/js
 */

(function($) {
    'use strict';

    /**
     * Quiz Builder class
     */
    class QuizBuilder {
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
            this.initQuestionTypeHandlers();
            this.initQuestionActions();
            this.initQuizSaving();
        }
        
        /**
         * Initialize question type change handlers
         */
        initQuestionTypeHandlers() {
            $(document).on('change', '.skillsprint-quiz-question-type', function() {
                const $question = $(this).closest('.skillsprint-quiz-question');
                const type = $(this).val();
                
                // Hide all question type specific fields
                $question.find('.skillsprint-quiz-options-field, .skillsprint-quiz-multiple-answer-field, .skillsprint-quiz-true-false-field, .skillsprint-quiz-matching-field, .skillsprint-quiz-short-answer-field').hide();
                
                // Show the appropriate field based on question type
                switch (type) {
                    case 'multiple_choice':
                        $question.find('.skillsprint-quiz-options-field').show();
                        break;
                    case 'multiple_answer':
                        $question.find('.skillsprint-quiz-multiple-answer-field').show();
                        break;
                    case 'true_false':
                        $question.find('.skillsprint-quiz-true-false-field').show();
                        break;
                    case 'matching':
                        $question.find('.skillsprint-quiz-matching-field').show();
                        break;
                    case 'short_answer':
                        $question.find('.skillsprint-quiz-short-answer-field').show();
                        break;
                }
            });
        }
        
        /**
         * Initialize question actions (add, edit, remove, save)
         */
        initQuestionActions() {
            // Add question
            $(document).on('click', '.skillsprint-add-question', function() {
                const $container = $(this).prev('.skillsprint-quiz-questions-container');
                const questionId = 'q_' + Date.now();
                
                const $newQuestion = $(`
                    <div class="skillsprint-quiz-question" data-question-type="multiple_choice">
                        <div class="skillsprint-quiz-question-header">
                            <h4 class="skillsprint-quiz-question-title">New Question</h4>
                            <div class="skillsprint-quiz-question-actions">
                                <button type="button" class="button button-secondary skillsprint-edit-question">Edit</button>
                                <button type="button" class="button button-secondary skillsprint-remove-question">Remove</button>
                            </div>
                        </div>
                        
                        <div class="skillsprint-quiz-question-preview">
                            <div class="skillsprint-quiz-question-type">Multiple Choice</div>
                        </div>
                        
                        <div class="skillsprint-quiz-question-form">
                            <input type="hidden" class="skillsprint-quiz-question-id" value="${questionId}">
                            
                            <div class="skillsprint-quiz-field">
                                <label>Question Text</label>
                                <textarea class="skillsprint-quiz-question-text large-text" rows="2"></textarea>
                            </div>
                            
                            <div class="skillsprint-quiz-field">
                                <label>Question Type</label>
                                <select class="skillsprint-quiz-question-type">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="true_false">True/False</option>
                                    <option value="multiple_answer">Multiple Answer</option>
                                    <option value="matching">Matching</option>
                                    <option value="short_answer">Short Answer</option>
                                </select>
                            </div>
                            
                            <!-- Multiple Choice Options -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-options-field">
                                <label>Answer Options</label>
                                <div class="skillsprint-quiz-options-container">
                                    <div class="skillsprint-quiz-option">
                                        <input type="text" class="skillsprint-quiz-option-text regular-text" value="Option 1">
                                        <label>
                                            <input type="radio" class="skillsprint-quiz-option-correct" checked>
                                            Correct
                                        </label>
                                        <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                                    </div>
                                    <div class="skillsprint-quiz-option">
                                        <input type="text" class="skillsprint-quiz-option-text regular-text" value="Option 2">
                                        <label>
                                            <input type="radio" class="skillsprint-quiz-option-correct">
                                            Correct
                                        </label>
                                        <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                                    </div>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-add-option">Add Option</button>
                            </div>
                            
                            <!-- Multiple Answer Options -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-multiple-answer-field" style="display: none;">
                                <label>Answer Options</label>
                                <div class="skillsprint-quiz-options-container">
                                    <div class="skillsprint-quiz-option">
                                        <input type="text" class="skillsprint-quiz-option-text regular-text" value="Option 1">
                                        <label>
                                            <input type="checkbox" class="skillsprint-quiz-option-correct" checked>
                                            Correct
                                        </label>
                                        <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                                    </div>
                                    <div class="skillsprint-quiz-option">
                                        <input type="text" class="skillsprint-quiz-option-text regular-text" value="Option 2">
                                        <label>
                                            <input type="checkbox" class="skillsprint-quiz-option-correct">
                                            Correct
                                        </label>
                                        <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                                    </div>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-add-option">Add Option</button>
                            </div>
                            
                            <!-- True/False -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-true-false-field" style="display: none;">
                                <label>Correct Answer</label>
                                <div class="skillsprint-quiz-true-false-container">
                                    <label>
                                        <input type="radio" class="skillsprint-quiz-true-false-correct" name="skillsprint-quiz-true-false-new" value="true" checked>
                                        True
                                    </label>
                                    <label>
                                        <input type="radio" class="skillsprint-quiz-true-false-correct" name="skillsprint-quiz-true-false-new" value="false">
                                        False
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Matching -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-matching-field" style="display: none;">
                                <label>Matching Pairs</label>
                                <div class="skillsprint-quiz-matching-container">
                                    <div class="skillsprint-quiz-matching-pair">
                                        <input type="text" class="skillsprint-quiz-matching-left regular-text" value="" placeholder="Left">
                                        <span class="dashicons dashicons-arrow-right-alt"></span>
                                        <input type="text" class="skillsprint-quiz-matching-right regular-text" value="" placeholder="Right">
                                        <button type="button" class="button button-secondary skillsprint-remove-matching-pair">Remove</button>
                                    </div>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-add-matching-pair">Add Pair</button>
                            </div>
                            
                            <!-- Short Answer -->
                            <div class="skillsprint-quiz-field skillsprint-quiz-short-answer-field" style="display: none;">
                                <label>Acceptable Answers</label>
                                <div class="skillsprint-quiz-short-answer-container">
                                    <div class="skillsprint-quiz-short-answer">
                                        <input type="text" class="skillsprint-quiz-short-answer-text regular-text" value="">
                                        <button type="button" class="button button-secondary skillsprint-remove-short-answer">Remove</button>
                                    </div>
                                </div>
                                <button type="button" class="button button-secondary skillsprint-add-short-answer">Add Acceptable Answer</button>
                            </div>
                            
                            <div class="skillsprint-quiz-field">
                                <label>Explanation</label>
                                <textarea class="skillsprint-quiz-question-explanation large-text" rows="2"></textarea>
                            </div>
                            
                            <div class="skillsprint-quiz-field">
                                <label>Points</label>
                                <input type="number" class="skillsprint-quiz-question-points small-text" value="1" min="1">
                            </div>
                            
                            <div class="skillsprint-quiz-question-actions">
                                <button type="button" class="button button-primary skillsprint-save-question">Save Question</button>
                                <button type="button" class="button button-secondary skillsprint-cancel-question">Cancel</button>
                            </div>
                        </div>
                    </div>
                `);
                
                $container.append($newQuestion);
                $newQuestion.find('.skillsprint-quiz-question-form').show();
                $newQuestion.find('.skillsprint-quiz-question-preview').hide();
            });
            
            // Edit question
            $(document).on('click', '.skillsprint-edit-question', function() {
                const $question = $(this).closest('.skillsprint-quiz-question');
                $question.find('.skillsprint-quiz-question-preview').hide();
                $question.find('.skillsprint-quiz-question-form').show();
            });
            
            // Cancel question edit
            $(document).on('click', '.skillsprint-cancel-question', function() {
                const $question = $(this).closest('.skillsprint-quiz-question');
                $question.find('.skillsprint-quiz-question-form').hide();
                $question.find('.skillsprint-quiz-question-preview').show();
            });
            
            // Save question
            $(document).on('click', '.skillsprint-save-question', function() {
                const $question = $(this).closest('.skillsprint-quiz-question');
                const $form = $question.find('.skillsprint-quiz-question-form');
                const $preview = $question.find('.skillsprint-quiz-question-preview');
                
                // Get question data
                const questionId = $form.find('.skillsprint-quiz-question-id').val();
                const questionText = $form.find('.skillsprint-quiz-question-text').val();
                const questionType = $form.find('.skillsprint-quiz-question-type').val();
                const questionExplanation = $form.find('.skillsprint-quiz-question-explanation').val();
                const questionPoints = $form.find('.skillsprint-quiz-question-points').val();
                
                // Build question data based on type
                let correctAnswer;
                let options = [];
                
                switch (questionType) {
                    case 'multiple_choice':
                        $form.find('.skillsprint-quiz-options-field .skillsprint-quiz-option').each(function(index) {
                            const optionText = $(this).find('.skillsprint-quiz-option-text').val();
                            const isCorrect = $(this).find('.skillsprint-quiz-option-correct').is(':checked');
                            
                            options.push(optionText);
                            
                            if (isCorrect) {
                                correctAnswer = index;
                            }
                        });
                        break;
                        
                    case 'multiple_answer':
                        correctAnswer = [];
                        
                        $form.find('.skillsprint-quiz-multiple-answer-field .skillsprint-quiz-option').each(function(index) {
                            const optionText = $(this).find('.skillsprint-quiz-option-text').val();
                            const isCorrect = $(this).find('.skillsprint-quiz-option-correct').is(':checked');
                            
                            options.push(optionText);
                            
                            if (isCorrect) {
                                correctAnswer.push(index);
                            }
                        });
                        break;
                        
                    case 'true_false':
                        correctAnswer = $form.find('.skillsprint-quiz-true-false-correct:checked').val();
                        break;
                        
                    case 'matching':
                        correctAnswer = {};
                        options = [];
                        
                        $form.find('.skillsprint-quiz-matching-pair').each(function() {
                            const leftText = $(this).find('.skillsprint-quiz-matching-left').val();
                            const rightText = $(this).find('.skillsprint-quiz-matching-right').val();
                            
                            if (leftText && rightText) {
                                options.push({
                                    left: leftText,
                                    right: rightText
                                });
                                
                                correctAnswer[leftText] = rightText;
                            }
                        });
                        break;
                        
                    case 'short_answer':
                        correctAnswer = [];
                        
                        $form.find('.skillsprint-quiz-short-answer-text').each(function() {
                            const answerText = $(this).val();
                            
                            if (answerText) {
                                correctAnswer.push(answerText);
                            }
                        });
                        break;
                }
                
                // Update question title
                $question.find('.skillsprint-quiz-question-title').text(questionText);
                
                // Build preview HTML
                let previewHtml = `
                    <div class="skillsprint-quiz-question-type">${questionType.replace('_', ' ').replace(/\\b\\w/g, l => l.toUpperCase())}</div>
                `;
                
                switch (questionType) {
                    case 'multiple_choice':
                        previewHtml += '<ul class="skillsprint-quiz-question-options-preview">';
                        
                        options.forEach((option, index) => {
                            const isCorrect = index === correctAnswer;
                            previewHtml += `
                                <li class="${isCorrect ? 'correct' : ''}">
                                    ${option}
                                    ${isCorrect ? '<span class="dashicons dashicons-yes"></span>' : ''}
                                </li>
                            `;
                        });
                        
                        previewHtml += '</ul>';
                        break;
                        
                    case 'true_false':
                        previewHtml += '<div class="skillsprint-quiz-question-options-preview">';
                        previewHtml += `
                            <div class="${correctAnswer === 'true' ? 'correct' : ''}">
                                True
                                ${correctAnswer === 'true' ? '<span class="dashicons dashicons-yes"></span>' : ''}
                            </div>
                            <div class="${correctAnswer === 'false' ? 'correct' : ''}">
                                False
                                ${correctAnswer === 'false' ? '<span class="dashicons dashicons-yes"></span>' : ''}
                            </div>
                        `;
                        previewHtml += '</div>';
                        break;
                        
                    case 'multiple_answer':
                        previewHtml += '<ul class="skillsprint-quiz-question-options-preview">';
                        
                        options.forEach((option, index) => {
                            const isCorrect = correctAnswer.includes(index);
                            previewHtml += `
                                <li class="${isCorrect ? 'correct' : ''}">
                                    ${option}
                                    ${isCorrect ? '<span class="dashicons dashicons-yes"></span>' : ''}
                                </li>
                            `;
                        });
                        
                        previewHtml += '</ul>';
                        break;
                        
                    case 'matching':
                        previewHtml += '<table class="skillsprint-quiz-question-matching-preview">';
                        previewHtml += '<tr><th>Left</th><th>Right</th></tr>';
                        
                        options.forEach(option => {
                            previewHtml += `
                                <tr>
                                    <td>${option.left}</td>
                                    <td>${option.right}</td>
                                </tr>
                            `;
                        });
                        
                        previewHtml += '</table>';
                        break;
                        
                    case 'short_answer':
                        previewHtml += '<div class="skillsprint-quiz-question-short-answer-preview">';
                        previewHtml += 'Acceptable answers: <span class="correct">' + correctAnswer.join(', ') + '</span>';
                        previewHtml += '</div>';
                        break;
                }
                
                if (questionExplanation) {
                    previewHtml += `
                        <div class="skillsprint-quiz-question-explanation-preview">
                            <strong>Explanation:</strong> ${questionExplanation}
                        </div>
                    `;
                }
                
                if (questionPoints && parseInt(questionPoints) > 1) {
                    previewHtml += `
                        <div class="skillsprint-quiz-question-points-preview">
                            Points: ${questionPoints}
                        </div>
                    `;
                }
                
                // Update the preview with the new content
                $preview.html(previewHtml);
                
                // Hide form and show preview
                $form.hide();
                $preview.show();
            });
            
            // Remove question
            $(document).on('click', '.skillsprint-remove-question', function() {
                if (confirm(skillsprint.i18n.confirm_delete)) {
                    $(this).closest('.skillsprint-quiz-question').remove();
                }
            });
            
            // Add option button
            $(document).on('click', '.skillsprint-add-option', function() {
                const $container = $(this).prev('.skillsprint-quiz-options-container');
                const isMultipleAnswer = $(this).closest('.skillsprint-quiz-multiple-answer-field').length > 0;
                
                const $newOption = $(`
                    <div class="skillsprint-quiz-option">
                        <input type="text" class="skillsprint-quiz-option-text regular-text" value="New Option">
                        <label>
                            <input type="${isMultipleAnswer ? 'checkbox' : 'radio'}" class="skillsprint-quiz-option-correct">
                            Correct
                        </label>
                        <button type="button" class="button button-secondary skillsprint-remove-option">Remove</button>
                    </div>
                `);
                
                $container.append($newOption);
            });
            
            // Remove option button
            $(document).on('click', '.skillsprint-remove-option', function() {
                $(this).closest('.skillsprint-quiz-option').remove();
            });
            
            // Add matching pair button
            $(document).on('click', '.skillsprint-add-matching-pair', function() {
                const $container = $(this).prev('.skillsprint-quiz-matching-container');
                
                const $newPair = $(`
                    <div class="skillsprint-quiz-matching-pair">
                        <input type="text" class="skillsprint-quiz-matching-left regular-text" value="" placeholder="Left">
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                        <input type="text" class="skillsprint-quiz-matching-right regular-text" value="" placeholder="Right">
                        <button type="button" class="button button-secondary skillsprint-remove-matching-pair">Remove</button>
                    </div>
                `);
                
                $container.append($newPair);
            });
            
            // Remove matching pair button
            $(document).on('click', '.skillsprint-remove-matching-pair', function() {
                $(this).closest('.skillsprint-quiz-matching-pair').remove();
            });
            
            // Add short answer button
            $(document).on('click', '.skillsprint-add-short-answer', function() {
                const $container = $(this).prev('.skillsprint-quiz-short-answer-container');
                
                const $newAnswer = $(`
                    <div class="skillsprint-quiz-short-answer">
                        <input type="text" class="skillsprint-quiz-short-answer-text regular-text" value="">
                        <button type="button" class="button button-secondary skillsprint-remove-short-answer">Remove</button>
                    </div>
                `);
                
                $container.append($newAnswer);
            });
            
            // Remove short answer button
            $(document).on('click', '.skillsprint-remove-short-answer', function() {
                $(this).closest('.skillsprint-quiz-short-answer').remove();
            });
        }
        
        /**
         * Initialize quiz saving
         */
        initQuizSaving() {
            $('.skillsprint-save-quiz').on('click', function() {
                const $button = $(this);
                const $status = $button.next('.skillsprint-quiz-save-status');
                const quizId = $button.data('quiz-id');
                const dayNumber = $button.data('day');
                const blueprintId = $button.data('blueprint-id');
                
                // Disable button and show loading state
                $button.prop('disabled', true).text('Saving...');
                $status.text('');
                
                // Collect quiz data
                const $quizContainer = $button.closest('.skillsprint-quiz-container');
                const quizTitle = $quizContainer.find('.skillsprint-quiz-title').val();
                const quizDescription = $quizContainer.find('.skillsprint-quiz-description').val();
                const passingScore = $quizContainer.find('.skillsprint-quiz-passing-score').val();
                const maxAttempts = $quizContainer.find('.skillsprint-quiz-max-attempts').val();
                
                // Get questions data
                const questions = [];
                
                $quizContainer.find('.skillsprint-quiz-question').each(function() {
                    const $question = $(this);
                    const $form = $question.find('.skillsprint-quiz-question-form');
                    
                    // Get common question data
                    const questionId = $form.find('.skillsprint-quiz-question-id').val();
                    const questionText = $form.find('.skillsprint-quiz-question-text').val();
                    const questionType = $form.find('.skillsprint-quiz-question-type').val();
                    const questionExplanation = $form.find('.skillsprint-quiz-question-explanation').val();
                    const questionPoints = $form.find('.skillsprint-quiz-question-points').val();
                    
                    // Build question data based on type
                    let correctAnswer;
                    let options = [];
                    
                    switch (questionType) {
                        case 'multiple_choice':
                            $form.find('.skillsprint-quiz-options-field .skillsprint-quiz-option').each(function(index) {
                                const optionText = $(this).find('.skillsprint-quiz-option-text').val();
                                const isCorrect = $(this).find('.skillsprint-quiz-option-correct').is(':checked');
                                
                                options.push(optionText);
                                
                                if (isCorrect) {
                                    correctAnswer = index;
                                }
                            });
                            break;
                            
                        case 'multiple_answer':
                            correctAnswer = [];
                            
                            $form.find('.skillsprint-quiz-multiple-answer-field .skillsprint-quiz-option').each(function(index) {
                                const optionText = $(this).find('.skillsprint-quiz-option-text').val();
                                const isCorrect = $(this).find('.skillsprint-quiz-option-correct').is(':checked');
                                
                                options.push(optionText);
                                
                                if (isCorrect) {
                                    correctAnswer.push(index);
                                }
                            });
                            break;
                            
                        case 'true_false':
                            correctAnswer = $form.find('.skillsprint-quiz-true-false-correct:checked').val();
                            break;
                            
                        case 'matching':
                            correctAnswer = {};
                            options = [];
                            
                            $form.find('.skillsprint-quiz-matching-pair').each(function() {
                                const leftText = $(this).find('.skillsprint-quiz-matching-left').val();
                                const rightText = $(this).find('.skillsprint-quiz-matching-right').val();
                                
                                if (leftText && rightText) {
                                    options.push({
                                        left: leftText,
                                        right: rightText
                                    });
                                    
                                    correctAnswer[leftText] = rightText;
                                }
                            });
                            break;
                            
                        case 'short_answer':
                            correctAnswer = [];
                            
                            $form.find('.skillsprint-quiz-short-answer-text').each(function() {
                                const answerText = $(this).val();
                                
                                if (answerText) {
                                    correctAnswer.push(answerText);
                                }
                            });
                            break;
                    }
                    
                    questions.push({
                        id: questionId,
                        type: questionType,
                        text: questionText,
                        options: options,
                        correct_answer: correctAnswer,
                        explanation: questionExplanation,
                        points: parseInt(questionPoints) || 1
                    });
                });
                
                // Build quiz data
                const quizData = {
                    title: quizTitle,
                    description: quizDescription,
                    passing_score: parseInt(passingScore) || 70,
                    max_attempts: parseInt(maxAttempts) || 3,
                    questions: questions
                };
                
                // Send AJAX request to save quiz data
                $.post(skillsprint.ajax_url, {
                    action: 'skillsprint_save_quiz_data',
                    blueprint_id: blueprintId,
                    quiz_id: quizId,
                    quiz_data: quizData,
                    nonce: skillsprint.nonce
                }, function(response) {
                    if (response.success) {
                        $status.text(response.data.message).css('color', 'green');
                    } else {
                        $status.text(response.data.message).css('color', 'red');
                    }
                    
                    $button.prop('disabled', false).text('Save Quiz');
                    
                    // Automatically hide status after 3 seconds
                    setTimeout(function() {
                        $status.text('').css('color', '');
                    }, 3000);
                }).fail(function() {
                    $status.text(skillsprint.i18n.save_error).css('color', 'red');
                    $button.prop('disabled', false).text('Save Quiz');
                });
            });
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        new QuizBuilder();
    });

})(jQuery);