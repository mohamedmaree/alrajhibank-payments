<?php
namespace maree\alrajhibankPayments;

class AlrajhiBank {

    public static function checkout($amount = 0.0,$responseURL='',$errorURL=''){
        $token   = csrf_token();
        $trackId = rand(111111111, 999999999);    
        $payment_data = '[{
        "id":"'.config('alrajhiBank.id').'",
        "password": "'.config('alrajhiBank.password').'",

        "amt":"'. $amount.'",
        "action":"1",
        "password":"'.config('alrajhiBank.password').'",
        "currencyCode":"'.config('alrajhiBank.currencyCode').'",
        "trackId": "'.$trackId .'",
        "responseURL":"'.$responseURL.'",
        "errorURL":"'.$errorURL.'",
        "udf1": null,
        "udf2": null,
        "udf3": null,
        "udf4": null,
        "udf5": null,
        "udf6": null,
        "udf7": null,
        "udf8": null,
        "udf9": null,
        "udf10": null,
        "langid":null}]';
        $encrypted = self::encrypt($payment_data,config('alrajhiBank.encryption_key'));

        $data =    '[{
        "id":"'.config('alrajhiBank.id').'",
        "trandata":"'.$encrypted.'",
        "responseURL":"'.$responseURL.'",
        "errorURL":"'.$errorURL.'"
        }]';

        $url = config('alrajhiBank.request_url');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json',"csrf-token:$token"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        $responseData = json_decode($responseData, true);
        if (isset($responseData[0]['result'])) {
            $arr = explode(':',$responseData[0]['result']);
            $payment_id = (isset($arr[0]))? $arr[0]: '';
            return ['key' => 'success' ,'checkoutId' => $payment_id , 'responseData' => $responseData];
        }else{
            return ['key' => 'fail','checkoutId' => '' , 'responseData' => $responseData];
        }
    }

    //trandata = $request->trandata
    public static function checkoutResponseStatus($trandata =''){
        $decrypted = self::decrypt($request->trandata, config('alrajhiBank.encryption_key'));
        $responseData = json_decode($decrypted, true);
        if (isset($responseData[0]['result']) && $responseData[0]['result'] ===  'CAPTURED') {
            return ['key' => 'success' , 'responseData' => $responseData];
        }else{
            return ['key' => 'fail', 'responseData' => $responseData];
        }
    }

    public static function encrypt($str, $key){
        $blocksize = openssl_cipher_iv_length("AES-256-CBC");
        $pad       = $blocksize - (strlen($str) % $blocksize);
        $str       = $str . str_repeat(chr($pad), $pad);
        $encrypted = openssl_encrypt($str, "AES-256-CBC", $key, OPENSSL_ZERO_PADDING, "PGKEYENCDECIVSPC");
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $chars     = array_map("chr", $encrypted);
        $bin       = join($chars);
        $encrypted = bin2hex($bin);
        $encrypted = urlencode($encrypted);
        return $encrypted;
    }

    public static function decrypt($code, $key){
        $string    = hex2bin(trim($code));
        $code      = unpack('C*', $string);
        $chars     = array_map("chr", $code);
        $code      = join($chars);
        $code      = base64_encode($code);
        $decrypted = openssl_decrypt($code, "AES-256-CBC", $key, OPENSSL_ZERO_PADDING, "PGKEYENCDECIVSPC");
        $pad       = ord($decrypted[strlen($decrypted) -1]);
        if ($pad > strlen($decrypted)) {
            return false;
        }
        if (strspn($decrypted, chr($pad), strlen($decrypted) - $pad) != $pad) {
            return false;
        }
        return urldecode(substr($decrypted, 0, -1 * $pad));
    }

}