<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
    class Export_model extends CI_Model {
 
        public function __construct()
        {
            $this->load->database();
        }
        
        public function exportList() {
            $this->db->select('*');
            $this->db->from('partner_new');
            $query = $this->db->get();
            return $query->result();
        }
    }
?>