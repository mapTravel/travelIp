<?php
namespace External;
use External\HttpRequest;

class Geoip {
	private $url = null;
	private $http = null;

	public function __construct($ip)
	{
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false){
			throw new \InvalidArgumentException ('Не коректный url');
		}
		$this->url .= GEOIP_URL.'json/'.$ip;
		$this->http = new HttpRequest($this->url,'json');
	}
	public function getGeoInfo(){
		$data = $this->http->send_url();
		if(empty($r['pb_error_msg']) && $data['header_code'] == 200 && is_array($data['data']) && !empty($data['data'])){
			return $data['data'];
		}else{
			throw new \InvalidArgumentException ('Не удалось получить Geo данные');
		}
	}

}