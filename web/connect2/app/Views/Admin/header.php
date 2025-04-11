<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= lang("Validation.title") ?></title>
	<link rel="shortcut icon" href="<?php echo base_url("assets/images/favicon.png") ?>" />
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

	<!-- Data table Theme JS files -->
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/tables/datatables/datatables.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/forms/selects/select2.min.js") ?>"></script>
	<!-- /Data Table theme JS files -->


	<!-- Theme JS files -->
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/ui/moment/moment.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/visualization/d3/d3.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/visualization/d3/d3_tooltip.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/forms/styling/switchery.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/forms/styling/uniform.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/forms/selects/bootstrap_multiselect.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/pickers/daterangepicker.js") ?>"></script>

	<script type="text/javascript" src="<?php echo base_url("assets/js/core/app.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/pages/dashboard.js") ?>"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/notifications/jgrowl.min.js") ?>"></script>
	<script type="text/javascript" src="<?php echo base_url("assets/js/plugins/notifications/sweet_alert.min.js") ?>"></script>

	<!-- /theme JS files -->

</head>
<style>
	div.dataTables_wrapper div.dataTables_filter input {
    margin-left: 0.5em!important;
    display: inline-block;
    width: auto;
}
</style>
<body>

	<!-- Main navbar -->
	<div class="navbar navbar-inverse">
		<div class="navbar-header">
			<a class="navbar-brand" href="#">Toki Meki<!-- <img src="<?php echo base_url("assets/images/New Project.png") ?>" alt=""> --></a>

			<ul class="nav navbar-nav visible-xs-block">
				<li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
				<li><a class="sidebar-mobile-main-toggle"><i class="icon-paragraph-justify3"></i></a></li>
			</ul>
		</div>

		<div class="navbar-collapse collapse" id="navbar-mobile">
			<ul class="nav navbar-nav">
				<li><a class="sidebar-control sidebar-main-toggle hidden-xs"><i class="icon-paragraph-justify3"></i></a></li>
			</ul>

			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown language-switch">
					<a href="" class="dropdown-toggle" data-toggle="dropdown">
						<img src="<?php 
							if (lang("Validation.lang_type")=="English")
							 {
								echo base_url("assets/images/flags/gb.png");
							}
							else
							 {
								echo base_url("assets/images/flags/fr.png");
							}
						 ?>" class="position-left" alt="">
						<?= lang("Validation.lang_type") ?>
						<span class="caret"></span>
					</a>

					<ul class="dropdown-menu">
						<li><a href="<?= base_url('lang/en'); ?>" class="deutsch" id="english" ><img src="<?php echo base_url("assets/images/flags/gb.png"); ?>" alt=""> English</a></li>
						<li><a class="deutsch" href="<?= base_url('lang/fr'); ?>" id="franch" ><img src="<?php echo base_url("assets/images/flags/fr.png"); ?>" alt=""> French</a></li>
					</ul>
				</li>

				<li class="dropdown dropdown-user">
					<a class="dropdown-toggle" data-toggle="dropdown">
						<img src="<?php echo base_url("assets/images/user_placeholder.jpg") ?>" alt="">
						<span><?php echo $_SESSION['user_name']; ?></span>
						<i class="caret"></i>
					</a>

					<ul class="dropdown-menu dropdown-menu-right">
						<li><a href="<?php echo base_url('/admin/My_Profile') ?>"><i class="icon-user-plus"></i><?= lang("Validation.my_profile") ?></a></li>
						<li><a href="<?php echo base_url('admin/logout') ?>"><i class="icon-switch2"></i><?= lang("Validation.logout") ?></a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
	<!-- /main navbar -->

	<script>
	/* 	$(".deutsch").on('click',function(){
			var id = $(this).attr('id');
				$.ajax({
				url: "<?php echo base_url('language_json') ?>",
				type: "POST",
				data: "language=" + id,
				dataType: "json",
				success: function(resp) {
					if (resp == 1) {
						window.location.reload();
					} else {
						
					}
				}
			});
		}) */
	</script>