<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title><?php echo get_settings('system_name'); ?></title>

	<?php include 'includes_top.php'; ?>
	<link rel="stylesheet" href="<?php echo site_url('assets/playing-page/css/mobile-app.css'); ?>">
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">

	<?php $current_page = isset($page_name) ? $page_name : 'home'; ?>

	<!-- App Header -->
	<header class="app-header">
		<?php if (isset($show_back) && $show_back): ?>
			<a href="javascript:history.back()" class="app-header-back">
				← <span>Kembali</span>
			</a>
		<?php else: ?>
			<a href="<?php echo site_url('mobile'); ?>" class="app-header-logo">
				<?php echo get_settings('system_name'); ?>
			</a>
		<?php endif; ?>

		<?php if ($this->session->userdata('user_id') > 0): ?>
			<a href="<?php echo site_url('mobile/profil'); ?>">
				<img class="app-header-avatar"
					src="<?php echo $this->user_model->get_user_image_url($this->session->userdata('user_id')); ?>"
					alt="Profile" />
			</a>
		<?php else: ?>
			<a href="<?php echo site_url('login'); ?>" style="font-size:14px;color:var(--primary);text-decoration:none;font-weight:500;">Masuk</a>
		<?php endif; ?>
	</header>

	<!-- Content -->
	<main class="app-content">
		<?php include $page_name . '.php'; ?>
	</main>

	<!-- Bottom Navigation -->
	<?php if ($this->session->userdata('user_id') > 0): ?>
	<nav class="bottom-nav">
		<a href="<?php echo site_url('mobile'); ?>" class="nav-item <?php echo $current_page == 'home' ? 'active' : ''; ?>">
			<div class="nav-icon-wrap">
				<i class="fas fa-home"></i>
			</div>
			<span>Home</span>
		</a>
		<a href="<?php echo site_url('mobile/kelas-saya'); ?>" class="nav-item <?php echo $current_page == 'my_courses' ? 'active' : ''; ?>">
			<div class="nav-icon-wrap">
				<i class="fas fa-book-open"></i>
			</div>
			<span>Kelas</span>
		</a>
		<a href="<?php echo site_url('mobile/profil'); ?>" class="nav-item <?php echo $current_page == 'profile' ? 'active' : ''; ?>">
			<div class="nav-icon-wrap">
				<i class="fas fa-user"></i>
			</div>
			<span>Profil</span>
		</a>
	</nav>
	<?php endif; ?>

	<?php include 'includes_bottom.php'; ?>
	<script>
		var baseUrl = '<?php echo site_url(); ?>';
	</script>
	<script src="<?php echo site_url('assets/playing-page/js/mobile-app.js'); ?>"></script>
</body>
</html>
