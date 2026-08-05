<?php $quiz_results = $quiz_results->row_array(); ?>
<?php $user_all_answers = json_decode(strtolower($quiz_results['user_answers']), true); ?>
<?php $my_correct_answer_question_ids = json_decode(strtolower($quiz_results['correct_answers']), true); ?>
<?php $total_marks = is_array(json_decode($lesson_details['attachment'], true)) ? json_decode($lesson_details['attachment'], true)['total_marks'] : 0; ?>
<?php $obtained = intval($quiz_results['total_obtained_marks']); ?>
<?php $percent = $total_marks > 0 ? round($obtained / $total_marks * 100) : 0; ?>

<div class="quiz-result">
    <div class="quiz-result-score">
        <div class="quiz-result-score-circle <?php echo $percent >= 70 ? 'pass' : 'fail'; ?>">
            <div class="quiz-result-score-num"><?php echo $obtained; ?></div>
            <div class="quiz-result-score-total">dari <?php echo $total_marks; ?></div>
        </div>
        <div class="quiz-result-score-label">
            <?php if($percent >= 70): ?>
                🎉 Selamat, kamu lulus!
            <?php else: ?>
                💪 Tetap semangat, coba lagi ya!
            <?php endif; ?>
        </div>
    </div>

    <div class="quiz-result-count">
        <span style="color:var(--success);">✅ <?php echo count($my_correct_answer_question_ids); ?> benar</span>
        <span style="color:#DC3545;">❌ <?php echo count($user_all_answers) - count($my_correct_answer_question_ids); ?> salah</span>
    </div>

    <div class="quiz-result-detail-title">📋 Detail Jawaban</div>

    <?php foreach($quiz_questions->result_array() as $question_number => $quiz_question): ?>
    <?php $question_number++; ?>
    <?php
        if(array_key_exists($quiz_question['id'], $user_all_answers)) {
            $user_answers = $user_all_answers[$quiz_question['id']];
        } else {
            $user_answers = array();
        }
        $is_correct = in_array($quiz_question['id'], $my_correct_answer_question_ids);
    ?>

    <div class="quiz-result-q <?php echo $is_correct ? 'correct' : 'wrong'; ?>">
        <div class="quiz-result-q-header">
            <span class="quiz-result-q-status"><?php echo $is_correct ? '✅' : '❌'; ?></span>
            <span class="quiz-result-q-num">Soal <?php echo $question_number; ?></span>
        </div>
        <div class="quiz-result-q-title"><?php echo remove_js(htmlspecialchars_decode_($quiz_question['title'])); ?></div>

        <?php if($question_number <= count($quiz_questions->result_array())): ?>
            <?php if(!$is_correct && !empty($quiz_question['correct_answers'])): ?>
            <div class="quiz-result-q-answer">
                Jawaban benar:
                <?php foreach(json_decode($quiz_question['correct_answers'], true) as $ca):
                    $opts = json_decode($quiz_question['options'], true);
                    $idx = intval($ca) - 1;
                    echo isset($opts[$idx]) ? htmlspecialchars($opts[$idx]) : htmlspecialchars($ca);
                endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php
        $total_attempted = $this->db->where('quiz_id', $lesson_details['id'])->get('quiz_results')->num_rows();
    ?>
    <?php if($lesson_details['quiz_attempt'] > ($total_attempted - 1)): ?>
        <button class="quiz-retake-btn" onclick="retakeQuiz()">🔄 Coba Lagi</button>
        <script>
        function retakeQuiz() {
            if (!confirm('Mau coba ulang quiz?')) return;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo site_url('user/start_quiz/'.$lesson_details['id'].'/retake'); ?>', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() { location.reload(); };
            xhr.send();
        }
        </script>
    <?php endif; ?>
</div>
