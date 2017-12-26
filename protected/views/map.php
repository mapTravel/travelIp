<?php require __DIR__ . '/_header.php' ?>
<?php require __DIR__ . '/_sidebar.php' ?>
<style>
	ng-map {width:100%; height:100%;}
	.username:hover, .iconMap:hover {
		cursor: pointer;
	}
	.user-block .list-group img {
		width: 16px;
		height: 11px;
		margin-right: 10px;
		margin-top: 5px;
	}
	.hoverNo:hover {
		cursor: context-menu;
	}
</style>
	<div class="content-wrapper" style="min-height: 1014px;">
		<section class="content-header">
			<h1>
				Карта
				<small>путешествий</small>
			</h1>
		</section>

		<section class="content" ng-controller="MapCtrl">
			<div class="col-md-7">
				<div class="box box-primary">
					<div class="box-body" style="height: 500px">
						<ng-map default-style="false" center="[{{MapLat}}, {{MapLng}}]" zoom="2">

						</ng-map>
					</div>
				</div>
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Популярное</h3>
					</div>
					<div class="box-body">
						<div class="col-md-10" ng-if="places_countries_count">
							<div class="info-box">
								<span class="info-box-icon bg-aqua"><i class="ion ion-ios-people-outline"></i></span>
								<div class="info-box-content">
									<span class="info-box-text">В наибольшем количестве стран побывал</span>
									<span class="info-box-number">{{places_users_count}}</span>
								</div><!-- /.info-box-content -->
							</div><!-- /.info-box -->
						</div>
						<div class="col-md-10" ng-if="places_countries_count">
							<div class="info-box">
								<span class="info-box-icon bg-red"><i class="fa fa-fw fa-map-pin"></i></span>
								<div class="info-box-content">
									<span class="info-box-text">Чаще всего бывали в этих странах</span>
									<span class="info-box-number">{{places_countries_count}}</span>
								</div><!-- /.info-box-content -->
							</div><!-- /.info-box -->
						</div>
						<div class="col-md-10" ng-if="users2">
							<div class="info-box">
								<span class="info-box-icon bg-yellow"><i class="fa fa-fw fa-user"></i></span>
								<div class="info-box-content">
									<span class="info-box-text">Самое распространенное имя путешественика</span>
									<span class="info-box-number">{{users2}}</span>
								</div><!-- /.info-box-content -->
							</div><!-- /.info-box -->
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Пользователи</h3>
					</div>
					<div class="box-body" style="max-height: 857px;overflow-x: hidden;">
						<div class="user-block"ng-init="user.showList = false" ng-repeat="user in users" style="margin-bottom: 20px">
							<img class="img-circle img-bordered-sm" src="images/avatar04.png" alt="user image">
							<span class="username">
							  	<a class="hoverNo">{{user.name}}</a>
								<a class="pull-right btn-box-tool" ng-click="showListPlace(user)"><i class="fa fa-fw fa-map-marker"></i></a>
							</span>
							<span class="description">Активен: <strong>{{user.is_active}}</strong></span>
							<span class="description">Любимые страны: <strong>{{user.favorite_countri}}</strong></span>
							<ul ng-if="user.showList" class="list-group list-group-unbordered" style="margin-left: 50px;margin-top: 15px">
								<li ng-repeat="pl in user.placeUser" ng-click="showInMapPlace(pl)" class="list-group-item iconMap">
									<img src="/images/flag/{{pl.short_name}}_16.png"/> {{pl.c_name}} <a class="pull-right"><i class="fa fa-fw fa-map"></i></a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
<?php require __DIR__ . '/_footer.php' ?>