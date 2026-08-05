<?php $total = $quiz_questions->num_rows(); ?>

<div class="quiz-sheet">
	<div class="quiz-sheet-progress">
		<div class="quiz-sheet-progress-text">Soal <span id="qCurrent">1</span> dari <?php echo $total; ?></div>
		<div class="quiz-sheet-progress-bar">
			<div class="quiz-sheet-progress-fill" id="qProgressFill" style="width: <?php echo round(1/$total*100); ?>%;"></div>
		</div>
	</div>

	<div class="quiz-sheet-body">
		<?php $qidx = 0; ?>
		<?php foreach($quiz_questions->result_array() as $question_number => $quiz_question): ?>
		<?php $qidx++; ?>
		<div class="quiz-sheet-q" id="quizQ<?php echo $qidx; ?>" style="<?php echo $qidx > 1 ? 'display:none' : ''; ?>">
			<div class="quiz-sheet-q-title">
				<span class="quiz-sheet-q-num"><?php echo $qidx; ?></span>
				<?php echo remove_js(htmlspecialchars_decode_($quiz_question['title'])); ?>
			</div>

			<?php if($quiz_question['type'] == 'multiple_choice' || $quiz_question['type'] == 'single_choice'): ?>
			<div class="quiz-sheet-options">
				<?php foreach(json_decode($quiz_question['options'], true) as $key => $option): ?>
				<?php $val = $key + 1; ?>
				<div class="quiz-option" id="opt_<?php echo $qidx; ?>_<?php echo $val; ?>"
					onclick="pickOption(<?php echo $qidx; ?>, <?php echo $val; ?>, <?php echo $quiz_question['id']; ?>, '<?php echo $quiz_question['type']; ?>')">
					<span class="quiz-option-letter"><?php echo chr(65 + $key); ?></span>
					<span class="quiz-option-text"><?php echo htmlspecialchars($option); ?></span>
				</div>
				<?php endforeach; ?>
			</div>
			<?php elseif($quiz_question['type'] == 'fill_in_the_blank'): ?>
			<div class="quiz-sheet-fill">
				<?php
				$correct_answers = json_decode($quiz_question['correct_answers'], true);
				$title_display = remove_js(htmlspecialchars_decode_($quiz_question['title']));
				foreach($correct_answers as $ca) { $title_display = str_replace($ca, ' <u>&nbsp;&nbsp;&nbsp;&nbsp;</u> ', $title_display); }
				echo $title_display;
				?>
				<?php foreach($correct_answers as $k => $word): ?>
				<input type="text" class="quiz-fill-input" placeholder="Jawaban <?php echo $k+1; ?>" onblur="submitFillAnswer(this, <?php echo $quiz_question['quiz_id']; ?>, <?php echo $quiz_question['id']; ?>, '<?php echo $quiz_question['type']; ?>')">
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>

	<div class="quiz-sheet-nav">
		<button type="button" class="quiz-sheet-nav-btn" id="qPrevBtn" onclick="navigateQuiz(-1)" disabled>← Sebelumnya</button>
		<button type="button" class="quiz-sheet-nav-btn quiz-sheet-nav-btn--next" id="qNextBtn" onclick="navigateQuiz(1)" <?php if($total <= 1) echo 'style="display:none"'; ?>>Selanjutnya →</button>
		<button type="button" class="quiz-sheet-nav-btn quiz-sheet-nav-btn--submit" id="qSubmitBtn" onclick="submitQuiz()" <?php if($total > 1) echo 'style="display:none"'; ?>>✅ Kumpulkan</button>
	</div>
</div>
