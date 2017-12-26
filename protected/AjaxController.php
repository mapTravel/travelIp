<?php
use DB\Db;
use External\Geoip;
use External\Geocode;
class AjaxController
{
	private $data = array();
	private $errors = array();

	public function __construct(){}

	/**
	 *
	 */
	public function get_all_usersAction(){

		$sql = "SELECT
					u.id,u.name,u.is_active,
					GROUP_CONCAT(c.name SEPARATOR ', ') AS favorite_countri
				FROM users AS u
					LEFT JOIN favorite_country AS fc ON fc.users_id = u.id
					LEFT JOIN countries AS c ON c.id = fc.countries_id
				GROUP BY u.id;";
		$all = Db::run($sql)->fetchAll();
		if(is_array($all) && !empty($all)){
			$this->data['users'] = $all;
		}
		$err = Db::getErrorInfo();
		if($err[0] === false){
			$this->errors[] = $err[2];
		}

		$this->json();
	}
	public function get_all_placesAction(){
		$sql = "SELECT
					u.id,u.name AS user_name,
					pl.formatted_address,
					pl.lat,
					pl.lng,
					pl.registration_date,
					pl.rating,
					c.name AS c_name,
					c.short_name
				FROM users AS u
				LEFT JOIN places AS pl ON pl.users_id = u.id
				LEFT JOIN countries AS c ON c.id = pl.countries_id";
		$all = Db::run($sql)->fetchAll();
		if(is_array($all) && !empty($all)){
			$this->data['places'] = $all;
		}
		$err = Db::getErrorInfo();
		if($err[0] === false){
			$this->errors[] = $err[2];
		}

		$this->json();
	}
	public function popularAction(){
		$sql = "SELECT u.name
				FROM places_users_count AS puc
					LEFT JOIN users AS u ON u.id = puc.users_id
				WHERE puc.count = (SELECT MAX(count) AS max FROM places_users_count)";
		$places_users_count = Db::run($sql)->fetchAll(PDO::FETCH_COLUMN);

		$sql2 = "SELECT name AS c_name FROM places_countries_count AS pcc
					LEFT JOIN countries AS c  ON c.id = pcc.countries_id
				WHERE pcc.count = (SELECT MAX(count) AS max FROM places_countries_count)";
		$places_countries_count = Db::run($sql2)->fetchAll(PDO::FETCH_COLUMN);

		$sql3 = "SELECT name FROM users GROUP BY 1 HAVING COUNT(name) > 1";
		$users = Db::run($sql3)->fetchAll(PDO::FETCH_COLUMN);

		$this->data['places_users_count'] = $places_users_count;
		$this->data['places_countries_count'] = $places_countries_count;
		$this->data['users'] = $users;

		$this->json();
	}
	public function import_fileAction(){
		$file = $_POST['nameF'];
		$path = CSV.DIRECTORY_SEPARATOR.$file;

		$info = new SplFileInfo($path);
		if($info->isFile() && $info->getExtension() == 'csv'){
			$csv = file_get_contents($path);
			if($csv === false){
				$this->errors[] = 'Ошибка не удалось произвести импорт -- '.$file;
			}
			if(empty($this->errors)){
				$Data = str_getcsv($csv, "\n");
				$fruitHeader = $this->explodArr(array_shift($Data));

				//Получаем удаленные данные
				$newData = $this->external_data($Data);

				if(
					in_array('name',$fruitHeader) &&
					in_array('registration_date',$fruitHeader) &&
					in_array('ip',$fruitHeader) &&
					in_array('rating',$fruitHeader) &&
					in_array('country',$fruitHeader) &&
					in_array('is_active',$fruitHeader) &&
					!empty($newData)
				){
					$db = Db::instance();
					try {
						$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$db->setAttribute(PDO::ATTR_AUTOCOMMIT,0);

						$db->beginTransaction();

						//Добавление всех пользователей
						$stmt_InsertNewUser = $db->prepare('INSERT INTO `users` (name, is_active) VALUES (:name,:is_active)');
						foreach($newData as $k => $user){
							$stmt_InsertNewUser->execute(array(':name' => $k, ':is_active' => $user['is_active']));
							$insertId_User = $db->lastInsertId();

							$newData[$k]['userId'] = $insertId_User;
						}
						//добавление всех стран
						$stmt_InsertNewCountries = $db->prepare('INSERT IGNORE INTO countries (name, short_name) VALUES (:name,:short_name)');
						$stmt_SelectCountriBySn = $db->prepare('SELECT id FROM countries WHERE short_name = :short_name');
						foreach($newData as $k => $user){
							foreach($user['favorite_country'] as $c => $n){
								$stmt_InsertNewCountries->execute(array(':name' => $n, ':short_name' => $c));
								$insertId_Countries = $db->lastInsertId();

								if(!$stmt_InsertNewCountries->rowCount() > 0){
									$stmt_SelectCountriBySn->execute(array(':short_name' => $c));
									$insertId_Countries = $stmt_SelectCountriBySn->fetchColumn();
								}
								$newData[$k]['favorite_country'][$c] = $insertId_Countries;
							}
							//Из развела places
							foreach($user['places'] as $k2 => $p){
								foreach($p['countrys'] as $c => $n){
									$stmt_InsertNewCountries->execute(array(':name' => $n, ':short_name' => $c));
									$insertId_Countries = $db->lastInsertId();

									if(!$stmt_InsertNewCountries->rowCount() > 0){
										$stmt_SelectCountriBySn->execute(array(':short_name' => $c));
										$insertId_Countries = $stmt_SelectCountriBySn->fetchColumn();
									}

									$newData[$k]['places'][$k2]['countrys'][$c] = $insertId_Countries;
								}
							}
						}
						//Добавим все favorite_country
						$stmt_InsertNewFavoriteCountry = $db->prepare('INSERT INTO favorite_country (users_id, countries_id) VALUES (:users_id,:countries_id)');
						foreach($newData as $k => $user){
							$userId = $user['userId'];
							if($userId > 0){
								foreach($user['favorite_country'] as $c => $id){
									$stmt_InsertNewFavoriteCountry->execute(array(':users_id' => $userId, ':countries_id' => $id));
								}
							}
						}
						//Добавим все places
						$stmt_InsertNewPlaces = $db->prepare('INSERT INTO places (users_id, countries_id, registration_date, ip, lat, lng, formatted_address, rating)  VALUES (:users_id, :countries_id, :registration_date, :ip, :lat, :lng, :formatted_address, :rating)');

						$stmt_InserPlacesUsersCount = $db->prepare('INSERT INTO places_users_count (users_id) VALUES (:users_id)');
						$stmt_UpdatePlacesUsersCount = $db->prepare('UPDATE places_users_count SET count = count + 1 WHERE users_id = :users_id');
						$stmt_InsertPlacesCountriesCount = $db->prepare('INSERT INTO places_countries_count (countries_id)  VALUES (:countries_id)');
						$stmt_UpdatePlacesCountriesCount = $db->prepare('UPDATE places_countries_count SET count = count + 1 WHERE countries_id = :countries_id');

						foreach($newData as $k => $user){
							$users_id = $user['userId'];
							foreach($user['places'] as $k2 => $p){
								$countries_id = current($p['countrys']);
								$stmt_InsertNewPlaces->execute(array(
									':users_id' => $users_id,
									':countries_id' => $countries_id,
									':registration_date' => $p['registration_date'],
									':ip' => ip2long($p['ip']),
									':lat' => $p['lat'],
									':lng' => $p['lng'],
									':formatted_address' => $p['formatted_address'],
									':rating' => $p['rating']
								));
								if($stmt_InsertNewPlaces->rowCount() > 0){
									$arr = array(':users_id' => $users_id);
									$stmt_UpdatePlacesUsersCount->execute($arr);
									if(!$stmt_UpdatePlacesUsersCount->rowCount() > 0){
										$stmt_InserPlacesUsersCount->execute($arr);
									}
									$arr2 = array(':countries_id' => $countries_id);
									$stmt_UpdatePlacesCountriesCount->execute($arr2);
									if(!$stmt_UpdatePlacesCountriesCount->rowCount() > 0){
										$stmt_InsertPlacesCountriesCount->execute($arr2);
									}

								}
							}
						}

						$db->commit();

					} catch (PDOException $e) {
						$db->rollBack();
						$this->errors[] = "Ошибка сохранения : " . $e->getMessage();
					}finally{
						$db->setAttribute(PDO::ATTR_AUTOCOMMIT,1);
					}
				}else{
					$this->errors[] = 'Неверный формат файла';
				}
			}
		}else{
			$this->errors[] = 'Ошибка импорта';
		}
		$this->json();
	}
	private function external_data($Data){
		$new = array();

		$GeocodeLatLng = new Geocode('json');
		$GeocodeLatLng->setUrlParam('language','ru');
		$GeocodeLatLng->setUrlParam('result_type','street_address');

		$places_i = 0;
		foreach($Data as $row){
			$dt = $this->explodArr($row);

			if(is_array($dt) && !empty($dt)){
				if(!array_key_exists($dt[0],$new)){
					$new[$dt[0]] = array();
				}
				if(!array_key_exists('favorite_country',$new[$dt[0]])){
					$new[$dt[0]]['favorite_country'] = array();
				}

				if(!in_array($dt[4],$new[$dt[0]]['favorite_country'])){
					$new[$dt[0]]['favorite_country'][] = $dt[4];
				}
				if(!array_key_exists('lastData',$new[$dt[0]])){
					$new[$dt[0]]['lastData'] = '0000-00-00';
				}


				$t1 = strtotime($dt[1]);
				$t2 = strtotime($new[$dt[0]]['lastData']);
				if( ($t1 > $t2) || $t2 === false){
					$new[$dt[0]]['is_active'] = $dt[5];
					$new[$dt[0]]['lastData'] = $dt[1];
				}
				$geoip = new Geoip($dt[2]);
				$geo = $geoip->getGeoInfo();

				$GeocodeLatLng->setUrlParam('latlng',$geo['latitude'].','.$geo['longitude']);
				$geoCode = $GeocodeLatLng->get_Info();

				$formatted_address = (!empty($geoCode)) ? $geoCode['formatted_address'] : '';

				$new[$dt[0]]['places'][$places_i] = array(
					'registration_date' => $dt[1],
					'ip' => $dt[2],
					'lat' => $geo['latitude'],
					'lng' => $geo['longitude'],
					'formatted_address' => $formatted_address,
					'rating' => $dt[3]
				);

				if(!array_key_exists('countrys',$new[$dt[0]]['places'][$places_i])){
					$new[$dt[0]]['places'][$places_i]['countrys'] = array();
				}
				if(is_array($geoCode['country_inf']) && !empty($geoCode['country_inf'])){
					$new[$dt[0]]['places'][$places_i]['countrys'][$geoCode['country_inf']['short_name']] = $geoCode['country_inf']['long_name'];
				}else{
					$new[$dt[0]]['places'][$places_i]['countrys'][strtolower($geo['country_code'])] = $geo['country_name'];
				}


				$places_i++;

			}
		}
		$GeocodeCountry = new Geocode('json');
		$GeocodeCountry->setUrlParam('language','ru');
		$GeocodeCountry->setUrlParam('result_type','country');

		foreach($new as $k => $v){
			$favorite_country = array_unique($v['favorite_country']);
			$newF = array();
			foreach($favorite_country as $f){
				$GeocodeCountry->setUrlParam('address',$f);
				$geoCode = $GeocodeCountry->get_Info();
				if(is_array($geoCode['country_inf']) && !empty($geoCode['country_inf'])){
					$newF[$geoCode['country_inf']['short_name']] = $geoCode['country_inf']['long_name'];
				}
			}
			if(!empty($newF)){
				$new[$k]['favorite_country'] = $newF;
			}
		}
		return $new;
	}
	private function explodArr($arr){
		return explode(',',str_replace(array("'","\""), "", $arr));
	}
	private function json(){
		$this->data['status'] = 'sucsess';
		if(!empty($this->errors)){
			$this->data['status'] = 'error';
			$this->data['err'] = $this->errors;
			$this->data['msg'] = implode(',',$this->errors);
		}
		echo json_encode($this->data);
	}
}