<?php
namespace External;

class Geocode {

	private $url = null;
	private $http = null;
	private $key = 'AIzaSyAUMnENJBND4YoIRk-WQZuBVBpF2HmLE0w';

	private $urlParam = array();
	private $tp = null;

	public function __construct($type)
	{
		$this->url .= GEOCODE_URL.$type.'?';
		$this->tp = $type;

		$this->setUrlParam('key',$this->key);
		//$this->http = new HttpRequest($this->url,'json');
	}
	public function get_Info(){
		$urlN = $this->url.http_build_query($this->urlParam);
		$http = new HttpRequest($urlN,$this->tp);

		$data = $http->send_url();
		if(empty($r['pb_error_msg']) && $data['header_code'] == 200 && is_array($data['data']) && !empty($data['data'])){
			if($data['data']['status'] == 'OK'){
				$data['data']['results'][0]['country_inf'] = $this->findCountry($data['data']['results'][0]['address_components']);
				return $data['data']['results'][0];
			}else if($data['data']['status'] == 'ZERO_RESULTS'){
				return $data['data']['results'];
			}
		}else{
			throw new \InvalidArgumentException ('Не удалось получить Код Geo данных');
		}
	}
	private function findCountry($arr){
		$c = array();
		if(is_array($arr) && !empty($arr)){
			foreach($arr as $item){
				if($item['types'][0] == 'country'){
					$c['long_name'] = $item['long_name'];
					$c['short_name'] = strtolower($item['short_name']);
					break;
				}
			}
		}
		return $c;
	}
	public function setUrlParam($key,$value)
	{
		$this->urlParam[$key] = $value;
	}

}