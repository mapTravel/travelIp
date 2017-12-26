<?php
namespace External;

class HttpRequest {
	private $url = '';
	private $emptyRezalt;
	private $postSend;
	private $format = null;

	public function __construct($url,$format,$emptyRezalt = false,$postSend = false){
		$this->url = $url;

		$this->format = $format;
		$this->emptyRezalt = $emptyRezalt;
		$this->postSend = $postSend;

	}
	function send_url(){
		$page = array();
		// создание объекта curl
		if($ch = curl_init()){
			if($this->postSend == true){
				//Отправляем запрос постом
				$post = $this->url['post'];
				$url = $this->url['url'];
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}
			curl_setopt_array( $ch, array( // действие два. установили опции.
				//CURLOPT_FOLLOWLOCATION => true, // следовать перенаправлениям...
				CURLOPT_MAXREDIRS => 10, // ... но не более 10 раз
				CURLOPT_RETURNTRANSFER => true, // смешная третья опция
				CURLOPT_TIMEOUT => 10, // Сколько сек. ждать ответ сервреа (комментарий взят из вашего сообщения, порядок букв сохранен)
				CURLOPT_URL => $this->url // вот этой строки можно избегать инициализируя сразу с адресом. но вам проще иметь ее тут.
			));
			//$page вернет массив содержащий параментры
			$page = $this->curl_exec_follow($ch);
			curl_close($ch);

			if($this->format == 'json'){
				$page['data'] = json_decode($page['rez'],true);
			}
			if($page['error_code'] > 0){
				$page['pb_error_msg'] = 'Ошибка инициализации запроса.Повторите еще раз !!';
				//error_show('Ошибка инициализации запроса.Повторите еще раз !!',false);
			}else if($page['header_code'] == 200 && (!empty($page['rez']) || $this->emptyRezalt == true) ){

			}else{
				$page['pb_error_msg'] = 'Ошибка запроса.Повторите еще раз';
				//return error_show('Ошибка запроса.Повторите еще раз',false);
			}
		}else{
			$page['pb_error_msg'] = 'Ошибка инициализации запроса';
			//return error_show('Ошибка инициализации запроса',false);
		}
		return $page;
	}
	private function curl_exec_follow($ch,$maxredirect = 5) {
		$rez = array();
		if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$rez['rez'] = curl_exec($ch);
			if (curl_errno($ch)) {
				$rez['error_code'] = curl_errno($ch);
				$rez['error_msg'] = curl_error($ch);
			} else {
				$infiArr = curl_getinfo($ch);
				$rez['header_code'] = $infiArr['http_code'];
			}
		} else {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			$newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

			$rch = curl_copy_handle($ch);
			curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
			do {
				curl_setopt($rch, CURLOPT_URL, $newurl);
				$rez['rez'] = curl_exec($rch);
				if (curl_errno($rch)) {
					$code = 0;
					$rez['error_code'] = curl_errno($rch);
					$rez['error_msg'] = curl_error($rch);
				} else {
					$infiArr = curl_getinfo($rch);
					$code = $infiArr['http_code'];
					$rez['header_code'] = $code;
					if ($code == 301 || $code == 302) {
						$redir = $infiArr['redirect_url'];
						//preg_match('/Location:(.*?)\n/', $rez, $matches);
						$newurl = trim($redir);
					} else {
						$code = 0;
					}
				}
			} while ($code && $maxredirect--);
			curl_close($rch);
		}
		return $rez;
	}
}