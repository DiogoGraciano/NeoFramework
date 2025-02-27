<?php

namespace Core;

class Criptrografy
{
    /**
     * Criptografa uma string usando AES-256-CBC
     *
     * @param string $data A string a ser criptografada
     * @return string A string criptografada
     */
    public static function encrypt($data):string|null
    {
        if($data){
            $first_key = base64_decode($_ENV["FIRSTKEY"]);
            $second_key = base64_decode($_ENV["SECONDKEY"]);    
                
            $method = "aes-256-cbc";    
            $iv_length = openssl_cipher_iv_length($method);
            $iv = openssl_random_pseudo_bytes($iv_length);
                    
            $first_encrypted = openssl_encrypt($data,$method,$first_key, OPENSSL_RAW_DATA ,$iv);    
            $second_encrypted = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);
                        
            $output = base64_encode($iv.$second_encrypted.$first_encrypted);  
            
            $output = str_replace("/","@",$output);

            return $output; 
        }   
        
        return null; 
    }

    /**
     * Descriptografa uma string criptografada usando AES-256-CBC
     *
     * @param string $input A string criptografada
     * @return mixed|bool A string descriptografada ou false se falhar
     */
    public static function decrypt($input):mixed
    {  
        if($input){
            $input = str_replace("@","/",$input);
        
            $first_key = base64_decode($_ENV["FIRSTKEY"]);
            $second_key = base64_decode($_ENV["SECONDKEY"]);            
            $mix =  base64_decode($input);
                    
            $method = "aes-256-cbc";    
            $iv_length = openssl_cipher_iv_length($method);
                        
            $iv = substr($mix,0,$iv_length);
            $second_encrypted = substr($mix,$iv_length,64);
            $first_encrypted = substr($mix,$iv_length+64);
                        
            $data = openssl_decrypt($first_encrypted,$method,$first_key,OPENSSL_RAW_DATA,$iv);
            $second_encrypted_new = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);
                
            if (hash_equals($second_encrypted,$second_encrypted_new))
                return $data;
        }
        
        return null;
    }
}
