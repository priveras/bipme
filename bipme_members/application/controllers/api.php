<?php
require(APPPATH.'/libraries/REST_Controller.php');
 
class Api extends REST_Controller
{

    public function __construct(){

        parent::__construct();
        $this->load->model('model_feed');
        $this->load->model('model_add');
    }

    function user_get()
    {
        if(!$this->get('id'))
        {
            $this->response(NULL, 400);
        }

        $this->load->model('model_feed');
        $user = $this->model_feed->get_users( $this->get('id') );
         
        if($user)
        {
            $this->response($user, 200); // 200 being the HTTP response code
        }
 
        else
        {
            $this->response(NULL, 404);
        }
    }
     
    function user_post()
    {

        $message = $this->post('message');
        $username = $this->post('username_api');
        $client_id = $this->post('client_id');
        $client_password = $this->post('client_key');

        if($this->model_add->check_client_key($client_id, $client_password)){

            $this->load->helper('email');

            if (valid_email($username)){

                if($data = $this->model_add->check_email($username)){

                    $username = $data['username'];

                    if($this->insert_notification($message, $username, $client_id)){
                        $result = true;
                        $status = 'Valid email. Email exists. Notification sent.';
                    }else{
                        $result = true;
                        $status = 'Valid email. Email exists. Notification was not sent.';
                    }
                }else{
                    if($this->model_add->send_email_api($message, $username, $client_id)){
                        $result = true;
                        $status = 'Valid email. Email does not exist. Mail sent. ';
                    }else{
                        $result = true;
                        $status = 'Valid email. Email does not exist. Mail was not sent. ';
                    }
                }
            }else{

                if($this->insert_notification($message, $username, $client_id)){
                    $result = true;
                    $status = 'Not valid email. Notification sent. ';
                }else{
                    $result = true;
                    $status = 'Not valid email. Notification was not sent. ';
                }
            }
        }else{
            $result = true;
            $status = 'You have no authorization';
        }
         
        if($result === FALSE)
        {
            $this->response(array('status' => $status));
        }
         
        else
        {
            $this->response(array('status' => $status));
        }
         
    }
     
    function users_get()
    {
        $users = $this->user_model->get_all();
         
        if($users)
        {
            $this->response($users, 200);
        }
 
        else
        {
            $this->response(NULL, 404);
        }
    }

    public function insert_notification($message, $username, $client_id){

        if($data = $this->model_add->get_user_info_api($username)){

            if($this->model_add->insert_notification_api($data, $message, $client_id)){

                if($this->model_add->send_push_api($data, $message, $client_id)){
                    return true;

                }else{
                    return true;
                }
            }else{
                return true;
                $status = "The notificiation was not inserted";
            }
        }else{
            return false;
            $status = "it was false";
        }
    }
}
?>