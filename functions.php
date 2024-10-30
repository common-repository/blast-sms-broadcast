<?php
  namespace BlastSmsBroadcast;

  class BlastSmsBroadcast_Helper{  
    
    private static $instance;

    static function blastmy_helper_get_instance()  
    {  
      if(!isset(self::$instance)) {  
          self::$instance = new self();  
      }  
      
      return self::$instance;  
    }                    
    
    static function blastmy_helper_validate_array($phones)
    {      
      if(is_array($phones)) {
        $multiple_phones = '';

        foreach($phones as $phone) {
          $phone = self::blastmy_helper_validate_phone( $phone);
          $multiple_phones .= $phone.',';
        }

        return $multiple_phones;
      }

      $single_phone = self::blastmy_helper_validate_phone( $phones);

      return $single_phone;
    }

    static function blastmy_helper_validate_phone($phone)
    { 
      $phone = preg_replace("/[^0-9]/", "", $phone);
      $phone = preg_replace('~^(?:0?1|601)~','+601', $phone);

      return $phone;
    }

    static function blastmy_helper_get_balance()
    {
      $method = 'GET';
      $path = '/balance';

      $response = self::blastmy_helper_get_response($method, $path);

      if(isset($response['balance'])) {
        return $response['balance'];
      }

      return false;
    }

    static function blastmy_helper_send_sms()
    {
      $method = 'POST';
      $path = '/sms';
      $phones = $_POST['blast_messages']['phone'];

      if( is_array($phones) ) {
        foreach($phones as $key => $phone) {
          $phones[$key] = sanitize_text_field( $phone );
        }
      } else {
        $phones = sanitize_text_field( $phones );
      }

      $body = array(
        'phones' => self::blastmy_helper_validate_array( $phones ),
        'message' => sanitize_textarea_field( $_POST['blast_messages']['message'] ?? NULL ),
        'delay' => sanitize_text_field( $_POST['blast_messages']['delay'] ?? NULL ),
        'webhook_url' => sanitize_text_field( $_POST['blast_messages']['webhook_url'] ?? NULL ),
        );

      return self::blastmy_helper_get_response($method, $path, $body);
    }

    static function blastmy_helper_get_sms($smsId)
    {
      $method = 'GET';
      $path = '/sms/'.$smsId;

      return self::blastmy_helper_get_response($method, $path);
    }

    static function blastmy_helper_get_response($method, $path, $body = null)
    {
      $base_url = 'https://blast.my/api/v1';
      $api_token = get_option('blast_options')['token'];

      if($method == 'POST') {
        $args = array(
          'body'        => json_encode($body),
          'timeout'     => '5',
          'redirection' => '5',
          'httpversion' => '1.0',
          'blocking'    => true,
          'headers'     => array(
            'Authorization' => 'Bearer ' . $api_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ),
        );

        $response = wp_remote_post( $base_url . $path, $args );
      }

      if($method == 'GET') {
        $args = array(
          'headers' => array(
            'Authorization' => 'Bearer ' . $api_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          )
        );  

        $response = wp_remote_get( $base_url . $path, $args );
      }

      return json_decode( wp_remote_retrieve_body($response), true);
    }
  }

  if ( isset($_POST['blast_messages']) && isset($_POST['submit']) && sanitize_text_field( $_POST['submit'] ) == 'Broadcast Now' ) 
  {
    if ($pagenow == 'admin.php' && sanitize_text_field( $_GET['page'] ) == 'blast-sms-broadcast') 
    {
      $response = BlastSmsBroadcast_Helper::blastmy_helper_send_sms();

      if (isset($response) && isset($response['message'])) 
      {
          $status = 'error';
          $message = $response['message'];

          if (isset($response['errors'])) 
          {
              foreach ($response['errors'] as $key => $value) 
              {
                  $message .= $key . ' : ' . $value[0];
              }
          }
      } 
      else 
      {
          $status = 'success';
          $message = 'Your message has been successfully broadcasted!';
      }

      add_action('admin_notices', function () use ($status, $message) 
      {
          $class = 'notice notice-' . $status;

          wp_kses_post( printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message)) );
      });
    }
  }

  $blast_options = get_option('blast_options');

  if (isset($blast_options['token']) && $blast_options['token'] != '') 
  {
    if ( !BlastSmsBroadcast_Helper::blastmy_helper_get_balance() ) 
    {
      if ($pagenow == 'admin.php' && sanitize_text_field( $_GET['page'] ) == 'blast-sms-broadcast') 
      {
        add_action('admin_notices', function () 
        {
          $class = 'notice notice-error';
          $message = 'API token invalid. Please review your token from Blast.my.';

          wp_kses_data( printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message)) );
        });
      }
    }
  }
?>