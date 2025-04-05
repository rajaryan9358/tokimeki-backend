<?php include_once("header.php"); ?>
<script src='<?php echo base_url("assets/a076d05399.js") ?>' crossorigin='anonymous'></script>
<style type="text/css">
	i{
		font-size: 35px;
	}
	h1	
	{
		font-size: 35px;
	}
</style>
<!-- Page container -->
<div class="page-container">
	<!-- Page content -->
	<div class="page-content">
		<?php echo include_once("side_menu.php"); ?>
		<!-- Main content -->
		<div class="content-wrapper">
			<!-- Page header -->
			<div class="page-header page-header-default">
				<div class="page-header-content">
					<div class="page-title">
						<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold"><?= lang('Validation.home') ?></span> - <?= lang('Validation.dashboard') ?></h4>
					</div>
				</div>

				<div class="breadcrumb-line">
					<ul class="breadcrumb">
						<li><a href="#"><i class="icon-home2 position-left"></i> <?= lang('Validation.home') ?></a></li>
						<li class="active"><?= lang('Validation.dashboard') ?></li>
					</ul>
				</div>
			</div>
			<!-- /page header -->


			<!-- Content area -->
			<div class="content">
	<div class="row d-flex">
		<div class="col-lg-4">
			<a  class="dashboard-content" href="">
				<div class="panel bg-info">
					<div class="panel-body">
						<h1 class="no-margin"  style="text-align: center;color: white;">
							<i class="fas fa-user-check"></i>
						</h1>
						<h3 class="no-margin" style="text-align: center;color: white;">
							<?= lang('Validation.total_active_users') ?>
						</h3>
						<h1  class="no-margin"  style="text-align: center;color: white;">
							<?php echo count($active_users); ?>
						</h1> 
					</div>
					<div id="today-revenue"></div>
				</div>
			</a>
		</div>
		<div class="col-lg-4">
			<a  class="dashboard-content" href="">
				<div class="panel bg-success">
					<div class="panel-body">
						<h1 class="no-margin"  style="text-align: center;color: white;">
							<i class="fas fa-user-minus"></i>
						</h1>
						<h3 class="no-margin" style="text-align: center;color: white;">
							<?= lang('Validation.total_inactive_users') ?>
						</h3>
						<h1  class="no-margin"  style="text-align: center;color: white;">
							<?php echo count($inactive_users); ?>
						</h1> 
					</div>
					<div id="today-revenue"></div>
				</div>
			</a>
		</div>
		<div class="col-lg-4">
			<a  class="dashboard-content" href="">
				<div class="panel bg" style="background-color: sandybrown;border-color: sandybrown;">
					<div class="panel-body">
						<h1 class="no-margin"  style="text-align: center;color: white;">
							<i class="fas fa-user-clock"></i>
						</h1>
						<h3 class="no-margin" style="text-align: center;color: white;">
							<?= lang('Validation.total_new_users_last_24hours') ?>
						</h3>
						<h1  class="no-margin"  style="text-align: center;color: white;">
							<?php echo count($last_24_hours); ?>
						</h1> 
					</div>
					<div id="today-revenue"></div>
				</div>
			</a>
		</div>
		<div class="col-lg-4">
			<a  class="dashboard-content" href="">
				<div class="panel bg-pink">
					<div class="panel-body">
						<h1 class="no-margin"  style="text-align: center;color: white;">
							<i class="fas fa-user-friends"></i>
						</h1>
						<h3 class="no-margin" style="text-align: center;color: white;">
							<?= lang('Validation.avg_connection_per_user') ?>
						</h3>
						<h1  class="no-margin"  style="text-align: center;color: white;">
							<?php echo $avg_connection; ?>
						</h1> 
					</div>
					<div id="today-revenue"></div>
				</div>
			</a>
		</div>
		<div class="col-lg-4">
			<a  class="dashboard-content" href="">
				<div class="panel bg-pink" style="background-color:purple;border-color: purple;">
					<div class="panel-body">
						<h1 class="no-margin"  style="text-align: center;color: white;">
							<i class="fas fa-calendar-alt"></i>
						</h1>
						<h3 class="no-margin" style="text-align: center;color: white;">
							<?= lang('Validation.connection_to_date') ?>
						</h3>
						<h1  class="no-margin"  style="text-align: center;color: white;">
							<?php echo count($last_24_hours_chat); ?>
						</h1>
					</div>
					<div id="today-revenue"></div>
				</div>
			</a>
		</div>
		<div class="col-lg-4">
			<a  class="dashboard-content" href="">
				<div class="panel bg-primary">
					<div class="panel-body">
						<h1 class="no-margin"  style="text-align: center;color: white;">
							<i class="fas fa-comment-dots"></i>
						</h1>
						<h3 class="no-margin" style="text-align: center;color: white;">
							<?= lang('Validation.total_message_in24_hours') ?>
						</h3>
						<h1  class="no-margin"  style="text-align: center;color: white;">
							<?php echo count($last_24_hours_chat); ?>
						</h1>
					</div>
					<div id="today-revenue"></div>
				</div>
			</a>
		</div>

		<?php include_once("footer.php") ?>

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
	$(".dashboard").addClass("active");
</script>