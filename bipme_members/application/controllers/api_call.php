<?php 

   function native_curl(){

        //Your client_id
        $client_id = 1;

        //Your client_key
        $client_key = '38371fef7d829c7f8b0e2fedf7a04334';

        //The message you want to send
        $message = "This is a message sent from the API";

        //The email or username you are sending it to
        $username_api = "priveras@gmail.com";

        //Bipme's API URL
        $url = 'http://localhost:8888/codeigniter/bipme/bipme_members/api/user/format/json';

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, array(
            'client_id' => $client_id,
            'message' => $message,
            'username_api' => $username_api,
            'client_key' => $client_key
        ));
         
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
         
        $result = json_decode($buffer);
     
        if(isset($result->status)){
            
            //Success message
            echo $result->status;
            echo 'Result was true';
        }else{
            
            //Error message
            echo 'Something has gone wrong';
        }
    }
?>