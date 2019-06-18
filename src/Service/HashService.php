<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 14/3/19
 * Time: 5:35 PM
 */

namespace App\Service;





class HashService
{

    private $secret_key ;
    private  $secret_iv;
    private $output ;
    private $encrypt_method;
    private $key ;
    private $iv;


    public function __construct()
    {
         $this->secret_key = 'cdeec51d0783f8e0b9650c81fd95f7df';
         $this->secret_iv = 'd0158c830a6c9cda98eaa30b39657f72';
         $this->output = false;
         $this->encrypt_method = "AES-256-CBC";
         $this->key = hash( 'sha256', $this->secret_key );
         $this->iv = substr( hash( 'sha256', $this->secret_iv ), 0, 16 );
    }

    public function encrypt( string $s)
    {
        $this->output = base64_encode( openssl_encrypt( $s, $this->encrypt_method, $this->key, 0, $this->iv ) );

        return $this->output;

    }

    public function decrypt(string $s)
    {
        $this->output = openssl_decrypt( base64_decode( $s ), $this->encrypt_method, $this->key, 0, $this->iv );
        return $this->output;

    }

    public function generateUUID ( $prefix = 'DIGI', $length=12 ) {
        // Perfect for: UNIQUE ID GENERATION
        // Create a UUID made of: PREFIX:TIMESTAMP:UUID(PART - LENGTH - or FULL)
        $my_random_id = $prefix;
        $my_random_id .= chr ( rand ( 65, 90 ) );
        $my_random_id .= time ();
        $my_uniqid = uniqid ( $prefix );
        if($length > 0) {
            $my_random_id .= substr($my_uniqid, $length);
        } else {
            $my_random_id .= $my_uniqid;
        }
	return $my_random_id;
}


}