<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jabatan extends CI_Controller{
	
	public function __construct(){
		
		parent::__construct();
		if(!$this->session->userdata('is_login'))redirect('login');
		if(!$this->general->privilege_check(JABATAN,'view'))
		    $this->general->no_access();
		$this->session->set_userdata('menu','user_management');
		$this->load->model('jabatan_model');
		$this->load->model('privilege_model');
	}
	
	private function _render($view,$data = array()){
	    
	    $this->load->view('header',$data);
	    $this->load->view('sidebar');
	    $this->load->view($view,$data);
	    $this->load->view('footer');
	}
	
	public function index(){
	    
	    $data = array('title'=>'Jabatan - Tekno Power');	    
	    $this->_render('jabatan',$data);
		
	}
	
	
	public function get_data(){
	    
	    
	    $limit = $this->config->item('limit');
	    $offset  = $this->uri->segment(3,0);
	    $q     = isset($_POST['q']) ? $_POST['q'] : '';	    
	    $data  = $this->jabatan_model->get_data($offset,$limit,$q);
	    $rows  = $paging = '';
	    $total = $data['total'];
	    
	    if($data['data']){
	        
	        $i= $offset+1;
	        $j= 1;
	        foreach($data['data'] as $r){
	            
	            $rows .='<tr>';
	                
	                $rows .='<td>'.$i.'</td>';
	                $rows .='<td width="20%">'.$r->jabatan.'</td>';
	                $rows .='<td width="40%">'.$r->keterangan.'</td>';
	                $rows .='<td align="center">';
	                
	                $rows .='<a title="Edit" class="a-warning" href="'.base_url().'jabatan/edit/'.$r->id.'">
	                            <i class="fa fa-pencil"></i> Edit
	                        </a> ';
	                
	                //administrator
	                if($r->id !=1){
	                
	                    $rows .='<a title="Delete" class="a-danger" href="'.base_url().'jabatan/delete/'.$r->id.'">
	                            <i class="fa fa-times"></i> Delete
	                        </a> ';
	                }
	               $rows .='<a title="Configure Privilege" class="a-info" href="'.base_url().'jabatan/privilege/proses/'.$r->id.'">
	                            <i class="fa fa-wrench"></i> Privilege
	                        </a>';
	               $rows .='</td>';
	            
	            $rows .='</tr>';
	            
	            ++$i;
	            ++$j;
	        }
	        
	        $paging .= '<li><span class="page-info">Displaying '.($j-1).' Of '.$total.' items</span></i></li>';
            $paging .= $this->_paging($total,$limit);
	        
	       	        
	    	    
	    }else{
	        
	        $rows .='<tr>';
	            $rows .='<td colspan="4">No Data</td>';
	        $rows .='</tr>';
	        
	    }
	    
	    echo json_encode(array('rows'=>$rows,'total'=>$total,'paging'=>$paging));
	}
	
	public function add(){
	    
	    if(!$this->general->privilege_check(JABATAN,'add'))
		    $this->general->no_access();
	    
	    $data = array('title'=>'Add Jabatan - Tekno Power');
        $this->_render('add',$data);		
	    	   
	}
	
	public function edit(){
	    
	    if(!$this->general->privilege_check(JABATAN,'edit'))
		    $this->general->no_access();
	    
	    $id = $this->uri->segment(3);
	    $get = $this->db->get_where('jabatan_user',array('id'=>$id))->row_array();
	    if(!$get)
	        show_404();
	    
	    $readonly = '';
	    if($id==1 or $id==4) //admin and Default
	        $readonly="readonly";
	        
	    $data = array('title'=>'Edit Jabatan - Tekno Power','id'=>$id,'readonly'=>$readonly);
        $this->_render('edit',array_merge($data,$get));		
	    	   
	}
	
	//carefull here...
	public function delete(){
	    
	    if(!$this->general->privilege_check(JABATAN,'remove'))
		    $this->general->no_access();
	    
	    $id = $this->uri->segment(3);
	    if($id==1)
	        show_error("Administrator Cannot be Removed");
	        	    
	    //hapus smw yg berhubungan dgn jabatan
		$this->db->trans_begin();
		    $this->db->delete('jabatan_user',array('id'=>$id));
			$this->db->delete('user',array('jabatan_id'=>$id));
			$this->db->delete('akses_user',array('jabatan_id'=>$id));
		if($this->db->trans_status()==false){
			
			show_error("Error Occured, please repeat");
			echo json_encode(array('status'=>false,'msg'=>'error occured'));
		}else{
			
			$this->db->trans_commit();
			 redirect('jabatan');
		}
	}
	
	public function save(){
	    
	     $data = $this->input->post(null,true);
	     
	     $send = $this->jabatan_model->save($data);
	     if($send)
	        redirect('jabatan');
	}
	
	public function update(){
	    
	     $data = $this->input->post(null,true);
	     $send = $this->jabatan_model->update($data);
	     if($send)
	        redirect('jabatan');
	}
	
	private function _paging($total,$limit){
	
	    $config = array(
                
            'base_url'  => base_url().'jabatan/get_data/',
            'total_rows'=> $total, 
            'per_page'  => $limit,
			'uri_segment'=> 3
        
        );
        $this->pagination->initialize($config); 

        return $this->pagination->create_links();
	}
	
	
}//end of class
