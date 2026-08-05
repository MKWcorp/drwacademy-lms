<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Certificate_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Cek kelayakan sertifikat & buat jika memenuhi syarat
     */
    public function check_certificate_eligibility($course_id = "", $user_id = "")
    {
        $progress = course_progress($course_id, $user_id);

        if ($progress < 100) {
            return false;
        }

        $course = $this->db->get_where('course', array('id' => $course_id))->row_array();
        if (empty($course['certificate_enabled'])) {
            return false;
        }

        $sudah_ada = $this->db->get_where('certificates', array(
            'course_id'  => $course_id,
            'student_id' => $user_id
        ));

        if ($sudah_ada->num_rows() > 0) {
            return $sudah_ada->row_array()['shareable_url'];
        }

        $identifier = substr(sha1($user_id . '-' . $course_id . '-' . date('d-M-Y')), 0, 10);

        $data = array(
            'course_id'     => $course_id,
            'student_id'    => $user_id,
            'shareable_url' => $identifier . '.jpg'
        );

        $this->db->insert('certificates', $data);
        $this->_buat_gambar_sertifikat($user_id, $course_id, $identifier);

        if (method_exists($this->email_model, 'notify_on_certificate_generate')) {
            $this->email_model->notify_on_certificate_generate($user_id, $course_id);
        }

        return $identifier . '.jpg';
    }

    /**
     * Buat gambar sertifikat JPG dengan GD library
     */
    private function _buat_gambar_sertifikat($user_id, $course_id, $identifier)
    {
        $default_template = FCPATH . 'uploads/certificates/template.jpg';
        $course = $this->db->get_where('course', array('id' => $course_id))->row_array();
        $custom = !empty($course['certificate_template'])
            ? FCPATH . 'uploads/certificates/' . $course['certificate_template']
            : null;
        $template_path = ($custom && file_exists($custom)) ? $custom : $default_template;
        $output_dir    = FCPATH . 'uploads/certificates/';
        $font_bold     = FCPATH . 'assets/backend/fonts/Nunito-Bold.ttf';
        $font_regular  = FCPATH . 'assets/backend/fonts/Nunito-Regular.ttf';

        if (!file_exists($template_path)) {
            log_message('error', 'Sertifikat: template tidak ditemukan');
            return false;
        }

        if (!file_exists($output_dir)) {
            mkdir($output_dir, 0777, true);
            mkdir($output_dir . 'qrcodes', 0777, true);
        }

        $img = imagecreatefromjpeg($template_path);
        if (!$img) {
            log_message('error', 'Sertifikat: gagal membaca template');
            return false;
        }

        $hitam  = imagecolorallocate($img, 40, 40, 40);
        $abu    = imagecolorallocate($img, 80, 80, 80);

        $siswa   = $this->user_model->get_all_user($user_id)->row_array();
        $history = $this->crud_model->get_watch_histories($user_id, $course_id)->row_array();

        $nama_siswa      = $siswa['first_name'] . ' ' . $siswa['last_name'];
        $tanggal_selesai = !empty($history['completed_date'])
            ? date('d F Y', $history['completed_date'])
            : date('d F Y');

        $center_x      = 1050;
        $pos_y_nama    = 557;
        $font_nama     = 50;

        $pos_y_tanggal = 663;
        $font_tanggal  = 22;

        $this->_teks_center($img, $nama_siswa, $font_nama, $center_x, $pos_y_nama, $hitam, $font_bold, 100);
        $this->_teks_center($img, $tanggal_selesai, $font_tanggal, $center_x, $pos_y_tanggal, $abu, $font_regular, 100);

        $output_path = $output_dir . $identifier . '.jpg';
        imagejpeg($img, $output_path, 90);

        return true;
    }

    /**
     * Render teks rata tengah pada titik center_x + auto-shrink jika terlalu panjang
     */
    private function _teks_center($img, $teks, $size, $center_x, $y, $color, $font, $margin = 100)
    {
        if (!file_exists($font)) {
            $w = strlen($teks) * imagefontwidth(5);
            imagestring($img, 5, $center_x - (int)($w / 2), $y, $teks, $color);
            return;
        }

        $canvas_w = imagesx($img);
        $ruang_kiri = $center_x - $margin;
        $ruang_kanan = $canvas_w - $margin - $center_x;
        $max_width = min($ruang_kiri, $ruang_kanan) * 2;

        $bbox = imagettfbbox($size, 0, $font, $teks);
        $teks_w = $bbox[2] - $bbox[0];

        $font_final = $size;
        if ($teks_w > $max_width) {
            $font_final = max(24, (int) ($size * $max_width / $teks_w));
            $bbox = imagettfbbox($font_final, 0, $font, $teks);
            $teks_w = $bbox[2] - $bbox[0];
        }

        $x = (int) ($center_x - $teks_w / 2);
        imagettftext($img, $font_final, 0, max(0, $x), $y, $color, $font, $teks);
    }
}
