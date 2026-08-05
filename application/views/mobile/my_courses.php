<?php
$user_id = $this->session->userdata('user_id');
$enrolled = $this->db
	->select('enrol.*, course.title, course.status, course.is_free_course')
	->from('enrol')
	->join('course', 'course.id = enrol.course_id')
	->where('enrol.user_id', $user_id)
	->order_by('enrol.date_added', 'DESC')
	->get()->result_array();
?>

<div class="section-title">Kelas Saya</div>

<?php if (count($enrolled) == 0): ?>
	<div class="empty-state">
		<div class="empty-icon">📚</div>
		<div class="empty-text">Belum ada kelas yang diikuti</div>
		<a href="<?php echo site_url('mobile'); ?>" class="btn-primary">Jelajahi Kelas</a>
	</div>
<?php else: ?>
	<div class="course-feature-grid">
		<?php foreach ($enrolled as $enrol):
			$course = $this->crud_model->get_course_by_id($enrol['course_id'])->row_array();
			if (!$course || $course['status'] != 'active') continue;
			$progress = course_progress($enrol['course_id'], $user_id);
			$total_lessons = $this->db->get_where('lesson', array('course_id' => $enrol['course_id']))->num_rows();
			$enroll_status = enroll_status($enrol['course_id'], $user_id);
			?>
			<a href="<?php echo site_url('mobile/kelas/' . $course['id']); ?>" class="course-feature-card">
				<img src="<?php echo $this->crud_model->get_course_thumbnail_url($course['id']); ?>"
					alt="<?php echo $course['title']; ?>"
					onerror="this.src='<?php echo base_url('uploads/thumbnails/course_thumbnails/placeholder.png'); ?>'" />
				<div class="feature-body">
					<div class="feature-title"><?php echo $course['title']; ?></div>
					<div class="progress-bar-wrap">
						<div class="progress-bar-fill" style="width: <?php echo $progress; ?>%;"></div>
					</div>
					<div class="feature-meta">
						<?php echo round($progress); ?>% · <?php echo $total_lessons; ?> materi
						<?php if ($enroll_status == 'expired'): ?>
							· <span style="color:#DC3545;">Kadaluarsa</span>
						<?php elseif ($progress >= 100): ?>
							· <span style="color:var(--success);">🏆 Lulus</span>
						<?php endif; ?>
					</div>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
