<?php
    // Security Middleware Bootstrap
    require_once __DIR__ . '/lib/security_bootstrap.php';
    
	$domain="http://".$_SERVER['HTTP_HOST']."";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Loading....</title>
	</head>
	<body>
	<script type="text/javascript">
		window.location="<?php echo $domain;?>/mhs/login";
	</script>
	</body>
</html>
