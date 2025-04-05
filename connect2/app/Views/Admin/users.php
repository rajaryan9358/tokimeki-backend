<?php include_once("header.php"); ?>
<script type="text/javascript" src="<?php echo base_url('assets/js/core/libraries/jquery_ui/widgets.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js/plugins/media/fancybox.min.js'); ?>"></script>
<!-- Page container -->
<style type="text/css">
	.modal-backdrop.in {
    opacity: 0.8!important;
    filter: alpha(opacity=50);
}
</style>
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
						<h4><i class="icon-arrow-left52 position-left"></i><?= lang('Validation.user_list') ?></h4>
					</div>
				</div>

				<div class="breadcrumb-line">
					<ul class="breadcrumb">
						<li><a href="<?php echo base_url('/admin/dashboard'); ?>"><i class="icon-home2 position-left"></i> <?= lang('Validation.home') ?></a></li>
						<li class="active"><?= lang('Validation.user_list') ?></li>
					</ul>
				</div>
			</div>
			<!-- /page header -->

			<!-- Content area -->
			<div class="content">
				<!-- Page length options -->
				<div class="panel panel-flat">
					<div class="panel-body table-responsive">
						<table class="table" id="user_table">
							<thead>
								<tr>
									<th><?= lang('Validation.user_image') ?></th>
									<!-- <th><?= lang('Validation.user_name') ?></th> -->
									<th><?= lang('Validation.nick_name') ?></th>
									<th><?= lang('Validation.email_id') ?></th>
									<!-- <th><?= lang('Validation.date_of_birth') ?></th> -->
									<th><?= lang('Validation.point_name') ?></th>
									<th><?= lang('Validation.match_name') ?></th>
									<th><?= lang('Validation.marital_status') ?></th>
									
									<th><?= lang('Validation.total_connections') ?></th>
									<th><?= lang('Validation.total_sent_messages') ?></th>
									<th><?= lang('Validation.total_receive_messages') ?></th>
									<th><?= lang('Validation.last_connections') ?></th>
									<th><?= lang('Validation.status') ?></th>
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

<!-- Change Status form -->
<div id="modal_change_status" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content login-form">

			<!-- Form -->
			<form id="status_change" class="modal-body">
				<input type="hidden" id="user_id" name="user_id" />
				<div class="text-left">
					<h5 class="content-group"><?= lang('Validation.change_user_status') ?></h5>
					<hr>
				</div>
				<div class="form-group">
					<label><?= lang('Validation.status') ?></label>
					<select class="form-control" id="status" name="status">
						<option value="">Select Status</option>
						<option value="1">Active</option>
						<option value="2">Deactive</option>
					</select>
					<label id="basic-error" class="validation-error-label" for="basic"></label>
				</div>
				<div class="form-group">
					<button type="submit" id="statusbtn" class="btn bg-blue btn-block"><?= lang('Validation.save') ?></button>
				</div>

			</form>
			<!-- /form -->

		</div>
	</div>
</div>
<!-- /Change Status form -->


