<?php include_once("header.php"); ?>

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

				<!-- Dashboard content -->
				<div class="row">

				</div>
				<!-- /dashboard content -->


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