<!-- Search Bar -->
<div class="search-bar">
	<span class="search-icon">🔍</span>
	<input type="text" id="mobileSearch" placeholder="Cari kelas..." autocomplete="off" />
</div>

<!-- Categories -->
<div class="section-title">Kategori</div>
<div class="category-grid">
	<?php
	$categories = $this->crud_model->get_categories()->result_array();
	foreach ($categories as $category): ?>
		<a href="<?php echo site_url('mobile/kategori/' . $category['slug']); ?>" class="category-card">
			<span class="cat-icon"><i class="<?php echo $category['font_awesome_class']; ?>"></i></span>
			<span class="cat-name"><?php echo $category['name']; ?></span>
		</a>
	<?php endforeach; ?>
</div>

<!-- Featured Courses -->
<?php $top_courses = $this->crud_model->get_top_courses()->result_array(); ?>
<?php if (count($top_courses) > 0): ?>
<div class="section-title">Kelas Populer</div>
<div class="course-feature-grid">
	<?php foreach ($top_courses as $course):
		$instructor = $this->user_model->get_all_user($course['creator'])->row_array();
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
					<?php endif; ?>
				</div>
			</div>
		</a>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- All Courses -->
<div class="section-title">Semua Kelas</div>
<div class="course-feature-grid">
	<?php
	$all_courses = $this->db->where('status', 'active')->order_by('id', 'DESC')->limit(20)->get('course')->result_array();
	foreach ($all_courses as $course):
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
