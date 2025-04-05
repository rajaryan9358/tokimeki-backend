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
						<h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold"><?= lang('Validation.question_answer_list') ?></h4>
					</div>
					<div class="heading-elements">
						<div class="heading-btn-group">
							<button type="button" class="btn btn-primary" data-toggle="modal" id="add_ques_ans"><?= lang('Validation.add_question_answer') ?></button>
						</div>
					</div>
					<a class="heading-elements-toggle"><i class="icon-more"></i></a>
				</div>

				<div class="breadcrumb-line">
					<ul class="breadcrumb">
						<li><a href="<?php echo base_url('/admin/dashboard'); ?>"><i class="icon-home2 position-left"></i><?= lang('Validation.home') ?></a></li>
						<li class="active"><?= lang('Validation.question_answer_list') ?></li>
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
						<table class="table" id="que_ans_list">
							<thead>
								<tr>
									<th>Category Name</th>
									<th width="400px;">Question</th>
									<th width="400px;">French Question</th>
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
<div id="question_answer_modal" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title"><?= lang('Validation.add_question_answer') ?></h5>
			</div>
			<hr>
			<form id="question_answer_form" method="POST">
				<div class="modal-body ">
					<div class="form-group">
						<div class="row">
							<div class="form-group col-sm-6">
								<input type="hidden" id="id" name="id">
								<label><?= lang('Validation.select_category') ?>*</label>
								<select id="category_name" name="category_name" class="form-control"></select>
							</div>

							<div class="col-sm-12">
								<label><?= lang('Validation.question') ?>*</label>
								<div class="form-group">
									<input type="text" id="question" name="question" class="form-control" placeholder="English Question">
								</div>
								<div class="form-group">
									<input type="text" id="question_fr" name="question_fr" class="form-control" placeholder="French Question">
								</div>
                                <hr>
							</div>
                            <div class="col-md-12 row" id="ans_que_0">
                                <div class="form-group col-sm-8">
                                    <label><?= lang('Validation.answer_1') ?>*</label>
									<input type="hidden" id="ans_id_0" name="ans_id[]">
									<div class="form-group">
                                    <input type="text" id="answers_0" name="answers[]" class="form-control" placeholder="English Answer 1">
									</div>
									<div class="form-group">
									<input type="text" id="answers_fr_0" name="answers_fr[]" class="form-control" placeholder="French Answer 1">
									</div>
                                </div>
                                <div class="form-group col-sm-2">
                                    <label><?= lang('Validation.score') ?>*</label>
                                    <input type="number" id="score_0" name="score[]" class="form-control">
                                </div>
								<div class="col-sm-2 action" id="action_0" style="margin-top:30px;">  
                                </div>
                            </div>
                            <div class="col-md-12 row" id="ans_que_1">
                                <div class="form-group col-sm-8">
                                    <label><?= lang('Validation.answer_2') ?></label>
									<input type="hidden" id="ans_id_1" name="ans_id[]">
									<div class="form-group">
                                    	<input type="text" id="answers_1" name="answers[]" class="form-control" placeholder="English Answer 2">
									</div>
									<div class="form-group">
                                    	<input type="text" id="answers_fr_1" name="answers_fr[]" class="form-control" placeholder="French Answer 2">
									</div>
                                </div>
                                <div class="form-group col-sm-2">
                                    <label><?= lang('Validation.score') ?></label>
                                    <input type="number" id="score_1" name="score[]" class="form-control">
                                </div>
								<div class="col-sm-2 action" id="action_1" style="margin-top:30px;">  
                                </div>
                            </div>
                            <div class="col-md-12 row" id="ans_que_2">
                                <div class="form-group col-sm-8">
                                    <label><?= lang('Validation.answer_3') ?></label>
									<input type="hidden" id="ans_id_2" name="ans_id[]">
									<div class="form-group">
                                    	<input type="text" id="answers_2" name="answers[]" class="form-control" placeholder="English Answer 3">
									</div>
									<div class="form-group">
                                    	<input type="text" id="answers_fr_2" name="answers_fr[]" class="form-control" placeholder="French Answer 3">
									</div>
                                </div>
                                <div class="form-group col-sm-2">
                                    <label><?= lang('Validation.score') ?></label>
                                    <input type="number" id="score_2" name="score[]" class="form-control">
                                </div>
								<div class="col-sm-2 action" id="action_2" style="margin-top:30px;">  
                                </div>
                            </div>
                            <div class="col-md-12 row" id="ans_que_3">
                                <div class="form-group col-sm-8">
                                    <label><?= lang('Validation.answer_4') ?></label>
									<input type="hidden" id="ans_id_3" name="ans_id[]">
									<div class="form-group">
                                    	<input type="text" id="answers_3" name="answers[]" class="form-control" placeholder="English Answer 4">
									</div>
									<div class="form-group">
                                    	<input type="text" id="answers_fr_3" name="answers_fr[]" class="form-control" placeholder="French Answer 4">
									</div>
                                </div>
                                <div class="form-group col-sm-2 ">
                                    <label><?= lang('Validation.score') ?></label>
                                    <input type="number" id="score_3" name="score[]" class="form-control">
                                </div>
								<div class="col-sm-2 action" id="action_3" style="margin-top:30px;">  
                                </div>
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
	$(".que_ans").addClass("active");

    $("#add_ques_ans").on('click',function(){

        $("#question_answer_modal").modal("show");
		document.getElementById('question_answer_form').reset();
		$(".action").html("");
        $.ajax({
			url: "<?php echo base_url('question_category_list') ?>",
			type: "POST",
			dataType: "json",
			success: function(resp) {
				var code = resp.<?php echo ERROR_CODE ?>;
                var respsonse = resp.<?php echo RESPONSE ?>;
				$("#category_name").html("");
				if (code == 1) {
                    var data = '';
                    data += "<option value=''>Select Category</option>";
                    for(var i=0;i<respsonse.length;i++){
                        data += "<option value='"+ respsonse[i]['id']+"'>" + respsonse[i]['category_name'] +"(" + respsonse[i]['category_name_fr'] + ")</option>";
                    }
                    $("#category_name").append(data);
				} else {
					data += "<option value=''>No Category Found.</option>";
					$("#category_name").html(data);
				}
			}
		});
    });

	// Question Answer list fetch data 
	loadData();
	function loadData(){
		$('#que_ans_list').DataTable({
			'processing': true,
			'serverSide': true,
			'serverMethod': 'post',
			'ajax': {
				'url':'<?=site_url('fetchCategoryQuestionList')?>'
			},
			'columnDefs': [
					{
						"targets": 3,
						"className": "text-center"
				},
			],
			'columns': [
				{ data: 'category_name'},
				{ data: 'question' },
				{ data: 'question_fr'},
				{ data: 'action' , orderable: false,}
			]
		});
   }

	// Add, Edit Queation Answer 
	if ($("#question_answer_form").length > 0) {

		$("#question_answer_form").validate({
			rules: {
				category_name: {
					required: true,
				},
				question: {
					required: true,
				},
                "answers[]": {
					required: true,
				},
				"answers_fr[]": {
					required: true,
				},
                "score[]": {
                    required: true,
                    digits: true,
                    min:0,
                    max:10
                },
			},
			messages: {
				category_name: {
					required: "<?= lang('Validation.category_required_msg') ?>",
				},
				question: {
					required: "<?= lang('Validation.question_required_msg') ?>",
				},
                "answers[]": {
					required: "<?= lang('Validation.answer_required_msg') ?>",
				},
				"answers_fr[]": {
					required: "<?= lang('Validation.answer_required_msg') ?>",
				},
                "score[]": {
					required: "<?= lang('Validation.score_required_msg') ?>",
				},
			},
			errorPlacement: function(label, element) {
				label.css("color", "red");
				label.insertAfter(element);
			},
			submitHandler: function(form) {
				var fd = new FormData($('#question_answer_form')[0]);
				$.ajax({
					url: "<?php echo base_url('add_question_answer') ?>",
					type: "POST",
					data: fd,
					dataType: "json",
					contentType: false,
					processData: false,
					success: function(resp) {
						var resp_code = resp.<?php echo ERROR_CODE ?>;
						var resp_msg = resp.<?php echo ERROR_MESSAGE ?>;

						if (resp_code == 1) {
							document.getElementById("question_answer_form").reset();
							$.jGrowl({
								header: resp_msg,
								theme: 'bg-success'
							});

							$("#question_answer_modal").modal("hide");
							$('#que_ans_list').DataTable().destroy();
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

	function edit_que_ans(id) {

		$(".action").html("");
		document.getElementById("question_answer_form").reset();
		$("#question_answer_modal").modal("show");
		$.ajax({
			url: "<?php echo base_url('fetch_que_ans_list') ?>",
			type: "POST",
			data: "id=" + id,
			dataType: "json",
			success: function(resp) {
				var code = resp.<?php echo ERROR_CODE ?>;
				var response = resp.<?php echo RESPONSE ?>;
				if (code == 1) {
					$("#id").val(response['id']);
					$("#category_name").disabled = true;
					$("#category_name").html("<option value='"+ response['category_id'] + "'> "+ response['category_name'] + "</option> ");
					$("#question").val(response['question']);
					$("#question_fr").val(response['question_fr']);
					var ans_data = response['ans_data'];
					for(var i=0;i<ans_data.length;i++){
						$("#action_"+i).html("");
						$("#ans_id_"+i).val(ans_data[i]['id']);
						$("#answers_"+i).val(ans_data[i]['answer']);
						$("#answers_fr_"+i).val(ans_data[i]['answer_fr']);
						$("#score_"+i).val(ans_data[i]['score']);
						if(ans_data.length != 1)
							$("#action_"+i).html('<a href="#"  onclick="delete_ans(' + ans_data[i]['id'] + ','+ i +','+response['id']+')" title="Delete Answer"><i class="icon-trash" style="font-size:x-large;"></i></a>');
					}
				} else {
					alert(resp.errorMessage);
				}
			}
		});
	}


	function delete_ans(ans_id,div_id,question_id){
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
						url: "<?php echo base_url('delete_answer') ?>",
						type: "POST",
						data: "id=" + ans_id,
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
								document.getElementById("question_answer_form").reset();
								$("#action_"+div_id).html("");
								edit_que_ans(question_id);
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

	function delete_question(id) {
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
						url: "<?php echo base_url('delete_question') ?>",
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
								$('#que_ans_list').DataTable().destroy();
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