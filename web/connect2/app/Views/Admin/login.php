<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Toki moki</title>

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="<?php echo base_url("assets/css/icons/icomoon/styles.css") ?>" rel="stylesheet" type="text/css">
	<link href="<?php echo base_url("assets/css/bootstrap.css") ?>" rel="stylesheet" type="text/css">
	<link href="<?php echo base_url("assets/css/core.css") ?>" rel="stylesheet" type="text/css">
	<link href="<?php echo base_url("assets/css/components.css") ?>" rel="stylesheet" type="text/css">
	<link href="<?php echo base_url("assets/css/colors.css") ?>" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/loaders/pace.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/core/libraries/jquery.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/core/libraries/bootstrap.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/loaders/blockui.min.js") ?>"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/forms/styling/uniform.min.js") ?>"></script>

	<script type="text/javascript" src="<?php echo base_url("assets/js/core/app.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/pages/login.js") ?>"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
	<!-- /theme JS files -->

</head>

<body class="login-container">

	<!-- Main navbar -->
	<div class="navbar navbar-inverse">
		<div class="navbar-header">
			<a class="navbar-brand" href="#">Toki Meki</a>

			<ul class="nav navbar-nav pull-right visible-xs-block">
				<li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
			</ul>
		</div>
	</div>
	<!-- /main navbar -->


	<!-- Page container -->
	<div class="page-container">
		<!-- Page content -->
		<div class="page-content">
			<!-- Main content -->
			<div class="content-wrapper">
				<!-- Content area -->
				<div class="content">
					<!-- Advanced login -->
					<form id="login_form" method="POST">
						<div class="panel panel-body login-form">
							<div class="text-center">

								<img height="55px" width="100px" src="<?php echo base_url("assets/images/tokiMokiLogo.png") ?>" />
								<h5 class="content-group">Login to your account <small class="display-block">Your credentials</small></h5>
							</div>
							<div class="alert" id="error_msg"></div>
							<div class="form-group has-feedback has-feedback-left">
								<div class="form-control-feedback">
									<i class="icon-user text-muted"></i>
								</div>
								<input type="text" class="form-control" name="email_id" id="email_id" value="<?= old('email') ?>" placeholder="Email Id">
							</div>

							<div class="form-group has-feedback has-feedback-left">
								<div class="form-control-feedback">
									<i class="icon-lock2 text-muted"></i>
								</div>
								<input type="password" class="form-control" name="password" id="password" placeholder="Password" value="<?= old('email') ?>">
							</div>

							<div class="form-group">
								<button type="submit" class="btn bg-blue btn-block" id="sign_in">Login <i class="icon-arrow-right14 position-right"></i></button>
							</div>
						</div>
					</form>
					<!-- /advanced login -->
					<!-- Footer -->
					<div class="footer text-muted text-center">
						&copy; 2021. <a href="#">Toki meki </a> by <a href="https://www.narolainfotech.com/" target="_blank">NISL</a>
					</div>
					<!-- /footer -->
				</div>
				<!-- /content area -->
			</div>
			<!-- /main content -->
		</div>
		<!-- /page content -->
	</div>
	<!-- /page container -->
</body>

</html>

<script>
	if ($("#login_form").length > 0) {
		$("#login_form").validate({
			rules: {
				email_id: {
					required: true,
				},
				password: {
					required: true,
					minlength: 8,
				}
			},
			messages: {
				email_id: {
					required: "Please Enter Valid Email Id",
				},
				password: {
					required: "Please Enter Valid Password",
				}
			},
			errorPlacement: function(label, element) {
				label.css("color", "red");
				label.css("padding-top", "4px");
				label.insertAfter(element);
			},
			submitHandler: function(form) {
				var fd = new FormData($('#login_form')[0]);
				$.ajax({
					url: "<?php echo base_url('admin/login/auth') ?>",
					type: "POST",
					data: fd,
					dataType: "json",
					contentType: false,
					processData: false,
					success: function(resp) {
						var code = resp.<?php echo ERROR_CODE ?>;
						var response = resp.<?php echo ERROR_MESSAGE ?>;
						if (code == 1) {
							document.getElementById("login_form").reset();
							window.location = '<?php echo base_url('admin/dashboard') ?>';
						} else {
							$("#error_msg").addClass('alert-danger');
							$("#error_msg").html(response);
						}
					}
				});
			}
		})
	}
</script>