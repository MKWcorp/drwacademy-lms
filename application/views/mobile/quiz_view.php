<?php
    $user_id = (isset($user_id) && $user_id > 0) ? $user_id : $this->session->userdata('user_id');
    $quiz_id = $lesson_details['id'];
    $is_course_instructor = $this->crud_model->is_course_instructor($course_details['id'], $user_id);
    if($this->session->userdata('admin_login')) $is_course_instructor = 1;

    $quiz_submission_checker = $this->user_model->quiz_submission_checker($lesson_details['id']);
    $quiz_questions = $this->crud_model->get_quiz_questions($lesson_details['id']);
    $total_marks = is_array(json_decode($lesson_details['attachment'], true)) ? json_decode($lesson_details['attachment'], true)['total_marks'] : 0;

    if($is_course_instructor){
        $quiz_results = $this->db->order_by('quiz_result_id', 'desc')->get_where('quiz_results', array('quiz_id' => $lesson_details['id']));
    }else{
        $quiz_results = $this->db->order_by('quiz_result_id', 'desc')->get_where('quiz_results', array('quiz_id' => $lesson_details['id'], 'user_id' => $user_id));
    }

    if($quiz_results->num_rows() > 0 && !$is_course_instructor){
        $available_time = (time_to_seconds($lesson_details['duration']) + $quiz_results->row('date_added')) - time();
    }else{
        $available_time = time_to_seconds($lesson_details['duration']);
    }
    $timer = time_to_seconds($lesson_details['duration']);
?>

<?php if(isset($_GET['student_id']) && $_GET['student_id'] > 0): ?>
    <?php $preloaded_result_id = $this->db->get_where('quiz_results', ['user_id' => $_GET['student_id'], 'quiz_id' => $quiz_id])->row('quiz_result_id'); ?>
<?php endif; ?>

<div class="quiz-page">
    <div class="quiz-page-header">
        <div class="quiz-page-title"><?php echo $lesson_details['title']; ?></div>
        <div class="quiz-page-meta">
            <?php echo $quiz_questions->num_rows(); ?> soal · <?php echo $total_marks; ?> nilai
        </div>
    </div>

    <?php if($quiz_submission_checker == 'submitted'): ?>
        <?php include 'quiz_result.php'; ?>
    <?php else: ?>
        <?php if(!$is_course_instructor && $quiz_submission_checker != 'on_progress'): ?>
        <div class="quiz-intro">
            <div class="quiz-intro-icon">📝</div>
            <div class="quiz-intro-title">Siap Mengerjakan?</div>
            <div class="quiz-intro-sub">
                <?php echo $quiz_questions->num_rows(); ?> soal pilihan ganda · nilai maksimal <?php echo $total_marks; ?>
                <?php if($timer > 0): ?><br>Waktu: <?php echo gmdate('H:i:s', $timer); ?><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($is_course_instructor): ?>
            <div class="quiz-instructor">
                <p><strong>Peserta:</strong> <?php echo $quiz_results->num_rows(); ?> siswa</p>
                <select onchange="viewAnswerSheet(this.value)" class="quiz-instructor-select">
                    <option value="">— Pilih Siswa —</option>
                    <?php foreach($quiz_results->result_array() as $participant_student):
                        $student_details = $this->user_model->get_all_user($participant_student['user_id'])->row_array(); ?>
                        <option value="<?php echo $participant_student['quiz_result_id']; ?>" <?php if(isset($preloaded_result_id) && $preloaded_result_id == $participant_student['quiz_result_id']) echo 'selected'; ?>>
                            <?php echo $student_details['first_name'].' '.$student_details['last_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="viewAnswerSheet" class="quiz-instructor-answers"></div>
            </div>
        <?php else: ?>

            <?php if($timer > 0): ?>
                <div class="quiz-timer">
                    <div class="quiz-timer-icon">⏱️</div>
                    <div class="quiz-timer-text" id="quizTimer">
                        <?php echo sprintf('%02d:%02d:%02d', floor($available_time/3600), floor(($available_time%3600)/60), $available_time%60); ?>
                    </div>
                    <div class="quiz-timer-label">waktu tersisa</div>
                </div>

                <?php if($quiz_submission_checker != 'on_progress'): ?>
                    <div class="quiz-start-area">
                        <button class="quiz-start-btn" id="quiz-start-btn" onclick="startMobileQuiz()">
                            ▶ Mulai Quiz
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div id="quiz_answer_sheet" style="display:none;">
                <?php include 'quiz_answer_sheet.php'; ?>
            </div>

            <?php if($timer == 0): ?>
                <script>setTimeout(function(){startMobileQuiz();}, 500);</script>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if($timer > 0): ?>
<script>
var timeLeft = <?php echo $available_time; ?>;
var timerInterval;

function updateTimer() {
    if (timeLeft <= 0) {
        clearInterval(timerInterval);
        document.getElementById('finalSubmitForm').submit();
        return;
    }
    timeLeft--;
    var h = Math.floor(timeLeft / 3600);
    var m = Math.floor((timeLeft % 3600) / 60);
    var s = timeLeft % 60;
    document.getElementById('quizTimer').textContent =
        String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
}

function startMobileQuiz() {
    var btn = document.getElementById('quiz-start-btn');
    if (btn) btn.style.display = 'none';
    timerInterval = setInterval(updateTimer, 1000);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo site_url('user/start_quiz/'.$lesson_details['id']); ?>?mobile=1', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function() {
        document.getElementById('quiz_answer_sheet').style.display = '';
    };
    xhr.send();
}

