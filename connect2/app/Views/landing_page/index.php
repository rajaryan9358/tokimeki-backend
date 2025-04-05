<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>TOKIMEKI</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url("assets/images/favicon.png") ?>" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="<?php echo base_url('assets/landing_page_assets/css/bootstrap.min.css');?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/landing_page_assets/css/LineIcons.2.0.css');?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/landing_page_assets/css/animate.css');?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/landing_page_assets/css/tiny-slider.css');?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/landing_page_assets/css/glightbox.min.css');?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/landing_page_assets/css/main.css');?>" />
    
</head>
<style type="text/css">
    .our-achievement {
    background-color: #c39e2c;
    text-align: center;
    padding: 130px 0; 
}
.section {
    padding-top: 50px!important;
    padding-bottom: 100px!important;
    position: relative;
}
</style>
<style type="text/css">


/* Slideshow container */
.slideshow-container {
  max-width: 1000px;
  position: relative;
  margin: auto;
}

/* The dots/bullets/indicators */
.dot {
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  transition: background-color 0.6s ease;
}

.active_dot {
  background-color: #717171;
}

/* Fading animation */
.fade {
  animation-name: fade;
  animation-duration: 5.5s;
}

@keyframes fade {
  from {opacity: .4} 
  to {opacity: 1}
}

/* On smaller screens, decrease text size */
@media only screen and (max-width: 300px) {
  .text {font-size: 11px}
}
</style>
<body>
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- /End Preloader -->

    <!-- Start Header Area -->
   <?php include_once("header.php"); ?>
    <!-- End Header Area -->
    <!-- Start Hero Area -->
   <!--   <section id="home" class="hero-area">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 col-md-12 col-12">
                    <div class="hero-content">
                        <h1 class="wow fadeInLeft" data-wow-delay=".4s">Find Matches</h1>
                        <p class="wow fadeInLeft" data-wow-delay=".6s">With Tokimeki, finding your perfect match has never been easier.Our advanced matching algorithm analyzes your preferences and behavior to match you with like-minded individuals who share your interests and values. Whether you're looking for a casual date or a long-term relationship, Tokimeki has got you covered.</p>
                        <div class="button wow fadeInLeft" data-wow-delay=".8s">
                            <a href="javascript:void(0)" class="btn"><i class="lni lni-apple"></i> App Store</a>
                            <a href="javascript:void(0)" class="btn btn-alt"><i class="lni lni-play-store"></i> Google
                                Play</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-12">
                    <div class="hero-image wow fadeInRight" data-wow-delay=".4s">
                        <img src="<?php echo base_url('assets/landing_page_assets/images/6-3-23-A.png'); ?>" alt="#">
                    </div>
                </div>
            </div>
        </div>
    </section>  -->
      <section id="home" class="hero-area">
       <div class="slideshow-container">

<div class="mySlides fade">
     <div class="row align-items-center">
                <div class="col-lg-5 col-md-12 col-12">
                    <div class="hero-content">
                        <h1 class="wow fadeInLeft" data-wow-delay=".4s">Find Matches</h1>
                        <p class="wow fadeInLeft" data-wow-delay=".6s">With Tokimeki, finding your perfect match has never been easier.Our advanced matching algorithm analyzes your preferences and behavior to match you with like-minded individuals who share your interests and values. Whether you're looking for a casual date or a long-term relationship, Tokimeki has got you covered.</p>
                        <div class="button wow fadeInLeft" data-wow-delay=".8s">
                            <a href="javascript:void(0)" class="btn"><i class="lni lni-apple"></i> App Store</a>
                            <a href="javascript:void(0)" class="btn btn-alt"><i class="lni lni-play-store"></i> Google
                                Play</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-12">
                    <div class="hero-image wow fadeInRight" data-wow-delay=".4s">
                        <img src="<?php echo base_url('assets/landing_page_assets/images/6-3-23-A.png'); ?>" alt="#">
                    </div>
                </div>
            </div>
<!--   <div class="numbertext">1 / 2</div>
  <img src="<?php echo base_url('assets/landing_page_assets/images/6-3-23-A.png'); ?>" alt="#">
  <div class="text">Caption Text</div> -->
</div>

<div class="mySlides fade">
     <div class="row align-items-center">
                <div class="col-lg-5 col-md-12 col-12">
                    <div class="hero-content">
                        <h1 class="wow fadeInLeft" data-wow-delay=".4s">Find Matches</h1>
                        <p class="wow fadeInLeft" data-wow-delay=".6s">With Tokimeki, finding your perfect match has never been easier.Our advanced matching algorithm analyzes your preferences and behavior to match you with like-minded individuals who share your interests and values. Whether you're looking for a casual date or a long-term relationship, Tokimeki has got you covered.</p>
                        <div class="button wow fadeInLeft" data-wow-delay=".8s">
                            <a href="javascript:void(0)" class="btn"><i class="lni lni-apple"></i> App Store</a>
                            <a href="javascript:void(0)" class="btn btn-alt"><i class="lni lni-play-store"></i> Google
                                Play</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-12">
                    <div class="hero-image wow fadeInRight" data-wow-delay=".4s">
                        <img src="<?php echo base_url('assets/landing_page_assets/images/6-3-23-B.png'); ?>" alt="#">
                    </div>
                </div>
            </div>
  <!-- <div class="numbertext">2 </div>
  <img src="<?php echo base_url('assets/landing_page_assets/images/6-3-23-B.png'); ?>" alt="#">
  <div class="text">Caption Two</div>