<!-- Basic modal -->
<div id="modal_default" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title"><span id="user_name"></span> - <?= lang('Validation.user_details') ?> </h5>
			</div>
			<form id="view_details_form">
				<div class="modal-body">
					<div class="form-group">
						<div class="row">
							<div class="col-sm-4">
								<label><?= lang('Validation.first_name') ?></label>
								<input type="text" id="view_fname" class="form-control">
							</div>

							<div class="col-sm-4">
								<label><?= lang('Validation.last_name') ?></label>
								<input type="text" id="view_lname" class="form-control">
							</div>
							<div class="col-sm-4">
								<label><?= lang('Validation.email_id') ?></label>
								<input type="text" id="view_emailid" class="form-control">
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="row">
							<div class="col-sm-4">
								<label><?= lang('Validation.nick_name') ?></label>
								<input type="text" id="view_nicknm" class="form-control">
							</div>
							<div class="col-sm-4">
								<label><?= lang('Validation.avtar_name') ?></label>
								<input type="text" id="view_avtarnm" class="form-control">
							</div>
							<div class="col-sm-4">
								<label><?= lang('Validation.active_deactive') ?></label>
								<input type="text" id="view_is_active" class="form-control">
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="row">
							<div class="col-sm-4">
								<label><?= lang('Validation.date_of_birth') ?></label>
								<input type="text" id="view_dob" class="form-control">
							</div>

							<div class="col-sm-4">
								<label><?= lang('Validation.is_verify') ?></label>
								<input type="text" id="view_isverify" class="form-control">
							</div>

							<div class="col-sm-4">
								<label><?= lang('Validation.is_subscribe') ?></label>
								<input type="text" id="view_issubscribe" class="form-control">
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="row">
							<div class="col-sm-4">
								<label><?= lang('Validation.google_id') ?></label>
								<input type="text" id="view_googleid" class="form-control">
							</div>

							<div class="col-sm-4">
								<label><?= lang('Validation.apple_id') ?></label>
								<input type="text" id="view_appleid" class="form-control">
							</div>

							<div class="col-sm-4">
								<label><?= lang('Validation.marital_status') ?></label>
								<input type="text" id="view_marital_status" class="form-control">
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-sm-4">
								<label><?= lang('Validation.point_name') ?></label>
								<input type="text" id="view_point" class="form-control">
							</div>

							<div class="col-sm-4">
								<label><?= lang('Validation.match_name') ?></label>
								<input type="text" id="view_matches" class="form-control">
							</div>
							<div class="col-sm-4" >
								<label><?= lang('Validation.user_image') ?></label>
								<div id="pr_image">
									
								</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-sm-3">
								<label><?= lang('Validation.total_connections') ?></label>
								<input type="text" id="total_connections" class="form-control" readonly>
							</div>

							<div class="col-sm-3">
								<label><?= lang('Validation.total_sent_messages') ?></label>
								<input type="text" id="total_sent_messages" class="form-control" readonly>
							</div>
							<div class="col-sm-3">
								<label><?= lang('Validation.total_receive_messages') ?></label>
								<input type="text" id="total_receive_messages" class="form-control" readonly>
							</div>
							<div class="col-sm-3">
								<label><?= lang('Validation.last_connections') ?></label>
								<input type="text" id="last_connections" class="form-control" readonly>
							</div>
						</div>
					</div>
				
				</div>
				
			</form>
			<div class="modal-footer">
				<button type="button" class="btn btn-link" data-dismiss="modal"><?= lang('Validation.close') ?></button>
				<!-- 	<button type="button" class="btn btn-primary">Save changes</button> -->
			</div>
		</div>
	</div>
</div>
<!-- /basic modal -->

