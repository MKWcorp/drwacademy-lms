<div class="search-bar">
	<span class="search-icon">🔍</span>
	<input type="text" id="mobileSearch" placeholder="Cari kelas..." autocomplete="off"
		value="<?php echo html_escape($search_query); ?>" />
</div>

<div class="search-header">
	<div class="search-header-label">Hasil pencarian untuk</div>
	<div class="search-header-query">"<?php echo html_escape($search_query); ?>"</div>
	<div class="search-header-count"><?php echo count($courses); ?> kelas ditemukan</div>
</div>

<?php if (count($courses) == 0): ?>
	<div class="empty-state">
		<div class="empty-icon">🔍</div>
		<div class="empty-text">Tidak ada kelas yang cocok</div>
		<a href="<?php echo site_url('mobile'); ?>" class="btn-primary">Kembali ke Home</a>
	</div>
<?php else: ?>
	<div class="course-feature-grid">
		<?php foreach ($courses as $course):
			$total_lessons = $this->db->get_where('lesson', array('course_id' => $course['id']))->num_rows();
			?>
			<a href="<?php echo site_url('mobile/kelas/' . $course['id']); ?>" class="course-feature-card">
				<img src="<?php echo $this->crud_model->get_course_thumbnail_url($course['id']); ?>"
					alt="<?php echo $course['title']; ?>"
					onerror="this.src='<?php echo base_url('uploads/thumbnails/course_thumbnails/placeholder.png'); ?>'" />
				<div class="feature-body">
					<div class="feature-title"><?php echo $course['title']; ?></div>
					<div class="feature-meta">
						<?php echo $total_lessons; ?> video
						<?php if ($course['is_free_course'] == 1): ?>
							· <span style="color:var(--success);font-weight:600;">GRATIS</span>
						<?php elseif ($course['discount_flag'] == 1): ?>
							· Rp <?php echo number_format($course['discounted_price'], 0, ',', '.'); ?>
						<?php else: ?>
							· Rp <?php echo number_format($course['price'], 0, ',', '.'); ?>
						<?php endif; ?>
					</div>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