</div> -->


</div>
<br>

<div style="text-align:center">
  <span class="dot"></span> 
  <span class="dot"></span> 
</div>

    </section>
    <!-- End Hero Area -->

    <!-- Start Features Area -->
    <section id="features" class="features section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h3 class="wow zoomIn" data-wow-delay=".2s">Features</h3>
                        <h2 class="wow fadeInUp" data-wow-delay=".4s">Connect with Your Soul Mates.
                        </h2>
                        <p class="wow fadeInUp" data-wow-delay=".6s">At Tokimeki, we believe that true love knows no boundaries. That's why we've created a platform that helps you connect with your soul mates from all over the world. With our global network of users, you'll have the opportunity to meet people from different backgrounds and cultures who share your passions and interests.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Feature -->
                    <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
                        <i class='fa fa-user'></i>
                        <h3><strong>User Profiles</strong></h3>
                        <p>The perfect way to showcase users' unique personalize.</p>
                    </div>
                    <!-- End Single Feature -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Feature -->
                    <div class="single-feature wow fadeInUp" data-wow-delay=".4s">
                        <i class="fa fa-comment"></i>
                        <h3><strong>Chat</strong></h3>
                        <p>The basic threshold of exchanging words</p>
                    </div>
                    <!-- End Single Feature -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Feature -->
                    <div class="single-feature wow fadeInUp" data-wow-delay=".6s">
                        <i class="fa fa-image"></i>
                        <h3><strong>File Sharing</strong></h3>
                        <p>An illustrative flair added to the get-to-know-you-better process.</p>
                    </div>
                    <!-- End Single Feature -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Feature -->
                    <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
                        <i class="fa fa-microphone"></i>
                        <h3><strong>Voice note</strong></h3>
                        <p>A channel to greater The next step of intimacy in conversation.</p>
                    </div>
                    <!-- End Single Feature -->
                </div>
              
            </div>
        </div>
    </section>
      <section id="overview" class="overview hero-area" style="padding: 0px 0 120px 0">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-12 col-12">
                    <div class="hero-image wow fadeInLeft" data-wow-delay=".4s">
                         <img src="<?php echo base_url('assets/landing_page_assets/images/6-3-23-B.png'); ?>" alt="#">
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 col-12">
                    <div class="hero-content">
                        <h1 class="wow fadeInLeft" data-wow-delay=".4s">Exchange Messages and Videos</h1>
                        <p class="wow fadeInLeft" data-wow-delay=".6s">Communication is key to any successful relationship, which is why we've made it easy for you to exchange messages and videos with your matches on Tokimeki. Our secure messaging platform allows you to chat and share media with your matches in a safe and private environment.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 col-md-12 col-12">
                    <div class="hero-content">
                        <h1 class="wow fadeInLeft" data-wow-delay=".4s">Swipe Profile Cards</h1>
                        <p class="wow fadeInLeft" data-wow-delay=".6s">Like what you see? Swipe right! Not interested? Swipe left. It's that simple. Our swipe-based interface makes it easy for you to browse through profiles and find the matches that are right for you. Plus, with our exclusive Super Like feature, you can let your potential matches know that you're really interested in getting to know them better.</p>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-12">
                    <div class="hero-image wow fadeInLeft" data-wow-delay=".4s">
                         <img src="<?php echo base_url('assets/landing_page_assets/images/6-3-23--C.png'); ?>" alt="#">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="contact-us" class="contact-us section" style="padding: 0px 0 120px 0">