<?php if($quiz_submission_checker == 'on_progress'): ?>
setTimeout(function(){startMobileQuiz();}, 800);
<?php endif; ?>
</script>
<?php endif; ?>

<script>
function viewAnswerSheet(quiz_result_id){
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo site_url('home/view_answer_sheet/'); ?>/' + quiz_result_id, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function() {
        document.getElementById('viewAnswerSheet').innerHTML = xhr.responseText;
    };
    xhr.send();
}
<?php if(isset($_GET['student_id']) && $_GET['student_id'] > 0): ?>
viewAnswerSheet('<?php echo $preloaded_result_id; ?>');
<?php endif; ?>
</script>

<script>
var quizTotal = <?php echo $quiz_questions->num_rows(); ?>;
var quizCurrent = 1;

function navigateQuiz(dir) {
    var newQ = quizCurrent + dir;
    if (newQ < 1 || newQ > quizTotal) return;

    document.getElementById('quizQ' + quizCurrent).style.display = 'none';
    quizCurrent = newQ;
    document.getElementById('quizQ' + quizCurrent).style.display = '';

    document.getElementById('qCurrent').textContent = quizCurrent;
    document.getElementById('qProgressFill').style.width = Math.round(quizCurrent / quizTotal * 100) + '%';

    document.getElementById('qPrevBtn').disabled = quizCurrent <= 1;
    var isLast = quizCurrent >= quizTotal;
    document.getElementById('qNextBtn').style.display = isLast ? 'none' : '';
    document.getElementById('qSubmitBtn').style.display = isLast ? '' : 'none';
}

function pickOption(qIdx, val, qid, type) {
    var container = document.getElementById('quizQ' + qIdx);
    var options = container.querySelectorAll('.quiz-option');
    for (var i = 0; i < options.length; i++) {
        options[i].classList.remove('selected');
    }
    document.getElementById('opt_' + qIdx + '_' + val).classList.add('selected');

    var formData = new FormData();
    formData.append('answer[]', val);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', baseUrl + 'user/submit_quiz_answer/<?php echo $quiz_id; ?>/' + qid + '/' + type, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
}

function submitFillAnswer(input, quizId, qId, type) {
    var val = input.value.trim();
    if (!val) return;
    var formData = new FormData();
    formData.append('answer[]', val);
    formData.append('answer[]', '');
    formData.append('answer[]', '');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', baseUrl + 'user/submit_quiz_answer/' + quizId + '/' + qId + '/' + type, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
}

function submitQuiz() {
    if (!confirm('Yakin sudah selesai? Jawaban tidak bisa diubah lagi.')) return;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo site_url('user/finish_quize_submission/'.$quiz_id); ?>', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function() {
        try {
            var resp = JSON.parse(xhr.responseText);
            if (resp.status === 'submit') location.reload();
        } catch(e) { location.reload(); }
    };
    xhr.send();
}
</script>

<style>
body.page-watch .app-content { padding: 16px; padding-bottom: 90px; }
</style>
