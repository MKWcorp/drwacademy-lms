<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mobile extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set(get_settings('timezone'));

        $this->load->database();
        $this->load->library('session');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');

        // CHECK CUSTOM SESSION DATA
        $this->user_model->check_session_data();

        // If user was deleted
        if ($this->session->userdata('user_login') && $this->user_model->get_all_user($this->session->userdata('user_id'))->num_rows() == 0) {
            $this->user_model->session_destroy();
        }

        ini_set('memory_limit', '1024M');
    }

    // ========== DASHBOARD ==========
    public function index()
    {
        $page_data['page_name'] = 'home';
        $page_data['show_back'] = false;
        $this->load->view('mobile/index', $page_data);
    }

    // ========== CATEGORY ==========
    public function category($slug = '')
    {
        $page_data['page_name'] = 'category';
        $page_data['category_slug'] = $slug;
        $page_data['show_back'] = true;
        $this->load->view('mobile/index', $page_data);
    }

    // ========== COURSE DETAIL ==========
    public function course($course_id = 0)
    {
        $course_id = intval($course_id);
        $page_data['page_name'] = 'course';
        $page_data['course_id'] = $course_id;
        $page_data['show_back'] = true;

        // Pre-load certificate data if applicable
        $user_id = intval($this->session->userdata('user_id'));
        if ($user_id && addon_status('certificate')) {
            $progress = course_progress($course_id, $user_id);
            if ($progress >= 100 && $this->crud_model->check_course_enrolled($course_id, $user_id)) {
                $this->load->model('addons/Certificate_model');
                $cert = $this->Certificate_model->check_certificate_eligibility($course_id, $user_id);
                $page_data['cert_data'] = ($cert && is_string($cert)) ? $cert : '';
            }
        }

        $this->load->view('mobile/index', $page_data);
    }

    // ========== DEBUG: Auto-login as user 668 ==========
    public function debug_login()
    {
        $this->session->set_userdata('custom_session_limit', (time()+864000));
        $this->session->set_userdata('user_id', '668');
        $this->session->set_userdata('role_id', '2');
        $this->session->set_userdata('user_login', '1');
        $this->session->set_userdata('name', 'Fajar Setya');
        $this->session->set_userdata('is_instructor', '0');
        redirect(site_url('mobile'), 'refresh');
    }

    // ========== WATCH LESSON ==========
    public function watch($lesson_id = 0)
    {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            redirect(site_url('login'), 'refresh');
        }

        $lesson = $this->crud_model->get_lessons('lesson', $lesson_id)->row_array();
        if (!$lesson) {
            show_404();
        }

        // Check enrollment
        $is_enrolled = $this->crud_model->check_course_enrolled($lesson['course_id'], $user_id);
        if (!$is_enrolled) {
            redirect(site_url('mobile/kelas/' . $lesson['course_id']), 'refresh');
        }
        if (enroll_status($lesson['course_id'], $user_id) != 'valid') {
            redirect(site_url('mobile/kelas/' . $lesson['course_id']), 'refresh');
        }

        $page_data['page_name'] = 'watch';
        $page_data['lesson_id'] = $lesson_id;
        $page_data['show_back'] = true;
        $page_data['body_class'] = 'page-watch';
        $this->load->view('mobile/index', $page_data);
    }

    // ========== QUIZ ==========
    public function quiz($lesson_id = 0)
    {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            redirect(site_url('login'), 'refresh');
        }

        // Redirect to existing mobile quiz handler
        redirect(site_url('home/quiz_mobile_web_view/' . $lesson_id), 'refresh');
    }

    // ========== MY COURSES ==========
    public function my_courses()
    {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name'] = 'my_courses';
        $page_data['show_back'] = false;
        $this->load->view('mobile/index', $page_data);
    }

    // ========== PROFILE ==========
    public function profile()
    {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name'] = 'profile';
        $page_data['show_back'] = false;
        $this->load->view('mobile/index', $page_data);
    }

    // ========== ENROLL (AJAX) ==========
    public function enroll($course_id = 0)
    {
        $user_id = $this->session->userdata('user_id');

        if (!$user_id) {
            echo json_encode(array('success' => false, 'message' => 'Silakan login terlebih dahulu'));
            return;
        }

        $course = $this->crud_model->get_course_by_id($course_id)->row_array();
        if (!$course) {
            echo json_encode(array('success' => false, 'message' => 'Kelas tidak ditemukan'));
            return;
        }

        if ($course['is_free_course'] != 1) {
            echo json_encode(array('success' => false, 'message' => 'Kelas ini berbayar'));
            return;
        }

        $already = $this->crud_model->check_course_enrolled($course_id, $user_id);
        if ($already) {
            echo json_encode(array('success' => true, 'message' => 'Anda sudah terdaftar'));
            return;
        }

        $this->crud_model->enrol_to_free_course($course_id, $user_id);
        echo json_encode(array('success' => true, 'message' => 'Berhasil mendaftar!'));
    }

    // ========== MARK COMPLETE (AJAX) ==========
    public function mark_complete()
    {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(array('success' => false));
            return;
        }

        $lesson_id = $this->input->post('lesson_id');
        $course_id = $this->input->post('course_id');

        if ($lesson_id && $course_id) {
            $this->crud_model->update_watch_history_manually($lesson_id, $course_id, $user_id);
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false));
        }
    }
}