<script>
	$(".user_list").addClass("active");

	loadData();
	function loadData() {
		$('#user_table').DataTable({
			'processing': true,
			'serverSide': true,
			'serverMethod': 'post',
			'ajax': {
				'url': '<?= site_url('getUserRecords') ?>'
			},
			'columnDefs': [
					{
						"targets": 7,
						"className": "text-center"
				},
			],
			'columns': [
				{
					data: 'profile_image'
				},
				// {
				// 	data: 'first_name'
				// },
				{
					data: 'nick_name'
				},
				{
					data: 'email_id'
				},
				// {
				// 	data: 'date_of_birth'
				// },
				{
					data: 'point'
				},
				{
					data: 'matches',
					orderable: false
				},
				{
					data: 'marital_status'
				},
				
				{
					data: 'total_connection',
					orderable: false
				},
				{
					data: 'total_sent_messages',
					orderable: false
				},
				{
					data: 'total_receiver_messages',
					orderable: false
				},
				{
					data: 'last_connection',
					orderable: false
				},
				{
					data: 'is_active',
					searchable: false
				},
				{
					data: 'action',
					orderable: false,
				}
			]
		});
	}

	function change_status(pid, is_active) {
		$("#modal_change_status").modal("show");
		document.getElementById("status_change").reset();
		document.getElementById("status").value = is_active;
		document.getElementById("user_id").value = pid;
	}

	if ($("#status_change").length > 0) {
		$("#status_change").validate({
			rules: {
				status: {
					required: true,
				},
			},
			messages: {
				status: {
					required: "<?= lang('Validation.select_status_msg') ?>",
				}
			},
			errorPlacement: function(label, element) {
				label.css("color", "red");
				label.css("padding-top", "4px");
				label.insertAfter(element);
			},
			submitHandler: function(form) {
				var fd = new FormData($('#status_change')[0]);
				$.ajax({
					url: "<?php echo base_url('user_status_change') ?>",
					type: "POST",
					data: fd,
					dataType: "json",
					contentType: false,
					processData: false,
					success: function(resp) {

						if (resp == 1) {
							document.getElementById("status_change").reset();
							$("#modal_change_status").modal("hide");
							$('#user_table').DataTable().destroy();
							$.jGrowl({
								header: "Active status change succesfully.",
								theme: 'bg-success'
							});
							loadData();
						} else {
							$.jGrowl({
								header: "Something want wrong.",
								theme: 'bg-warning'
							});
						}
					}
				});
			}
		})
	}

	function view_user_details(uid) {
		document.getElementById("view_details_form").reset();
		$.ajax({
			url: "<?php echo base_url('fetchUserDetails') ?>",
			type: "POST",
			data: "uid=" + uid,
			dataType: "json",
			success: function(resp) {
				if (resp.errorCode == <?PHP echo SUCCESS_CODE ?>) {

					$("#user_name").html(resp.response['first_name'] + ' ' + resp.response['last_name']);
					$("#view_fname").val(resp.response['first_name']);
					$("#view_lname").val(resp.response['last_name']);
					$("#view_nicknm").val(resp.response['nick_name']);
					$("#view_avtarnm").val(resp.response['avtar_name']);
					$("#view_dob").val(resp.response['date_of_birth']);
					$("#view_isverify").val((resp.response['is_verify'] == 1) ? "Verify" : "Not Verify");
					$("#view_issubscribe").val((resp.response['is_subscribe'] == 1) ? "Subscribe" : "Not Subscribe");
					$("#view_googleid").val(resp.response['google_id']);
					$("#view_appleid").val(resp.response['apple_id']);
					$("#view_marital_status").val(resp.response['marital_status']);
					$("#view_emailid").val(resp.response['email_id']);
					$("#view_point").val(resp.response['point']);
					$("#view_matches").val(resp.response['matches']);
					$("#view_emailid").val(resp.response['email_id']);
					$("#view_is_active").val((resp.response['is_active'] == 1) ? "Active" : "Deactive");
					$("#total_connections").val(resp.response['total_connection']);
					$("#total_sent_messages").val(resp.response['cnt_sent_message']);
					$("#total_receive_messages").val(resp.response['cnt_receive_message']);
					$('#pr_image').html(resp.response['profile_image']);
					$('#last_connections').val(resp.response['last_connection']);

				} else {
					alert(resp.errorMessage);
				}
			}
		});
	}
	function delete_user(id) {
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
						url: "<?php echo base_url('delete_user') ?>",
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
								$('#user_table').DataTable().destroy();
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
	 $('[data-popup="lightbox"]').fancybox({
      padding: 3,
        wheel : false,
  transitionEffect: "slide",
   // thumbs          : false,
  // hash            : false,
  loop            : true,
  // keyboard        : true,
  toolbar         : false,
  // animationEffect : false,
  // arrows          : true,
  clickContent    : false,
  hideOnOverlayClick : true
  });
</script>
</body>

</html>