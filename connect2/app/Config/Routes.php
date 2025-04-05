<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
//$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
//$routes->get('/', 'Home::index');
//$routes->get('/contact/','Home::contact');
$routes->get('/TermsandConditions/','Home::TermsandConditions');
$routes->get('/PrivacyPolicy/','Home::PrivacyPolicy');
$routes->post('/send_email', 'Home::send_email');


//login
$routes->get('/admin/','Admin\adminController::index');
$routes->post('/admin/login/auth','Admin\adminController::LoginAuth');
$routes->get('/admin/logout','Admin\adminController::logout');
// $routes->group("/admin", ["filter" => "auth"], function ($routes) 
// {

$routes->get('lang/{locale}', 'Admin\adminController::fetch_language');

// dashboard
$routes->get('/admin/dashboard','Admin/DashboardController::index' ,['filter' => 'auth']);
$routes->get('/admin/My_Profile','Admin/DashboardController::my_profile',['filter' => 'auth']);
$routes->post('/admin/My_Profile','Admin/DashboardController::update_profile',['filter' => 'auth']);

//Users
$routes->get('/admin/users','Admin/userController::index',['filter' => 'auth']);
$routes->post('/user_status_change','Admin/userController::user_status_change',['filter' => 'auth']);
$routes->post('/fetchUserDetails','Admin/userController::fetchUserDetails',['filter' => 'auth']);
$routes->post('/delete_user','Admin/userController::delete_user',['filter' => 'auth']);
$routes->post('/getUserRecords','Admin/userController::getUserRecords',['filter' => 'auth']);

//category
$routes->get('/admin/category','Admin/categoryController::index',['filter' => 'auth']);
$routes->post('/category_list','Admin/categoryController::category_list',['filter' => 'auth']);
$routes->post('/add_category','Admin/categoryController::add_category',['filter' => 'auth']);
$routes->post('/delete_category','Admin/categoryController::delete_category',['filter' => 'auth']);
$routes->post('/fetchCategoryList','Admin/categoryController::fetchCategoryList',['filter' => 'auth']);

// Question Answers
$routes->get('/admin/question_answer_list','Admin/QuestionAnswerController::index',['filter' => 'auth']);
$routes->post('/add_question_answer','Admin/QuestionAnswerController::add_question_answer',['filter' => 'auth']);
$routes->post('/question_category_list','Admin/QuestionAnswerController::category_list',['filter' => 'auth']);
$routes->post('/fetchCategoryQuestionList','Admin/QuestionAnswerController::fetchCategoryQuestionList',['filter' => 'auth']);
$routes->post('/fetch_que_ans_list','Admin/QuestionAnswerController::fetch_que_ans_list',['filter' => 'auth']);
$routes->post('/delete_answer','Admin/QuestionAnswerController::delete_answer',['filter' => 'auth']);
$routes->post('/delete_question','Admin/QuestionAnswerController::delete_question',['filter' => 'auth']);


// Language Specification
$routes->get('/language_specification','Admin/LanguageController::index',['filter' => 'auth']);
$routes->post('/add_language','Admin/LanguageController::add_language',['filter' => 'auth']);
$routes->post('/language_list','Admin/LanguageController::language_list',['filter' => 'auth']);
$routes->post('/fetch_language','Admin/LanguageController::fetch_language',['filter' => 'auth']);
$routes->post('/delete_language','Admin/LanguageController::delete_language',['filter' => 'auth']);
$routes->post('/language_json','Admin/LanguageController::language_json',['filter' => 'auth']);

// });
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
