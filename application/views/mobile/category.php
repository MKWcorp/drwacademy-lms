<?php
$category_slug = $category_slug ?? '';
$category = $this->db->get_where('category', array('slug' => $category_slug))->row_array();

if (!$category): ?>
	<div class="empty-state">
		<div class="empty-icon">🔍</div>
		<div class="empty-text">Kategori tidak ditemukan</div>
	</div>
	<?php return;
endif;

// Get sub-categories
$sub_categories = $this->crud_model->get_sub_categories($category['id']);

// Build array of sub-category IDs
$subcat_ids = array();
if (!empty($sub_categories)) {
	foreach ($sub_categories as $sc) {
		$subcat_ids[] = $sc['id'];
	}
}

// Get courses: all courses where category_id = parent OR sub_category_id IN (sub-IDs)
$this->db->where('status', 'active');
$this->db->group_start();
$this->db->where('category_id', $category['id']);
if (!empty($subcat_ids)) {
	$this->db->or_where_in('sub_category_id', $subcat_ids);
}
$this->db->group_end();
$this->db->order_by('id', 'DESC');
$courses = $this->db->get('course')->result_array();
$count = count($courses);
?>

<div class="category-header">
	<div class="cat-title"><?php echo htmlspecialchars($category['name']); ?></div>
	<div class="cat-count"><?php echo $count; ?> kelas</div>
</div>

<?php if (!empty($sub_categories)): ?>
<div class="subcat-chips">
	<button class="subcat-chip active" data-subcat="all">Semua</button>
	<?php foreach ($sub_categories as $sc): ?>
		<button class="subcat-chip" data-subcat="<?php echo $sc['id']; ?>">
			<?php echo htmlspecialchars($sc['name']); ?>
		</button>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="course-list">
	<?php if ($count == 0): ?>
		<div class="empty-state">
			<div class="empty-icon">📭</div>
			<div class="empty-text">Belum ada kelas di kategori ini</div>
		</div>
	<?php endif; ?>

	<?php foreach ($courses as $course):
		$total_lessons = $this->db->get_where('lesson', array('course_id' => $course['id']))->num_rows();
		$user_id = $this->session->userdata('user_id');
		$is_enrolled = $user_id ? $this->crud_model->check_course_enrolled($course['id'], $user_id) : 0;
		?>
		<a href="<?php echo site_url('mobile/kelas/' . $course['id']); ?>"
			class="course-card"
			data-subcat-id="<?php echo $course['sub_category_id']; ?>"
			data-cat-id="<?php echo $course['category_id']; ?>">
			<img class="course-card-img"
				src="<?php echo $this->crud_model->get_course_thumbnail_url($course['id']); ?>"
				alt="<?php echo htmlspecialchars($course['title']); ?>"
				onerror="this.src='<?php echo base_url('uploads/thumbnails/course_thumbnails/placeholder.png'); ?>'" />
			<div class="course-card-body">
				<div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
				<div class="course-card-meta">
					<span>📹 <?php echo $total_lessons; ?> video</span>
					<?php if ($is_enrolled): ?>
						<span style="color:var(--success);">✅ Terdaftar</span>
					<?php endif; ?>
				</div>
				<?php if ($course['is_free_course'] == 1): ?>
					<div class="course-card-price free">Gratis</div>
				<?php endif; ?>
			</div>
		</a>
	<?php endforeach; ?>
</div>

<script>
(function() {
	var chips = document.querySelectorAll('.subcat-chip');
	var cards = document.querySelectorAll('.course-card[data-subcat-id]');

	chips.forEach(function(chip) {
		chip.addEventListener('click', function() {
			chips.forEach(function(c) { c.classList.remove('active'); });
			this.classList.add('active');

			var filter = this.getAttribute('data-subcat');

			cards.forEach(function(card) {
				if (filter === 'all') {
					card.style.display = '';
				} else {
					var scId = card.getAttribute('data-subcat-id');
					var catId = card.getAttribute('data-cat-id');
					card.style.display = (scId === filter || catId === filter) ? '' : 'none';
				}
			});
		});
	});
})();
</script>
