<?php
namespace App\Helpers;
use Config;
class Helper
{
    public static function customCrypt($vWord){
    	/******
		#IMP_NOTE_:_if_you_need_to_change_below_custom_key_in_future_then_make_sure_its_length_should_be_32_characters_(now_its_32),_otherwise_it_will_stop_working.
    	******/
	    $customKey = "DinarAdpSecretKeyGoodToGoForSucc"; 
	    $newEncrypter = new \Illuminate\Encryption\Encrypter( $customKey, Config::get( 'app.cipher' ) );
	    return $newEncrypter->encrypt( $vWord );
	}

	public static function customDecrypt($vWord){
	    $customKey = "DinarAdpSecretKeyGoodToGoForSucc";
	    $newEncrypter = new \Illuminate\Encryption\Encrypter( $customKey, Config::get( 'app.cipher' ) );
	    return $newEncrypter->decrypt( $vWord );
	}
}