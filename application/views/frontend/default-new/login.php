<?php if(get_frontend_settings('recaptcha_status')): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<!---------- Header Section End  ---------->
    <section class="sign-up my-0 mt-0">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-md-6 d-none d-md-block text-center">
                    <img loading="lazy" width="65%" src="<?php echo site_url('assets/frontend/default-new/image/login-security.gif') ?>">
                </div>
                <div class="col-lg-5 col-md-6 col-sm-12 col-12 ">
                    <div class="sing-up-right">
                        <h3><?php echo get_phrase('Log In'); ?><span>!</span></h3>
                        <p>Jelajahi, belajar, dan berkembang bersama kami. Nikmati perjalanan edukasi yang menyenangkan. Ayo mulai!</p>

<?php
$login_mode = $this->session->flashdata('login_form_mode');
$is_reseller_tab = ($login_mode == 'reseller');
$reseller_id_val = $this->session->flashdata('reseller_id_reseller');
$reseller_hp_val = $this->session->flashdata('reseller_nomor_hp');
?>
                        <!-- Login Method Tabs -->
                        <ul class="nav nav-pills login-tabs mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo $is_reseller_tab ? '' : 'active'; ?>" id="tab-email" type="button" onclick="switchLoginMethod('email')" role="tab">
                                    <i class="fa-solid fa-envelope"></i> Email
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo $is_reseller_tab ? 'active' : ''; ?>" id="tab-reseller" type="button" onclick="switchLoginMethod('reseller')" role="tab">
                                    <i class="fa-solid fa-store"></i> ID Reseller
                                </button>
                            </li>
                        </ul>

                        <!-- Email Login Form -->
                        <form id="form-login-email" action="<?php echo site_url('login/validate_login') ?>" method="post" <?php echo $is_reseller_tab ? 'style="display:none;"' : ''; ?>>
                            <div class="mb-4">
                                <h5><?php echo get_phrase('Your email'); ?></h5>
                                <div class="position-relative">
                                    <i class="fa-solid fa-user"></i>
                                    <input class="form-control" id="email" type="email" name="email" placeholder="<?php echo get_phrase('Enter your email'); ?>">
                                </div>
                            </div>
                            <div class="">
                                <h5><?php echo get_phrase('Password') ?></h5>
                                <div class="position-relative">
                                    <i class="fa-solid fa-key"></i>
                                    <i class="fa-solid fas fa-eye cursor-pointer" onclick="if($('#password').attr('type') == 'text'){$('#password').attr('type', 'password');}else{$('#password').attr('type', 'text');} $(this).toggleClass('fa-eye'); $(this).toggleClass('fa-eye-slash') " style="right: 20px; left: unset;"></i>
                                    <input class="form-control" id="password" type="password" name="password" placeholder="<?php echo get_phrase('Enter your valid password'); ?>">
                                </div>
                                <small class="w-100">
                                    <a class="text-end w-100 text-muted" href="<?php echo site_url('login/forgot_password_request'); ?>"><?php echo get_phrase('Forgot password?'); ?></a>
                                </small>
                            </div>
                            <?php if(get_frontend_settings('recaptcha_status')): ?>
                                <div class="g-recaptcha" data-sitekey="<?php echo get_frontend_settings('recaptcha_sitekey'); ?>"></div>
                            <?php endif; ?>
                            <div class="log-in">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo get_phrase('Log in') ?>
                                </button>
                            </div>
                        </form>

                        <!-- ID Reseller Login Form -->
                        <form id="form-login-reseller" action="<?php echo site_url('login/validate_reseller_login') ?>" method="post" <?php echo $is_reseller_tab ? '' : 'style="display:none;"'; ?>>
                            <div class="mb-4">
                                <h5>ID Reseller</h5>
                                <div class="position-relative">
                                    <i class="fa-solid fa-id-card"></i>
                                    <input class="form-control" name="id_reseller" value="<?php echo html_escape($reseller_id_val); ?>" placeholder="Masukkan ID Reseller Anda (cth: 288-219-1006-2002)">
                                </div>
                            </div>
                            <div class="">
                                <h5>Nomor HP</h5>
                                <div class="position-relative">
                                    <i class="fa-solid fa-phone"></i>
                                    <input class="form-control" name="nomor_hp" value="<?php echo html_escape($reseller_hp_val); ?>" placeholder="Masukkan nomor HP terdaftar">
                                </div>
                                <small class="w-100 text-muted">
                                    Nomor HP digunakan untuk verifikasi data reseller Anda
                                </small>
                            </div>
                            <div class="log-in mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Masuk
                                </button>
                            </div>
                        </form>

                        <div class="another text-center">
                            <p>
                                <?php echo get_phrase('Don`t have an account?') ?>
                                <a href="<?php echo site_url('sign_up') ?>"><?php echo get_phrase('Sign up') ?></a>
                            </p>
                            <h5><?php echo get_phrase('Or') ?></h5>
                        </div>
                        <div class="social-media">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <!-- <button type="button" class="btn btn-primary"><a href="#"><img loading="lazy" src="image/facebook.png"> Facebook</a></button> -->
                                    <?php if(get_settings('fb_social_login')) include "facebook_login.php"; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    function switchLoginMethod(method) {
        if (method === 'email') {
            document.getElementById('form-login-email').style.display = 'block';
            document.getElementById('form-login-reseller').style.display = 'none';
            document.getElementById('tab-email').classList.add('active');
            document.getElementById('tab-reseller').classList.remove('active');
        } else {
            document.getElementById('form-login-email').style.display = 'none';
            document.getElementById('form-login-reseller').style.display = 'block';
            document.getElementById('tab-email').classList.remove('active');
            document.getElementById('tab-reseller').classList.add('active');
        }
    }
    </script>