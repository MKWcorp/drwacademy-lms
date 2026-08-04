<?php
$lesson_id = $lesson_id ?? 0;
$lesson = $this->crud_model->get_lessons('lesson', $lesson_id)->row_array();

if (!$lesson):
	echo '<div class="empty-state"><div class="empty-icon">🔍</div><div class="empty-text">Video tidak ditemukan</div></div>';
	return;
endif;

$user_id = $this->session->userdata('user_id');
$course = $this->crud_model->get_course_by_id($lesson['course_id'])->row_array();
$all_lessons = $this->crud_model->get_lessons('course', $course['id'])->result_array();

// Find current index
$current_idx = -1;
foreach ($all_lessons as $i => $l) {
	if ($l['id'] == $lesson_id) { $current_idx = $i; break; }
}

$prev_lesson = $current_idx > 0 ? $all_lessons[$current_idx - 1] : null;
$next_lesson = $current_idx < count($all_lessons) - 1 ? $all_lessons[$current_idx + 1] : null;

// Get completion status
$completed_lesson_ids = $user_id ? course_progress($course['id'], $user_id, 'completed_lesson_ids') : array();
if (!is_array($completed_lesson_ids)) $completed_lesson_ids = array();
$is_completed = in_array($lesson_id, $completed_lesson_ids);

// Determine video source
$video_url = '';
if ($lesson['lesson_type'] == 'video') {
	$video_url = $lesson['video_url'] ?? '';
} elseif ($lesson['lesson_type'] == 'video_url' && !empty($lesson['video_url'])) {
	$video_url = $lesson['video_url'];
} elseif (!empty($lesson['attachment'])) {
	$video_url = $lesson['attachment'];
}
?>

<div class="video-container">
	<?php if (!empty($video_url)): ?>
		<?php if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false): ?>
			<?php
			preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $video_url, $matches);
			$youtube_id = $matches[1] ?? '';
			?>
			<?php if ($youtube_id): ?>
			<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">
				<iframe src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>?rel=0&modestbranding=1"
					style="position:absolute;top:0;left:0;width:100%;height:100%;"
					frameborder="0" allowfullscreen></iframe>
			</div>
			<?php endif; ?>
		<?php elseif (strpos($video_url, 'vimeo.com') !== false): ?>
			<?php
			preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches);
			$vimeo_id = $matches[1] ?? '';
			?>
			<?php if ($vimeo_id): ?>
			<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">
				<iframe src="https://player.vimeo.com/video/<?php echo $vimeo_id; ?>"
					style="position:absolute;top:0;left:0;width:100%;height:100%;"
					frameborder="0" allowfullscreen></iframe>
			</div>
			<?php endif; ?>
		<?php else: ?>
			<video controls playsinline preload="metadata"
				onended="markComplete(<?php echo $lesson_id; ?>, <?php echo $course['id']; ?>)"
				ontimeupdate="trackProgress(this, <?php echo $lesson_id; ?>, <?php echo $course['id']; ?>)">
				<source src="<?php echo $video_url; ?>" type="video/mp4" />
				Browser Anda tidak mendukung pemutaran video.
			</video>
		<?php endif; ?>
	<?php else: ?>
		<div style="background:var(--gray-900);color:white;padding:40px;text-align:center;">
			<div style="font-size:48px;margin-bottom:12px;">🎬</div>
			<div>Tidak ada video tersedia</div>
		</div>
	<?php endif; ?>
</div>

<div class="video-info">
	<div class="video-title"><?php echo ($current_idx + 1) . '. ' . $lesson['title']; ?></div>
	<div class="video-meta">
		<?php echo $course['title']; ?>
		<?php if ($is_completed): ?> · ✅ Selesai<?php endif; ?>
	</div>
</div>

<?php if ($is_completed && isset($video_url) && strpos($video_url, 'youtube.com') === false && strpos($video_url, 'vimeo.com') === false): ?>
	<a href="javascript:void(0)" class="btn-outline" onclick="markComplete(<?php echo $lesson_id; ?>, <?php echo $course['id']; ?>)">
		↩️ Tandai Belum Selesai
	</a>
<?php endif; ?>

<div class="video-nav">
	<?php if ($prev_lesson): ?>
		<a href="<?php echo site_url('mobile/nonton/' . $prev_lesson['id']); ?>" class="btn btn-prev">
			← Sebelumnya
		</a>
	<?php endif; ?>

	<?php if ($next_lesson && $next_lesson['lesson_type'] == 'quiz'): ?>
		<a href="<?php echo site_url('mobile/quiz/' . $next_lesson['id']); ?>" class="btn btn-next">
			<?php echo $is_completed ? '📝 Lanjut Quiz →' : '📝 Quiz →'; ?>
		</a>
	<?php elseif ($next_lesson): ?>
		<a href="<?php echo site_url('mobile/nonton/' . $next_lesson['id']); ?>" class="btn btn-next">
			Selanjutnya →
		</a>
	<?php elseif ($is_completed): ?>
		<a href="<?php echo site_url('mobile/kelas/' . $course['id']); ?>" class="btn btn-next complete">
			✅ Selesai — Kembali
		</a>
	<?php endif; ?>
</div>

<script>
function markComplete(lessonId, courseId) {
	var xhr = new XMLHttpRequest();
	xhr.open('POST', baseUrl + 'mobile/mark-complete', true);
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	xhr.onload = function() {
		location.reload();
	};
	xhr.send('lesson_id=' + lessonId + '&course_id=' + courseId);
}

function trackProgress(video, lessonId, courseId) {
	// Throttle: send every 10 seconds
	if (!video._lastTrack || Date.now() - video._lastTrack > 10000) {
		video._lastTrack = Date.now();
		var percent = (video.currentTime / video.duration) * 100;
		if (percent > 90 && !video._autoCompleted) {
			video._autoCompleted = true;
			markComplete(lessonId, courseId);
		}
	}
}
</script>
