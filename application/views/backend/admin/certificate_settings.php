<?php
    $warna_teks = $this->db->get_where('settings', array('key' => 'cert_text_color'))->row('value') ?: '#000000';
    $ukuran_nama = $this->db->get_where('settings', array('key' => 'cert_name_size'))->row('value') ?: '52';
    $ukuran_kursus = $this->db->get_where('settings', array('key' => 'cert_course_size'))->row('value') ?: '36';
?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3">Pengaturan Sertifikat</h4>

                <form action="<?php echo site_url('addons/certificate/settings'); ?>" method="post" enctype="multipart/form-data">

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label" for="template">Template Sertifikat</label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="form-control" id="template" name="template" accept="image/*">
                                </div>
                            </div>
                            <small class="text-muted">
                                Template saat ini: uploads/certificates/template.jpg (1600 x 1131 px)
                            </small>
                            <?php if (file_exists('uploads/certificates/template.jpg')): ?>
                                <div class="mt-2">
                                    <img src="<?php echo base_url('uploads/certificates/template.jpg'); ?>" style="max-width: 300px; border: 1px solid #ddd; border-radius: 6px;">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label">Warna Teks Utama</label>
                        <div class="col-md-9">
                            <input type="color" name="cert_text_color" value="<?php echo $warna_teks; ?>" class="form-control form-control-color" style="width: 80px;">
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label">Ukuran Font Nama</label>
                        <div class="col-md-9">
                            <input type="number" name="cert_name_size" value="<?php echo $ukuran_nama; ?>" class="form-control" min="20" max="120" style="width: 120px;">
                            <small class="text-muted">Ukuran dalam px (default: 52)</small>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label">Ukuran Font Nama Kursus</label>
                        <div class="col-md-9">
                            <input type="number" name="cert_course_size" value="<?php echo $ukuran_kursus; ?>" class="form-control" min="16" max="80" style="width: 120px;">
                            <small class="text-muted">Ukuran dalam px (default: 36)</small>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label">QR Code Generator</label>
                        <div class="col-md-9">
                            <?php
                            $qr_status = $this->db->get_where('settings', array('key' => 'cert_qr_enabled'))->row('value');
                            ?>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="cert_qr_enabled" name="cert_qr_enabled" value="1" <?php echo ($qr_status == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="cert_qr_enabled">Aktifkan QR Code verifikasi</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <div class="col-md-9 offset-md-3">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
