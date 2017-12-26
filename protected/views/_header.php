<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Путешествия - Тестовое задание</title>
    <link rel="stylesheet" href="/theme/bootstrap/css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="/theme/css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="/theme/css/ionicons.min.css" type="text/css" />
    <link rel="stylesheet" href="/theme/css/AdminLTE.css" type="text/css" />
    <link rel="stylesheet" href="/theme/css/skins/_all-skins.min.css" type="text/css" />

	<script>
		/* <![CDATA[ */
		var SERVER = '<?php echo GET_HTTP_HOST().'/ajax';?>';
		/* ]]> */
	</script>
	<script type="text/javascript" src="/theme/js/jQuery-2.1.4.min.js"></script>
    <script type="text/javascript" src="/theme/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/theme/bootstrap/js/bootstrap.js"></script>

	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAUMnENJBND4YoIRk-WQZuBVBpF2HmLE0w"></script>

	<script type="text/javascript" src="/theme/appAng/components/angular.min.js"></script>
	<script type="text/javascript" src="/theme/appAng/components/angular-sanitize.min.js"></script>
    <script type="text/javascript" src="/theme/appAng/components/map/ng-map.min.js"></script>
    <script type="text/javascript" src="/theme/appAng/app.js"></script>


	<script src="/theme/appAng/components/map/directives/marker.js"></script>
	<script src="/theme/appAng/components/map/markerclusterer.js"></script>

	<script>
		MarkerClusterer.prototype.MARKER_CLUSTER_IMAGE_PATH_
			= '/images/marker-clusterer/m';
	</script>


</head>
<body ng-app="AppMapTravel" class="skin-blue sidebar-mini wysihtml5-supported">
<div class="wrapper">
	<header class="main-header">
		<a href="#" class="logo" style="width: 200px">
			<span class="logo-lg"><b>Travel</b>MAP</span>
		</a>
	</header>



