<?php
$user_id = $this->session->userdata('user_id');
$user = $this->user_model->get_all_user($user_id)->row_array();
?>

<div class="profile-header">
	<img class="profile-avatar"
		src="<?php echo $this->user_model->get_user_image_url($user_id); ?>"
		alt="Avatar" />
	<div class="profile-name"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></div>
	<div class="profile-email"><?php echo $user['email']; ?></div>
</div>

<div class="profile-menu">
	<a href="<?php echo site_url('mobile/kelas-saya'); ?>">
		<span class="menu-icon">📚</span>
		Kelas Saya
		<span class="menu-arrow">›</span>
	</a>

	<?php if ($user['is_instructor'] == 1): ?>
		<a href="<?php echo site_url('user/dashboard'); ?>">
			<span class="menu-icon">🎓</span>
			Dasbor Instruktur
			<span class="menu-arrow">›</span>
		</a>
	<?php endif; ?>

	<a href="<?php echo site_url('home/profile/user_profile'); ?>">
		<span class="menu-icon">⚙️</span>
		Pengaturan
		<span class="menu-arrow">›</span>
	</a>

	<a href="<?php echo site_url('login/logout'); ?>" class="logout">
		<span class="menu-icon">🚪</span>
		Keluar
		<span class="menu-arrow">›</span>
	</a>
</div>
