	<!-- Main sidebar -->
	<div class="sidebar sidebar-main">
		<div class="sidebar-content">

			<!-- User menu -->
			<div class="sidebar-user">
				<div class="category-content">
					<div class="media">
						<a href="#" class="media-left"><img src="<?php echo base_url("assets/images/user_placeholder.jpg") ?>" class="img-circle img-sm" alt=""></a>
						<div class="media-body">
							<span class="media-heading text-semibold pt-10" style=""><?php echo $_SESSION['user_name']; ?></span>
						</div>
					</div>
				</div>
			</div>
			<!-- /user menu -->

			<!-- Main navigation -->
			<div class="sidebar-category sidebar-category-visible">
				<div class="category-content no-padding">
					<ul class="navigation navigation-main navigation-accordion">
						<!-- Main -->
					
						<li class="dashboard"><a href="<?php echo base_url("admin/dashboard") ?>"><i class="icon-home4"></i> <span><?= lang('Validation.dashboard') ?></span></a></li>

						<li class="que_ans_category"><a href="<?php echo base_url("admin/category") ?>"><i class="icon-grid"></i><span><?= lang('Validation.category') ?></span></a></li>
						
						<li class="user_list"><a href="<?php echo base_url("admin/users") ?>"><i class="icon-users"></i> <span><?= lang('Validation.user_list') ?></span></a></li>
						
						<li class="que_ans"><a href="<?php echo base_url("admin/question_answer_list") ?>"><i class="icon-question7"></i> <span><?= lang('Validation.question_answer') ?></span></a></li>

					<!-- 	<li class="language_list"><a href="<?php // echo base_url("/language_specification") ?>"><i class="icon-flag3"></i> <span><?php // lang('Validation.language_specification') ?></span></a></li> -->
					</ul>
				</div>
			</div>
			<!-- /main navigation -->

		</div>
	</div>
	<!-- /main sidebar -->