<?php include_once("header.php"); ?>

<!-- Page container -->
<div class="page-container">

    <!-- Page content -->
    <div class="page-content">

        <!-- Side menu -->
        <?php echo include_once("side_menu.php"); ?>

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header page-header-default">
                <div class="page-header-content">
                    <div class="page-title">
                        <h4><i class="icon-arrow-left52 position-left"></i><?= lang('Validation.my_profile') ?></h4>
                    </div>
                </div>

                <div class="breadcrumb-line">
                    <ul class="breadcrumb">
                        <li><a href="<?php echo base_url('/admin/dashboard'); ?>"><i class="icon-home2 position-left"></i><?= lang('Validation.home') ?></a></li>
                        <li class="active"><?= lang('Validation.my_profile') ?></li>
                    </ul>
                </div>
            </div>
            <!-- /page header -->


            <!-- Content area -->
            <div class="content">

                <!-- Form horizontal -->
                <div class="panel panel-flat">
                    <div class="panel-heading">
                        <h5 class="panel-title"><?= lang('Validation.my_profile') ?></h5>
                    </div>

                    <div class="panel-body">

                        <form class="form-horizontal" action="#">
                            <fieldset class="content-group">
                                <legend class="text-bold"><?= lang('Validation.basic_information') ?></legend>
                                <input type="hidden" id="admin_id" value="<?php if (isset($resp)) echo $resp['id']; ?>" />
                                <div class="form-group">
                                    <label class="control-label col-lg-2"><?= lang('Validation.user_name') ?></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="user_name" name="user_name" value="<?php if (isset($resp)) echo $resp['user_name']; ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-2"><?= lang('Validation.email_id') ?></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="email_id" name="email_id" value="<?php if (isset($resp)) echo $resp['email_id']; ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-2"><?= lang('Validation.password') ?></label>
                                    <div class="col-lg-10">
                                        <input type="password" class="form-control" id="password" name="password" value="<?php if (isset($resp)) echo $resp['password']; ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-2"><?= lang('Validation.contact_no') ?></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="contact_no" name="contact_no" value="<?php if (isset($resp)) echo $resp['contact_no']; ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-2"><?= lang('Validation.date_of_birth') ?></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="dob" name="dob" value="<?php if (isset($resp)) echo date('d/m/Y', strtotime($resp['dob'])); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-2"><?= lang('Validation.address_1') ?></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="address1" name="address1" value="<?php if (isset($resp)) echo $resp['address1']; ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-2"><?= lang('Validation.address_2') ?></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="address2" name="address2" value="<?php if (isset($resp)) echo $resp['address2']; ?>">
                                    </div>
                                </div>

                            </fieldset>

                            <!-- <div class="text-right">
									<button type="submit" class="btn btn-primary">Submit <i class="icon-arrow-right14 position-right"></i></button>
								</div> -->
                        </form>
                    </div>
                </div>
                <!-- /form horizontal -->


                <?php include_once("footer.php"); ?>

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