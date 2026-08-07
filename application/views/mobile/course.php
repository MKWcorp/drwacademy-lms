<?php
$course_id = intval($course_id ?? 0);
$course = $this->crud_model->get_course_by_id($course_id)->row_array();

if (!$course):
	echo '<div class="empty-state"><div class="empty-icon">🔍</div><div class="empty-text">Kelas tidak ditemukan</div></div>';
	return;
endif;

$user_id = intval($this->session->userdata('user_id'));
$is_enrolled = $user_id ? intval($this->crud_model->check_course_enrolled($course_id, $user_id)) : 0;
$enroll_valid = false;
if ($is_enrolled) {
	$enroll_valid = (enroll_status($course_id, $user_id) == 'valid');
}

$progress = 0;
$completed_lesson_ids = array();

if ($user_id) {
	$progress = intval(course_progress($course_id, $user_id));
	$ids = course_progress($course_id, $user_id, 'completed_lesson_ids');
	if (is_array($ids)) {
		$completed_lesson_ids = $ids;
	}
}

$lessons = $this->crud_model->get_lessons('course', $course_id)->result_array();
$total_lessons = count($lessons);

// Find next lesson
$next_lesson_id = null;
foreach ($lessons as $lesson) {
	if (!in_array($lesson['id'], $completed_lesson_ids)) {
		$next_lesson_id = intval($lesson['id']);
		break;
	}
}

$last_watched = null;
if ($user_id) {
	$watch_history = $this->crud_model->get_watch_histories($user_id, $course_id)->row_array();
	if ($watch_history && !empty($watch_history['watching_lesson_id'])) {
		$last_watched = intval($watch_history['watching_lesson_id']);
	}
}

$certificate_eligible = false;
$certificate_url = '';
if (isset($cert_data) && $cert_data != '') {
	$certificate_eligible = true;
	$certificate_url = $cert_data;
}
?>

<div class="course-detail-hero">
	<img src="<?php echo $this->crud_model->get_course_thumbnail_url($course_id); ?>"
		alt="<?php echo htmlspecialchars($course['title']); ?>"
		onerror="this.src='<?php echo base_url('uploads/thumbnails/course_thumbnails/placeholder.png'); ?>'" />
	<div class="course-detail-info">
		<h1 class="course-detail-title"><?php echo htmlspecialchars($course['title']); ?></h1>
		<div class="course-detail-meta">
			<span><i class="fas fa-video"></i> <?php echo $total_lessons; ?> materi</span>
			<?php if (!empty($course['level'])): ?>
				<span><i class="fas fa-signal"></i> <?php echo htmlspecialchars($course['level']); ?></span>
			<?php endif; ?>
		</div>
		<?php if ($is_enrolled && $enroll_valid): ?>
			<div class="progress-bar-wrap">
				<div class="progress-bar-fill" style="width: <?php echo $progress; ?>%;"></div>
			</div>
			<div class="progress-label">Progress: <?php echo round($progress); ?>% (<?php echo count($completed_lesson_ids); ?>/<?php echo $total_lessons; ?> selesai)</div>
		<?php endif; ?>
	</div>
</div>

<?php if (!$is_enrolled): ?>
	<?php if ($course['is_free_course'] == 1): ?>
		<a href="javascript:void(0)" class="btn-primary btn-enroll" onclick="enrollCourse(<?php echo $course_id; ?>, this)">
			🎓 Daftar Kelas (Gratis)
		</a>
	<?php else: ?>
		<a href="<?php echo site_url('home/course/' . rawurlencode(slugify($course['title'])) . '/' . $course_id); ?>" class="btn-primary">
			💳 Beli Kelas
		</a>
	<?php endif; ?>

	<div style="margin-top: 16px;">
		<div class="section-title">📋 Materi (<?php echo $total_lessons; ?>)</div>
		<div class="lesson-list">
			<?php foreach ($lessons as $lesson): ?>
				<div class="lesson-item locked">
					<div class="lesson-status locked">🔒</div>
					<div class="lesson-info">
						<div class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></div>
						<div class="lesson-meta"><?php echo $lesson['lesson_type'] == 'quiz' ? '📝 Quiz' : '🎬 Video'; ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

<?php elseif ($is_enrolled && !$enroll_valid): ?>
	<div style="text-align:center;padding:20px;color:var(--gray-500);">
		⏰ Akses kelas telah berakhir
	</div>

<?php else: ?>
	<?php if ($progress > 0 && $progress < 100 && ($last_watched || $next_lesson_id)): ?>
		<a href="<?php echo site_url('mobile/nonton/' . ($last_watched ? $last_watched : $next_lesson_id)); ?>" class="btn-primary" style="margin-bottom:16px;">
			▶️ Lanjutkan Belajar
		</a>
	<?php elseif ($progress < 100 && $next_lesson_id): ?>
		<a href="<?php echo site_url('mobile/nonton/' . $next_lesson_id); ?>" class="btn-primary" style="margin-bottom:16px;">
			▶️ Mulai Belajar
		</a>
	<?php endif; ?>

	<?php if ($certificate_eligible): ?>
		<div class="certificate-card">
			<div class="cert-icon">🏆</div>
			<div class="cert-title">Selamat! Anda Lulus</div>
			<div class="cert-desc">Anda telah menyelesaikan semua materi</div>
			<a href="<?php echo site_url('uploads/certificates/' . $certificate_url); ?>" class="btn-primary" target="_blank">
				📜 Lihat Sertifikat
			</a>
		</div>
	<?php endif; ?>

	<div style="margin-top: 16px;">
		<div class="section-title">📋 Materi (<?php echo count($completed_lesson_ids); ?>/<?php echo $total_lessons; ?>)</div>
		<div class="lesson-list">
			<?php foreach ($lessons as $lesson):
				$is_completed = in_array($lesson['id'], $completed_lesson_ids);
				$is_current = ($next_lesson_id == $lesson['id']);
				$is_locked = (!$is_completed && $lesson['id'] != $next_lesson_id);
				?>
				<a href="<?php echo $is_locked ? '#' : ($lesson['lesson_type'] == 'quiz' ? site_url('mobile/quiz/' . $lesson['id']) : site_url('mobile/nonton/' . $lesson['id'])); ?>" class="lesson-item <?php echo $is_locked ? 'locked' : ''; ?>">
					<div class="lesson-status <?php echo $is_completed ? 'done' : ($is_current ? 'current' : 'locked'); ?>">
						<?php echo $is_completed ? '✅' : ($is_current ? ($lesson['lesson_type'] == 'quiz' ? '📝' : '▶') : '🔒'); ?>
					</div>
					<div class="lesson-info">
						<div class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></div>
						<div class="lesson-meta">
							<?php echo $lesson['lesson_type'] == 'quiz' ? 'Quiz' : 'Video'; ?>
							<?php echo $is_completed ? ' · Selesai' : ''; ?>
							<?php if (!empty($lesson['duration'])): ?> · <?php echo htmlspecialchars($lesson['duration']); ?><?php endif; ?>
						</div>
					</div>
					<?php if (!$is_locked): ?><div class="lesson-arrow">›</div><?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
