<?php include 'login_auth.php'; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Dosen | POLITEKNIK INDONUSA Surakarta</title>
		<script type="text/javascript" src="../lib/jquery-1.7.1.min.js"></script>
  		<script type="text/javascript" src="../lib/backbone/underscore.js"></script>
  		<script type="text/javascript" src="../lib/backbone/backbone.js"></script>
		<link rel="stylesheet" type="text/css" href="../lib/webix.css">
		<link rel="stylesheet" type="text/css" href="../lib/wsia.css">
		<link rel="stylesheet" href="../lib/skins/compact.css" type="text/css" media="screen" charset="utf-8">
		<script src="../lib/webix.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" src="../lib/components/sidebar/sidebar.js"></script>
		<link rel="stylesheet" type="text/css" href="../lib/components/sidebar/sidebar.css">
		
		<script src="https://accounts.google.com/gsi/client" async defer></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<style>
			@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
	
			body, .webix_view {
				font-family: 'Inter', sans-serif !important;
			}
	
			/* Premium Layout */
			.card_premium {
				background: #ffffff !important;
				border-radius: 12px !important;
				box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important;
				border: 1px solid #edf2f7 !important;
			}
	
			.toolbar_premium {
				background: #ffffff !important;
				border-bottom: 1px solid #edf2f7 !important;
			}
	
			.header_title {
				color: #1a202c !important;
				font-weight: 700 !important;
				font-size: 18px !important;
				letter-spacing: -0.5px;
			}
	
			.card_title {
				font-weight: 700 !important;
				font-size: 16px !important;
				color: #2d3748 !important;
				margin-bottom: 10px;
			}
	
			.card_desc {
				color: #718096 !important;
				font-size: 13px !important;
				line-height: 1.5;
			}
	
			/* Profile Image */
			.profile_image_container {
				width: 140px;
				height: 140px;
				border-radius: 70px;
				overflow: hidden;
				border: 4px solid #f7fafc;
				box-shadow: 0 4px 10px rgba(0,0,0,0.1);
				background: #fff;
				margin-bottom: 15px;
			}
	
			.profile_img {
				width: 100%;
				height: 100%;
				object-fit: cover;
			}
	
			/* Google Linked Account */
			.linked_account {
				padding: 12px;
				background: #f0fff4;
				border: 1px solid #c6f6d5;
				border-radius: 8px;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
	
			.email_text {
				color: #22543d;
				font-weight: 600;
				font-size: 13px;
			}
	
			.unlink_btn {
				color: #c53030;
				font-size: 12px;
				cursor: pointer;
				text-decoration: underline;
			}
	
			/* Buttons Fix */
			.webix_primary button, .btn_action button {
				border-radius: 8px !important;
				font-weight: 600 !important;
			}
		</style>
</head>
	<body class="app_wsiamhs" bgcolor="#244531">
	</body>
	<script src="js/wsiamhs_routes.js" charset="utf-8"></script>
	<script src="js/wsiamhs.js" charset="utf-8"></script>
	<script src="js/wsiamhs_actions.js" charset="utf-8"></script>
	
</html>