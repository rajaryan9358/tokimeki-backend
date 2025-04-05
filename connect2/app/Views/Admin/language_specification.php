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
						<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold"><?= $_SESSION['lang']['language_specification_list'] ?></h4>
					</div>
					<div class="heading-elements">
						<div class="heading-btn-group">
							<button type="button" class="btn btn-primary" id="add_language_btn"><?= $_SESSION['lang']['add_language_specification'] ?></button>
						</div>
					</div>
					<a class="heading-elements-toggle"><i class="icon-more"></i></a>
				</div>

				<div class="breadcrumb-line">
					<ul class="breadcrumb">
						<li><a href="/dashboard"><i class="icon-home2 position-left"></i><?= $_SESSION['lang']['home'] ?></a></li>
						<li class="active"><?= $_SESSION['lang']['language_specification_list'] ?></li>
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
						<table class="table" id="language_list">
							<thead>
								<tr>
									<th>Label</th>
									<th>English Label</th>
									<th>Franch Label</th>
									<th class="text-center">Actions</th>
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
<div id="language_modal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title"><?= $_SESSION['lang']['add_language_specification'] ?></h5>
			</div>
			<hr>
			<form id="language_form" method="POST">
				<div class="modal-body">
					<div class="form-group">
						<div class="row">
							<div class="col-sm-12 form-group">
								<input type="hidden" id="lid" name="lid">
								<label><?= $_SESSION['lang']['label_code'] ?>*</label>
								<input type="text" id="label" name="label" class="form-control">
							</div>
							<div class="col-sm-12 form-group">
								<label><?= $_SESSION['lang']['english_label'] ?>*</label>
								<input type="text" id="en_lang" name="en_lang" class="form-control">
							</div>
                            <div class="col-sm-12 form-group">
								<label><?= $_SESSION['lang']['french_label'] ?>*</label>
								<input type="text" id="fr_lang" name="fr_lang" class="form-control">
							</div>
						</div>
					</div>
				</div>
				<hr>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><?= $_SESSION['lang']['save'] ?></button>
					<button type="button" class="btn btn-link" data-dismiss="modal"><?= $_SESSION['lang']['close'] ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /basic modal -->

</body>

</html>

<script>

	$(".language_list").addClass("active");

	$("#add_language_btn").on('click',function(){
		$("#lid").val("");
		document.getElementById("language_form").reset();
		$("#language_modal").modal("show");
	})
	
	// Language list fetch data 
	loadData();
	function loadData(){
		$('#language_list').DataTable({
			'processing': true,
			'serverSide': true,
			'serverMethod': 'post',
			'ajax': {
				'url':'<?=site_url('language_list')?>'
			},
			'columns': [
				{ data: 'label' },
				{ data: 'en_lang' },
				{ data: 'fr_lang' },
				{ data: 'action' , orderable: false,}
			]
		});
   }

	// Add, Edit Language 
	if ($("#language_form").length > 0) {
		$("#language_form").validate({
			rules: {
				label: {
					required: true,
				},
				en_lang: {
					required: true,
				},
                fr_lang: {
					required: true,
				},
			},
			messages: {
				label: {
					required: "<?= $_SESSION['lang']['label_required_msg'] ?>",
				},
				en_lang: {
					required: "<?= $_SESSION['lang']['en_label_required_msg'] ?>",
				},
                fr_lang: {
					required: "<?= $_SESSION['lang']['fr_label_required_msg'] ?>",
				},
			},
			errorPlacement: function(label, element) {
				label.css("color", "red");
				label.insertAfter(element);
			},
			submitHandler: function(form) {
				var fd = new FormData($('#language_form')[0]);
				$.ajax({
					url: "<?php echo base_url('add_language') ?>",
					type: "POST",
					data: fd,
					dataType: "json",
					contentType: false,
					processData: false,
					success: function(resp) {
						var resp_code = resp.<?php echo ERROR_CODE ?>;
						var resp_msg = resp.<?php echo ERROR_MESSAGE ?>;

						if (resp_code == 1) {
							document.getElementById("language_form").reset();
							$.jGrowl({
								header: resp_msg,
								theme: 'bg-success'
							});

							$("#language_modal").modal("hide");
							$('#language_list').DataTable().destroy();
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

	function edit_language(lid) {
		document.getElementById("language_form").reset();
		$("#language_modal").modal("show");
		$.ajax({
			url: "<?php echo base_url('fetch_language') ?>",
			type: "POST",
			data: "lid=" + lid,
			dataType: "json",
			success: function(resp) {
				var code = resp.<?php echo ERROR_CODE ?>;
				var response = resp.<?php echo RESPONSE ?>;
				var message = resp.<?php echo ERROR_MESSAGE ?>;
				if (code == 1) {
					$("#lid").val(response['id']);
					$("#label").val(response['label']);
					$("#en_lang").val(response['en_lang']);
					$("#fr_lang").val(response['fr_lang']);
				} else {
					alert(message);
				}
			}
		});
	}

	function delete_language(id) {
		swal({
				title: "Are you sure?",
				text: "You will not be able to recover this record!",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#EF5350",
				confirmButtonText: "Yes, delete it!",
				cancelButtonText: "No, cancel pls!",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: "<?php echo base_url('delete_language') ?>",
						type: "POST",
						data: "id=" + id,
						dataType: "json",
						success: function(resp) {

							var code = resp.<?php echo ERROR_CODE ?>;
							var errormsg = resp.<?php echo ERROR_MESSAGE ?>;
							if (code == 1) {
								swal({
									title: "Deleted!",
									text: "Your record has been deleted.",
									confirmButtonColor: "#66BB6A",
									type: "success"
								});
								$('#language_list').DataTable().destroy();
								loadData();
							} else {
								alert(errormsg);
							}
						}
					});
				} else {
					swal({
						title: "Cancelled",
						text: "Your record is safe :)",
						confirmButtonColor: "#2196F3",
						type: "error"
					});
				}
			});
	}

</script>