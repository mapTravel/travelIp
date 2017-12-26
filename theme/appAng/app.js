angular.module('AppMapTravel', ['ngSanitize','ngMap'])
	.config(function ($httpProvider) {
		// Используем x-www-form-urlencoded Content-Type
		$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

		// Переопределяем дефолтный transformRequest в $http-сервисе
		$httpProvider.defaults.transformRequest = [function (data) {
			/**
			 * рабочая лошадка; преобразует объект в x-www-form-urlencoded строку.
			 * @param {Object} obj
			 * @return {String}
			 */
			var param = function (obj) {
				var query = '';
				var name, value, fullSubName, subValue, innerObj, i;

				for (name in obj) {
					value = obj[name];

					if (value instanceof Array) {
						for (i = 0; i < value.length; ++i) {
							subValue = value[i];
							fullSubName = name + '[' + i + ']';
							innerObj = {};
							innerObj[fullSubName] = subValue;
							query += param(innerObj) + '&';
						}
					}
					else if (value instanceof Object) {
						for (subName in value) {
							subValue = value[subName];
							fullSubName = name + '[' + subName + ']';
							innerObj = {};
							innerObj[fullSubName] = subValue;
							query += param(innerObj) + '&';
						}
					}
					else if (value !== undefined && value !== null) {
						query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
					}
				}

				return query.length ? query.substr(0, query.length - 1) : query;
			};

			return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
		}];
	})
	.controller('MapCtrl', function ($scope, $http,NgMap) {
		$scope.users = [];
		$scope.places = [];

		$scope.MapLat = 50.458999;
		$scope.MapLng = 30.540905;

		$scope.dynMarkers = [];

		NgMap.getMap().then(function(map) {
			$scope.map = map;

			var send_data = {};

			$http.post(SERVER+'/get_all_places', send_data).then(function (response) {
				if (response.status == 200) {
					if (response.data.status != 'error') {
						if(response.data.places.length > 0){
							$scope.places = response.data.places;

							console.log('map.markers = ',map['markers']);


							for (var i = 0; i < $scope.places.length; i++) {
								var store = $scope.places[i];

								var contentString = '<div id="content" style="width: 300px">'+
									'<div id="siteNotice">'+
									'</div>'+
									'<h1 id="firstHeading" class="firstHeading">'+store.user_name+'</h1>'+
									'<div id="bodyContent">'+
									'<p><b>Страна: </b><img src="/images/flag/'+store.short_name+'_16.png"/> '+store.c_name +'</p>'+
									'<p><b>Рейтинг: </b>'+store.rating +'</p>'+
									'<p><b>Дата: </b>'+store.registration_date +'</p>'+
									'<p><b>Адрес: </b>'+store.formatted_address +'</p>'+
									'</div>'+
									'</div>';

								var locationInfowindow = new google.maps.InfoWindow({
									content: contentString
								});


								store.position = new google.maps.LatLng(store.lat,store.lng);
								store.title = 'name '+ i;
								store.infowindow = locationInfowindow;

								var marker = new google.maps.Marker(store);
								$scope.dynMarkers.push(marker);

								google.maps.event.addListener(marker, 'click', function() {
									this.infowindow.open(map, this);
								});
							}
							console.log('finished loading scripts/starbucks.json', 'vm.stores', $scope.dynMarkers.length);
							$scope.markerClusterer = new MarkerClusterer(map, $scope.dynMarkers, {});

						}
					} else {
						alert(response.data.msg);
					}
				}
			});

		});
		$scope.showInMap = function(user){
			var userId = user.id;

			console.log('userId = ',userId);
			angular.forEach($scope.places, function(value, key) {
				if(value.id == userId){
					$scope.map.setZoom(8);
					$scope.map.setCenter(new google.maps.LatLng(value.lat, value.lng));
				}

			});
		};
		$scope.showInMapPlace = function(place){
			$scope.map.setZoom(8);
			$scope.map.setCenter(new google.maps.LatLng(place.lat, place.lng));
		};
		$scope.showListPlace = function(user){

			if(user.placeUser && user.showList == false){
				user.showList = true;
			}else if(user.placeUser && user.showList == true){
				user.showList = false;
			}else{
				console.log('Start');
				var placeUser = [];
				angular.forEach($scope.places, function(value, key) {
					if(value.id == user.id){
						placeUser.push(value);
					}

				});
				if(placeUser.length > 0){
					user.showList = true;
					user.placeUser = placeUser;
				}
			}


		};
		function getAllUsers() {
			var send_data = {};

			$http.post(SERVER+'/get_all_users', send_data).then(function (response) {
				if (response.status == 200) {
					if (response.data.status != 'error') {
						if(response.data.users.length > 0){
							$scope.users = response.data.users;
						}
					} else {
						alert(response.data.msg);
					}
				}
			});
		}

		function getPopularInfo(){
			var send_data = {};


			$http.post(SERVER+'/popular', send_data).then(function (response) {
				if (response.status == 200) {
					if (response.data.status != 'error') {

						if(response.data.places_countries_count.length > 0){
							$scope.places_countries_count = response.data.places_countries_count.join(', ');
						}
						if(response.data.places_users_count.length > 0){
							$scope.places_users_count = response.data.places_users_count.join(', ');
						}
						if(response.data.users.length > 0){
							$scope.users2 = response.data.users.join(', ');
						}
					} else {
						alert(response.data.msg);
					}
				}
			});
		}
		getAllUsers();
		getPopularInfo();
	}).controller('UserCsvCtrl', function ($scope, $http) {

		$scope.status = '';

		$scope.importBtn = false;
		$scope.import = function(name){
			var send_data = {
				"nameF": name
			};

			$scope.importBtn = true;

			$http.post(SERVER+'/import_file', send_data).then(function (response) {
				if (response.status == 200) {
					if (response.data.status != 'error') {
						$scope.status = 'label-success';
						$scope.statusMsg = 'Ok';
					} else {
						$scope.importBtn = false;
						alert(response.data.msg);
					}
				}else{
					$scope.importBtn = false;
				}
			});
		}
	})
	;


angular.module('AppMapTravel').directive('convertToNumber', function() {
	return {
		require: 'ngModel',
		link: function (scope, element, attrs, ngModel) {
			ngModel.$parsers.push(function(val) {
				//console.log('ngModel.$parsers push = ',val);
				//console.log('ngModel.$parsers parseInt = ',parseInt(val, 10));
				return parseInt(val, 10);
			});


			ngModel.$formatters.push(function (val) {
				//console.log('ngModel.$formatters.push = ',val);
				return '' + val;
			});
		}
	};
});

/*angular.element(document).ready(function () {
	var user_percent_discount = $('.section-user_percent_discount');
	angular.bootstrap(user_percent_discount, ['AppUserPercentDiscount']);
	user_percent_discount.show();
});*/
