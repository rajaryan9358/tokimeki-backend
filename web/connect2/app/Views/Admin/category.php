<?php include_once("header.php") ?>
<!-- Page container -->
<div class="page-container">

	<!-- Page content -->
	<div class="page-content">

		<!-- Main sidebar -->
		<?php include_once('side_menu.php'); ?>
		<!-- /main sidebar -->

		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Page header -->
			<div class="page-header page-header-default">
				<div class="page-header-content">
					<div class="page-title">
						<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold"><?= lang('Validation.category_list') ?></h4>
					</div>
					<div class="heading-elements">
						<div class="heading-btn-group">
							<button type="button" class="btn btn-primary" id="add_category_btn"><?= lang('Validation.add_category') ?></button>
						</div>
					</div>
					<a class="heading-elements-toggle"><i class="icon-more"></i></a>
				</div>

				<div class="breadcrumb-line">
					<ul class="breadcrumb">
						<li><a href="<?php echo base_url('/admin/dashboard'); ?>"><i class="icon-home2 position-left"></i><?= lang('Validation.home') ?></a></li>
						<li class="active"><?= lang('Validation.category_list') ?></li>
					</ul>
				</div>
			</div>
			<!-- /page header -->

			<!-- Content area -->
			<div class="content">

				<!-- Page length options -->
				<div class="panel panel-flat">
					<div class="panel-body">
						<!-- datatable datatable-show-all -->
						<table class="table" id="category_list">
							<thead>
								<tr>
									<th>Category Name</th>
									<th>Category French Name</th>
									<th>Scope</th>
									<th>Actions</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
				<!-- /page length options -->

				<?php include_once("footer.php"); ?>

			</div>
			<!-- /content area -->

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</div>
<!-- /page container -->

<!-- Basic modal -->
<div id="category_modal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title"><?= lang('Validation.add_category') ?></h5>
			</div>
			<hr>
			<form id="category_form" method="POST">
				<div class="modal-body">
					<div class="form-group">
						<div class="row">
							<div class="col-sm-12">
								<label><?= lang('Validation.category_name') ?>*</label>
								<input type="hidden" id="cat_id" name="cat_id">
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<input type="text" id="category_name" name="category_name" class="form-control" placeholder="English Category">
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<input type="text" id="category_name_fr" name="category_name_fr" class="form-control" placeholder="French Category">
										</div>
									</div>
								</div>
							</div>
						
							<div class="col-sm-6">
								<label><?= lang('Validation.score') ?>*</label>
								<input type="number" id="score" name="score" min="0" max="10" class="form-control">
							</div>
						</div>
					</div>
				</div>
				<hr>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><?= lang('Validation.save') ?></button>
					<button type="button" class="btn btn-link" data-dismiss="modal"><?= lang('Validation.close') ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /basic modal -->

</body>

</html>

<script>
	//$("#category_list").DataTable();
	$(".que_ans_category").addClass("active");

	$("#add_category_btn").on('click',function(){
		document.getElementById("category_form").reset();
		$("#category_modal").modal("show");
		
	});

	// category list fetch data 
	loadData();
	function loadData(){
		$('#category_list').DataTable({
			'processing': true,
			'serverSide': true,
			'serverMethod': 'post',
			'ajax': {
				'url':'<?=site_url('fetchCategoryList')?>'
			},
			'columnDefs': [
					{
						"targets": 3,
						"className": "text-center"
				},
			],
			'columns': [
				{ data: 'category_name' },
				{ data: 'category_name_fr' },
				{ data: 'score' },
				{ data: 'action' , orderable: false,}
			]
		});
   }

	// Add, Edit category 
	if ($("#category_form").length > 0) {
		$("#category_form").validate({
			rules: {
				category_name: {
					required: true,
				},
				category_name_fr: {
					required: true,
				},
				score: {
					required: true,
				},
			},
			messages: {
				category_name: {
					required: "<?= lang('Validation.category_name_required_msg') ?>",
				},
				category_name_fr: {
					required: "<?= lang('Validation.category_name_required_msg') ?>",
				},
				score: {
					required: "<?= lang('Validation.score_required_msg') ?>",
				}
			},
			errorPlacement: function(label, element) {
				label.css("color", "red");
				label.insertAfter(element);
			},
			submitHandler: function(form) {
				var fd = new FormData($('#category_form')[0]);
				$.ajax({
					url: "<?php echo base_url('add_category') ?>",
					type: "POST",
					data: fd,
					dataType: "json",
					contentType: false,
					processData: false,
					success: function(resp) {
						var resp_code = resp.<?php echo ERROR_CODE ?>;
						var resp_msg = resp.<?php echo ERROR_MESSAGE ?>;

						if (resp_code == 1) {
							document.getElementById("category_form").reset();
							$.jGrowl({
								header: resp_msg,
								theme: 'bg-success'
							});

							$("#category_modal").modal("hide");
							$('#category_list').DataTable().destroy();
							loadData();
							
						} else {
							$.jGrowl({
								header: resp_msg,
								theme: 'bg-danger'
							});
						}
					}
				});
			}
		})
	}

	function edit_category(cid) {
		document.getElementById("category_form").reset();
		$("#category_modal").modal("show");
		$.ajax({
			url: "<?php echo base_url('category_list') ?>",
			type: "POST",
			data: "id=" + cid,
			dataType: "json",
			success: function(resp) {
				var code = resp.<?php echo ERROR_CODE ?>;
				var response = resp.<?php echo RESPONSE ?>;
				var message = resp.<?php echo ERROR_MESSAGE ?>;
				if (code == 1) {
					$("#cat_id").val(response['id']);
					$("#category_name").val(response['category_name']);
					$("#category_name_fr").val(response['category_name_fr']);
					$("#score").val(response['score']);
				} else {
					alert(message);
				}
			}
		});
	}

	function delete_category(id) {
		swal({
				title: "<?= lang('Validation.confirm_msg') ?>",
				text: "<?= lang('Validation.confirm_text') ?>",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#EF5350",
				confirmButtonText: "<?= lang('Validation.yes_delete_msg') ?>",
				cancelButtonText: "<?= lang('Validation.no_delete_msg') ?>",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: "<?php echo base_url('delete_category') ?>",
						type: "POST",
						data: "id=" + id,
						dataType: "json",
						success: function(resp) {

							var code = resp.<?php echo ERROR_CODE ?>;
							var errormsg = resp.<?php echo ERROR_MESSAGE ?>;
							if (code == 1) {
								swal({
									title: "<?= lang('Validation.after_delete_title') ?>",
									text: "<?= lang('Validation.confirm_dlt') ?>",
									confirmButtonColor: "#66BB6A",
									type: "success"
								});
								$('#category_list').DataTable().destroy();
								loadData();
							} else {
								alert(errormsg);
							}
						}
					});
				} else {
					swal({
						title: "<?= lang('Validation.Cancelled') ?>",
						text: "<?= lang('Validation.safe_record') ?>",
						confirmButtonColor: "#2196F3",
						type: "error"
					});
				}
			});
	}
</script>