<div class="container">
<div class="contact-head wow fadeInUp" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
<div class="row">
<div class="col-12">
<div class="section-title">
<h3 class="wow zoomIn" data-wow-delay=".2s" style="visibility: visible; animation-delay: 0.2s; animation-name: zoomIn;">Contact</h3>
<h2 class="wow fadeInUp" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">We’d Love To Help You</h2>
</div>
</div>
</div>
</div>
        <div class="contact-form-head section">
        <div class="container">
        <div class="row align-items-center">
       
        <div class="col-lg-8 offset-md-2 col-12">
        <div class="form-main">
        <form class="form" method="post" id="contact_usFrom" action="<?php echo base_url('/send_email') ?>">
        <div class="row">
        <div class="col-lg-4 col-12">
        <div class="form-group">
        <input name="name" id="name" type="text" placeholder="Your Name" required="required">
        </div>
        </div>
        <div class="col-lg-4 col-12">
        <div class="form-group">
        <input name="email" type="email" id="email" placeholder="Your Email" required="required">
        </div>
        </div>
        <div class="col-lg-4 col-12">
        <div class="form-group">
        <input name="phone" type="text" id="phone" placeholder="Your Phone" required="required"  onkeypress="return isNumberKey(event)">
        </div>
        </div>
        <div class="col-12">
        <div class="form-group message">
        <textarea name="message" id="message" placeholder="Your Message"></textarea>
        </div>
        </div>
        <div class="col-12" style="text-align: center;">
        <div class="col-md-3 offset-md-4 button" style="width:30%;text-align: center;">
        <button type="submit" class="btn button">Submit Message</button>
        </div>
        </div>
        </div>
        </form>
        </div>
        </div>
        </div>
        </div>
        </div>
    </section>
    <!-- End Features Area -->
  

 <!--    
    <section class="our-achievement section">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 col-md-12 col-12">
                    <div class="title">
                        <h2>Trusted by developers from over 80 planets</h2>
                        <p>Building a relationship-centric dating app with social features.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-12">
                            <div class="single-achievement wow fadeInUp" data-wow-delay=".2s">
                                <h3 class="counter"><span id="secondo1" class="countup" cup-end="100">100</span>%</h3>
                                <p>satisfaction</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-12">
                            <div class="single-achievement wow fadeInUp" data-wow-delay=".4s">
                                <h3 class="counter"><span id="secondo2" class="countup" cup-end="120">120</span>K</h3>
                                <p>Happy Users</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-12">
                            <div class="single-achievement wow fadeInUp" data-wow-delay=".6s">
                                <h3 class="counter"><span id="secondo3" class="countup" cup-end="125">125</span>k+</h3>
                                <p>Downloads</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    
    <?php include_once('footer.php'); ?>
    <!--/ End Footer Area -->

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    <!-- ========================= JS here ========================= -->
    <script src="<?php echo base_url('assets/landing_page_assets/js/bootstrap.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/landing_page_assets/js/wow.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/landing_page_assets/js/tiny-slider.js'); ?>"></script>
    <script src="<?php echo base_url('assets/landing_page_assets/js/glightbox.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/landing_page_assets/js/count-up.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/landing_page_assets/js/main.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url("assets/js/plugins/notifications/jgrowl.min.js") ?>"></script>
    <script type="text/javascript">

        //====== counter up 
        var cu = new counterUp({
            start: 0,
            duration: 2000,
            intvalues: true,
            interval: 100,
            append: " ",
        });
        cu.start();
    </script>
</body>
<script type="text/javascript">
    function isNumberKey(evt) {
  var charCode = (evt.which) ? evt.which : evt.keyCode
  if (charCode > 31 && (charCode < 48 || charCode > 57))
    return false;
  return true;
}
    if ($("#contact_usFrom").length > 0) {
        $("#contact_usFrom").validate({
            rules: {
                name: {
                    required: true,
                },
                email: {
                    required: true,
                    email: true,
                },
                phone: {
                    required: true,
                    number:true
                    
                },
                message: {
                    required: true,
                    
                }
            },
            messages: {
                name: {
                    required: "Please Enter Name",
                },
                email: {
                    required: "Please Enter Email",
                },
                phone: {
                    required: "Please Enter Phone No",
                    
                },
                message: {
                   required: "Please Enter Message",
                    
                }
            },
            errorPlacement: function(label, element) {
                label.css("color", "red");
                label.css("padding-top", "4px");
                label.insertAfter(element);
            },
            //  submitHandler: function (form, e) 
            //  {
            //   e.preventDefault();
            //   // console.log('inside');
            //   // return false;
            //     var FormData_val = new FormData($('#contact_usFrom')[0]);
            //     $.ajax({
            //         url: "<?php echo base_url('send_email') ?>",
            //         type: "POST",
            //         data: FormData_val,
            //         dataType: 'json',
            //         processData: false,  // tell jQuery not to process the data
            //         contentType: false,  // tell jQuery not to set contentType
            //         success: function (data) {
                
            //             console.log(resp);
            //             // var code = resp.<?php echo ERROR_CODE ?>;
            //             // var response = resp.<?php echo ERROR_MESSAGE ?>;
            //             // if (code == 1) {
            //             //     document.getElementById("contact_usFrom").reset();
            //             //     window.location = '<?php echo base_url('admin/dashboard') ?>';
            //             // } else {
            //             //     $("#error_msg").addClass('alert-danger');
            //             //     $("#error_msg").html(response);
            //             // }
            //         }
            //     });
            // }
        })
    }

</script>
<?php 
    if (isset($_SESSION['email_sent'])) 
    {
      if ($_SESSION['email_sent']==1) 
      {
         ?>
         <script type="text/javascript">
            console.log('here');
             $.jGrowl({
                        header: "Active status change succesfully.",
                        theme: 'bg-success'
                            });
         </script>
         <?php
      }
    }
 ?>
<script>
let slideIndex = 0;
showSlides();

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  slideIndex++;
  if (slideIndex > slides.length) {slideIndex = 1}    
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active_dot", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active_dot";
  setTimeout(showSlides, 4000); // Change image every 2 seconds
}
</script>
</html>