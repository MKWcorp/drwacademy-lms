<?php
$user_details = $this->user_model->get_all_user($this->session->userdata('user_id'))->row_array();
$social_links = json_decode($user_details['social_links'], true);
if (!is_array($social_links)) $social_links = array('twitter' => '', 'facebook' => '', 'linkedin' => '');
?>

<div class="pengaturan-photo">
	<img src="<?php echo $this->user_model->get_user_image_url($user_details['id']); ?>" alt="Avatar" />
	<form action="<?php echo site_url('home/update_profile/update_photo/true'); ?>" method="post" enctype="multipart/form-data" class="pengaturan-photo-form" id="photoForm">
		<input type="file" id="pengaturan-photo-input" name="user_image" accept="image/*" onchange="document.getElementById('photoForm').submit(); this.disabled = true;">
		<label for="pengaturan-photo-input" class="pengaturan-photo-btn">📷 Ganti Foto</label>
	</form>
</div>

<form action="<?php echo site_url('home/update_profile/update_basics'); ?>" method="post" class="pengaturan-form">
	<div class="pengaturan-section">
		<div class="pengaturan-section-title">Informasi Dasar</div>

		<div class="pengaturan-field">
			<label>Nama Depan</label>
			<input type="text" name="first_name" value="<?php echo html_escape($user_details['first_name']); ?>" placeholder="Nama depan" required>
		</div>

		<div class="pengaturan-field">
			<label>Nama Belakang</label>
			<input type="text" name="last_name" value="<?php echo html_escape($user_details['last_name']); ?>" placeholder="Nama belakang">
		</div>

		<div class="pengaturan-field">
			<label>Bio</label>
			<textarea name="biography" rows="4" placeholder="Ceritakan tentang diri Anda..."><?php echo html_escape($user_details['biography']); ?></textarea>
		</div>
	</div>

	<div class="pengaturan-section">
		<div class="pengaturan-section-title">Media Sosial</div>

		<div class="pengaturan-field pengaturan-field-icon">
			<span class="pengaturan-field-icon__icon">𝕏</span>
			<input type="text" name="twitter_link" value="<?php echo html_escape($social_links['twitter']); ?>" placeholder="Link Twitter / X">
		</div>

		<div class="pengaturan-field pengaturan-field-icon">
			<span class="pengaturan-field-icon__icon">ⓕ</span>
			<input type="text" name="facebook_link" value="<?php echo html_escape($social_links['facebook']); ?>" placeholder="Link Facebook">
		</div>

		<div class="pengaturan-field pengaturan-field-icon">
			<span class="pengaturan-field-icon__icon">Ⓛ</span>
			<input type="text" name="linkedin_link" value="<?php echo html_escape($social_links['linkedin']); ?>" placeholder="Link LinkedIn">
		</div>
	</div>

	<button type="submit" class="pengaturan-save">💾 Simpan Perubahan</button>
</form>
