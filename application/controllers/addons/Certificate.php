<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Certificate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('addons/Certificate_model', 'certificate_model');
    }

    public function generate_certificate($shareable_url = "")
    {
        if (empty($shareable_url)) {
            redirect(site_url('home'), 'refresh');
        }

        $sertifikat = $this->db->get_where('certificates', array(
            'shareable_url' => $shareable_url
        ))->row_array();

        if (empty($sertifikat)) {
            $this->session->set_flashdata('error_message', 'Sertifikat tidak ditemukan');
            redirect(site_url('home'), 'refresh');
        }

        $siswa  = $this->user_model->get_all_user($sertifikat['student_id'])->row_array();
        $kursus = $this->crud_model->get_course_by_id($sertifikat['course_id'])->row_array();
        $history = $this->crud_model->get_watch_histories(
            $sertifikat['student_id'],
            $sertifikat['course_id']
        )->row_array();

        $data['siswa']           = $siswa;
        $data['kursus']          = $kursus;
        $data['sertifikat']      = $sertifikat;
        $data['tanggal_selesai'] = !empty($history['completed_date'])
            ? date('d F Y', $history['completed_date'])
            : date('d F Y');

        $data['gambar_sertifikat'] = base_url('uploads/certificates/' . $sertifikat['shareable_url']);
        $data['page_title']        = 'Sertifikat - ' . $siswa['first_name'] . ' ' . $siswa['last_name'];

        $this->load->view('certificate/index.php', $data);
    }

    public function certificate_progress($course_id = "")
    {
        $user_id = $this->session->userdata('user_id');

        if (empty($user_id) || empty($course_id)) {
            echo json_encode(array(
                'html' => array(
                    'elem' => '#certificate-content',
                    'content' => '<p class="text-muted text-center">Silakan login untuk melihat sertifikat</p>'
                )
            ));
            return;
        }

        $progress = course_progress($course_id, $user_id);

        if ($progress >= 100) {
            $sertifikat = $this->db->get_where('certificates', array(
                'course_id'  => $course_id,
                'student_id' => $user_id
            ));

            if ($sertifikat->num_rows() > 0) {
                $url = site_url('certificate/' . $sertifikat->row('shareable_url'));
                $html = '<div class="text-center mt-4">';
                $html .= '<h5 class="mb-3">Selamat! Anda telah menyelesaikan kursus ini</h5>';
                $html .= '<p class="mb-3">Sertifikat Anda sudah tersedia</p>';
                $html .= '<a href="' . $url . '" target="_blank" class="btn btn-primary">';
                $html .= '<i class="fas fa-certificate"></i> Lihat Sertifikat';
                $html .= '</a>';
                $html .= '</div>';
            } else {
                $html = '<div class="text-center mt-4">';
                $html .= '<h5 class="mb-3">Kursus selesai!</h5>';
                $html .= '<p class="mb-3">Muat ulang halaman untuk membuat sertifikat</p>';
                $html .= '<button class="btn btn-primary" onclick="location.reload()">';
                $html .= '<i class="fas fa-sync"></i> Muat Ulang';
                $html .= '</button>';
                $html .= '</div>';
            }
        } else {
            $html = '<div class="text-center mt-4">';
            $html .= '<h5 class="mb-3">Progress Kursus: ' . round($progress) . '%</h5>';
            $html .= '<p class="mb-3">Selesaikan semua lesson untuk mendapatkan sertifikat</p>';
            $html .= '</div>';
        }

        echo json_encode(array(
            'html' => array(
                'elem' => '#certificate-content',
                'content' => $html
            )
        ));
    }

    public function settings()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        check_permission('certificate');

        $page_data['page_name']  = 'certificate_settings';
        $page_data['page_title'] = 'Pengaturan Sertifikat';
        $this->load->view('backend/index.php', $page_data);
    }
}
