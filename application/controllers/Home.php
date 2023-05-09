<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    
    public function __construct(){
	   
        parent::__construct();
        $this->load->library(array('form_validation','session'));
		$this->load->library('email');
		$this->load->database();	
		
		$this->load->library('pagination');
        $this->load->helper('url_helper');
    } 

	public function index(){	    
		$this->load->view('partners');
	}	
	
	public function import(){
	    error_reporting(0);
	    
	    // load model
        $this->load->model('Import_model', 'import');
        $this->load->helper(array('url','html','form'));
	    
	    date_default_timezone_set('Asia/Kolkata');
	    $c_date             = date("d-m-Y h:i:s A");
	    
	    $type = $this->input->post('type');
	    
	    
	    
	    if($type=="update"){
	        //TRUNCATE table
	        $this->db->truncate('partner');
	    }
	    
	    if ($this->input->post('submit')) {
                 
                $path = 'assets/uploads/';
                require_once APPPATH . "/third_party/PHPExcel.php";
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'xlsx|xls|csv';
                $config['remove_spaces'] = TRUE;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);            
                if (!$this->upload->do_upload('uploadFile')) {
                    $error = array('error' => $this->upload->display_errors());
                } else {
                    $data = array('upload_data' => $this->upload->data());
                }
                if(empty($error)){
                  if (!empty($data['upload_data']['file_name'])) {
                    $import_xls_file = $data['upload_data']['file_name'];
                } else {
                    $import_xls_file = 0;
                }
                $inputFileName = $path . $import_xls_file;
                 
                try {
                    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($inputFileName);
                    $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                    $flag = true;
                    $i=0;
                    foreach ($allDataInSheet as $value) {
                      if($flag){
                        $flag =false;
                        continue;
                      }
                        $inserdata[$i]['admin_id']      = $value['B'];
                        $inserdata[$i]['name']          = $value['C'];
                        $inserdata[$i]['person_name']   = $value['D'];
                        $inserdata[$i]['email']         = $value['E'];
                        $inserdata[$i]['pwd']           = md5($value['F']);
                        $inserdata[$i]['phone']         = $value['G'];
                        $inserdata[$i]['bp_code']       = $value['H'];
                        $inserdata[$i]['state']         = $value['I'];
                        $inserdata[$i]['pincode']       = $value['J'];
                        $inserdata[$i]['city']          = $value['K'];
                        $inserdata[$i]['address']       = $value['L'];
                        $inserdata[$i]['location_id']   = $value['M'];
                        $inserdata[$i]['gst']           = $value['N'];
                        $inserdata[$i]['c_date']        = $c_date;
                        $inserdata[$i]['otp']           = $value['P'];
                        $inserdata[$i]['status']        = $value['Q'];
                      $i++;
                    }               
                    $result = $this->import->importData($inserdata);   
                    if($result){
                        
                        //Remove file from folder
                            $folder_path = "assets/uploads/";
                            // specified folder
                            $files = glob($folder_path.'/*'); 
                            // Deleting all the files in the list
                            foreach($files as $file) {
                                if(is_file($file)){ 
                                    unlink($file);
                                }
                            }
    			        redirect('superadmin/partners?msg=addOk');
    			    }else{
    			        redirect('superadmin/partners?msg=addErr');
    			    }        
      
              } catch (Exception $e) {
                   die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME)
                            . '": ' .$e->getMessage());
                }
              }else{
                  echo $error['error'];
                }
                 
                 
        }
	}
	
	public function export() {
	    error_reporting(0);
	     $this->load->model('Export_model', 'export');
        // create file name
        $fileName = 'Partner-data-'.time().'.xlsx';  
        // load excel library
        $this->load->library('excel');
        $listInfo = $this->export->exportList();
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        // set Header
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'id');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'admin_id');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'name');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'person_name');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'email');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'pwd');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'phone');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'bp_code');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'state');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'pincode');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'city');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'address');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'location_id');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'gst');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'c_date');
        $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'otp');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'status');
        // set Row
        $rowCount = 2;
        foreach ($listInfo as $list) {
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $list->id);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $list->admin_id);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $list->name);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $list->person_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $list->email);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $list->pwd);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $list->phone);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $list->bp_code);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $list->state);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $list->pincode);
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $list->city);
            $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $list->address);
            $objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $list->location_id);
            $objPHPExcel->getActiveSheet()->SetCellValue('N' . $rowCount, $list->gst);
            $objPHPExcel->getActiveSheet()->SetCellValue('O' . $rowCount, $list->c_date);
            $objPHPExcel->getActiveSheet()->SetCellValue('P' . $rowCount, $list->otp);
            $objPHPExcel->getActiveSheet()->SetCellValue('Q' . $rowCount, $list->status);
            $rowCount++;
        }
        $filename = "partner-csv-". date("Y-m-d-H-i-s").".csv";
        $fileName = "partner-". date("Y-m-d-H-i-s A").".xlsx";
        
        header( "Content-type: application/vnd.ms-excel" );
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header("Pragma: no-cache");
        header("Expires: 0");
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        
        redirect('partners');
        
    }
	
	
}








