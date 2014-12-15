<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'core/MY_Crud.php');

class Json_model extends MY_Crud {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_all_entries()
    {
        $sql = sprintf('SELECT * 
				FROM clients
				ORDER BY id DESC');
    	
        $query = $this->executeQuery($sql);
		return $query;
    }
    
	function get_clients()
    {
    	$sql = sprintf('SELECT * 
				FROM clients
				ORDER BY id DESC');
    	
        $query = $this->executeQuery($sql);
		return $query;
    }
    
    function get_users()
    {
    	$sql = sprintf('SELECT * 
				FROM users
				ORDER BY id DESC');
    	
        $query = $this->executeQuery($sql);
		return $query;
    }
    
    function get_posts_user($user_id)
    {
    	$sql = sprintf("SELECT c.name, c.image, c.tel, u.username, u.email, pu.message, pu.date, pu.id
						FROM posts_users pu
						JOIN users u ON u.id = pu.user_id
						JOIN clients c ON c.id = pu.client_id
						WHERE pu.user_id = %d ORDER BY pu.date DESC LIMIT 30" , $user_id);
    	
        $query = $this->executeQuery($sql);
		return $query;
    }
    
	function get_info_user($email)
    {
    	$sql = sprintf("SELECT * 
				FROM users
				WHERE email = '%s'
				ORDER BY id DESC", $email);
    	
        $query = $this->executeQuery($sql);
		return $query;
    }

    function get_info_user2($username)
    {
        $sql = sprintf("SELECT * 
                FROM users
                WHERE username = '%s'
                ORDER BY id DESC", $username);
        
        $query = $this->executeQuery($sql);
        return $query;
    }
    
	function get_info_user_by_id($user_id)
    {
    	$sql = sprintf("SELECT * 
				FROM users
				WHERE id = %d
				ORDER BY id DESC", $user_id);
    	
        $query = $this->executeQuery($sql);
		return $query;
    }
    
	function get_info_user_by_user_pwd($user, $password)
    {

    	$md5_password = md5($password);

    	$sql = sprintf("SELECT * 
				FROM users
				WHERE username = '%s'
				AND LOWER(password) = '%s'
				ORDER BY id DESC", $user, $md5_password);
    	
        $query = $this->executeQuery($sql);

		return $query;
    }

        function get_info_user_by_user_pwd2($user, $password, $parse_token)
    {

        $md5_password = md5($password);

        $sql = sprintf("SELECT * 
                FROM users
                WHERE username = '%s'
                AND LOWER(password) = '%s'
                ORDER BY id DESC", $user, $md5_password);
        
        $query = $this->executeQuery($sql);
        
        if($query){

            $data = array(
               'parse_token' => $parse_token,
            );

            $this->db->where('id', $query[0]["id"]);
            $this->db->update('users', $data); 
        }

        return $query;
    }
    
 	function save_user($data)
    {
    	$the_last_id = 0;
		if (!$this->get_info_user($data['email']) && !$this->get_info_user2($data['username']))
		{
			$the_data = array(	
							"username" => strtolower($data['username']), 
							"email" => $data['email'],
							"password" => md5($data['password']),
							"name" => $data['name'],
							"parse_token" => $data['parse_token'],
                            "device" => $data['device'],
							);
			
			$this->setTable('users');
			if($the_last_id = $this->insert($the_data)){

                $this->db->where('email', $data['email']);
                $query = $this->db->get('posts_emails');

                if($query->num_rows > 0){
                    $query = $query->result_array();

                    foreach ($query as $row) {

                        $data_email = array(
                            'client_id' => $row['client_id'],
                            'user_id' => $the_last_id,
                            'message' => $row['message'],
                            'date' => $row['date']
                            );

                        $insert1 = $this->db->insert('posts_users', $data_email);
                        $insert2 = $this->db->insert('posts_users2', $data_email);

                        if($insert1 && $insert2){
                            $this->db->where('client_id', $row['client_id']);
                            $this->db->where('email', $row['email']);
                            $this->db->where('message', $row['message']);

                            $this->db->delete('posts_emails');
                        }
                    }   
                }

                $the_data2 = array( 
                                "client_id" => 1, 
                                "user_id" => $the_last_id,
                                "message" => "Bienvenido a Bipme, aquí recibirás tus notificaciones..."
                                );
                
                $this->setTable('posts_users');
                $this->insert($the_data2);

                $this->setTable('posts_users2');
                $this->insert($the_data2);
            }
        }

		return $the_last_id;
    }

    function delete_post_user($post_user_id)
    {
    	$this->setTable('posts_users');
    	$where = array("id" => $post_user_id);
    	
        $this->delete($where);
    }

}