<?php include 'login_auth.php'; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Dashboard | POLITEKNIK INDONUSA Surakarta</title>
		<script type="text/javascript" src="../lib/jquery-1.7.1.min.js"></script>
  		<script type="text/javascript" src="../lib/backbone/underscore.js"></script>
  		<script type="text/javascript" src="../lib/backbone/backbone.js"></script>
		<link rel="stylesheet" type="text/css" href="../lib/webix.css">
		<link rel="stylesheet" type="text/css" href="../lib/wsia.css">
		<link rel="stylesheet" href="../lib/skins/compact.css" type="text/css" media="screen" charset="utf-8">
		<script src="../lib/webix.js?v=7.0.1" type="text/javascript" charset="utf-8"></script>
		<script src="../lib/webix_view.js" type="text/javascript" charset="utf-8"></script>
		<style type="text/css">
			.rotate{
			  animation: rotate 1.5s linear infinite; 
			}
			@keyframes rotate{
			  to{ transform: rotate(360deg); }
			}

			.spinner{
			  display:inline-block; width: 15px; height: 15px;
			  border-radius: 50%;
			  box-shadow: inset -2px 0 0 2px #0bf;
			}
		</style>
	</head>
	<body class="app_wsia" bgcolor="#244531">
	</body>
	<script src="js/proxy.js"></script>
	<script src="js/wsia_routes.js?<?php echo rand();?>"></script>
	<script src="js/wsia.js?<?php echo rand();?>"></script>
	<script src="js/wsia_actions.js?<?php echo rand();?>"></script>
	<div id="bobot"></div>
</html>