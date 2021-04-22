<?php
namespace App\Helpers;
use Config;
use Modules\CommonSetting\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;

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

	public static function emailDynamicCodesReplace($code_values){

		$template_data = EmailTemplate::where(["template_type_id"=>$code_values["template_type_id"],'status'=>'1'])->with("templateType")->first();

		if(!empty($template_data)){
			$template_data = $template_data->toArray();
			$content_data = $template_data["content"];

			if($template_data["template_type"]["dynamic_code"] != '' || $template_data["template_type"]["dynamic_code"] != Null){
	            $dynamic_code = $template_data["template_type"]["dynamic_code"];
	            $dynamic_code = explode(',',$dynamic_code);
	            $new_array = array_combine($dynamic_code,$code_values["dynamic_code_value"]);
	            foreach($new_array as $key => $value){
	            	$word = "[".trim($key)."]";
	                $content = str_replace($word,trim($value),$content_data);
	                $content_data = $content;
	            }
	        }

	        $data["content"] =  str_replace('\n','',$content_data);
	        $data["content"] =  str_replace('\"','"',$data["content"]);
	        if(substr($data["content"],0,1) == '"'){
	            $data["content"] =  substr($data["content"],1,strlen($data["content"]) - 2);
	        }
	        $data["subject"] = $template_data["subject"];
	        $data["email_to"] = $code_values["email_to"];

	        $image_url = [
	            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
	            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
	            'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
	            'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
				'banner_img_url' => env('APP_URL')."/img/emailBanner.jpg",
	        ];


	        // Mail::send('emails.CommonMailTemplate', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
	        //     $m->from('noreply@meritincentives.com','Takreem');
	        //     $m->to($data["email_to"])->subject($data['subject']);
	        // });

	        return $data;
		}
		return true;
		
	}

	public static function ValidateGetRequestParameters(){
		
		$request = Input::all();
		if(!empty($request))
		{
			foreach($request as $index => $single)
			{
				$value = trim(preg_replace("/'/", "", $single));
				Input::offsetSet($index, $value);
				$_REQUEST[$index] 	= $value;				
				$_GET[$index] 		= $value;				
			}
		}	
	}
	
}