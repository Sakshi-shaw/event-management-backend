
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/service','ServiceController::index');
// $routes->get('/login','LoginController::index');
$routes->post('/register', 'RegistrationController::register');

$routes->post('/login', 'RegistrationController::login');
$routes->post('/logout', 'RegistrationController::logout');
$routes->post('/checkAuth', 'RegistrationController::checkAuth');

$routes->get('getDepartments', 'DepartmentsController::getDepartments');
$routes->post('departments', 'DepartmentsController::getDepartmentsByDegree');
$routes->post('insertDepartment', 'DepartmentsController::insertDepartment');


$routes->get('degrees', 'DegreeController::getDegrees');
$routes->post('insertDegree', 'DegreeController::insertDegree');


$routes->get('colleges', 'CollegesController::getColleges');
$routes->post('insertCollege', 'CollegesController::insertCollege');


$routes->get('students', 'StudentController::index');
$routes->get('/students/(:num)', 'StudentController::getStudentById/$1');



$routes->put('updateStudent', 'StudentController::updateData');

$routes->get('/teachers/(:num)', 'TeachersController::getTeacherById/$1');
$routes->put('updateTeacher', 'TeachersController::updateData');

//$routes->get('events', 'StudentController::getEvents');

// Route to fetch all rooms
$routes->get('rooms', 'RoomsController::index');

$routes->get('events', 'EventsController::index');
$routes->post('createEvent', 'EventsController::create');
$routes->post('upload_image', 'EventsController::uploadEventImage');


$routes->post('venues', 'EventsController::getAvailableVenues');
$routes->get('geteventspast', 'EventsController::getPastEvents');
$routes->get('geteventsupcoming', 'EventsController::getUpcomingEvents');
$routes->get('registrations/teacher/(:num)', 'EventsController::getEventsByTeacher/$1');
$routes->get('getRecentEventsPast', 'EventsController::getRecentPastEvents');

$routes->get('events/requested/(:num)', 'EventsController::getEventByRoomOwner/$1');
$routes->get('events/accepted/(:num)', 'EventsController::getAcceptedEventByRoomOwner/$1');
$routes->get('events/rejected/(:num)', 'EventsController::getRejectedEvents/$1');
$routes->post('acceptRegistration', 'EventsController::updateRegistrationAccept');
$routes->post('rejectRegistration', 'EventsController::updateRegistrationReject');






$routes->get('/eventtypes', 'EventTypesController::index');

$routes->get('getRegistrations', 'EventRegistrationController::getAllRegistrations');
$routes->get('registrations/student/(:num)', 'EventRegistrationController::getRegistrationsByStudent/$1');
$routes->post('eventregistration', 'EventRegistrationController::registerEvent');



$routes->post('subscribe', 'StudentController::subscribe');
$routes->post('send-emails', 'StudentController::sendEmails');




$routes->get('getEventLevels', 'EventLevelsController::getEventLevels');


$routes->post('create/:event_id', 'NotificationController::createNotificationForEvent');
$routes->get('getNotification', 'NotificationController::index');
$routes->get('/getNotificationRequestedById/(:num)', 'NotificationController::getNotificationByRequestedBy/$1');
$routes->put('notifications/(:segment)/hide', 'NotificationController::hideNotification/$1');


$routes->get('roomfacilities/(:num)', 'RoomFacilitiesController::index/$1');


$routes->post('/notifications/markAsRead/(:num)', 'NotificationController::markAllAsRead/$1');



$routes->post('forget-password', 'StudentController::forgetPassword');
$routes->post('reset-password', 'StudentController::resetPassword');




//google calender
// app/Config/Routes.php

$routes->get('events/send', 'EventsController::sendEmailAndAddEvent');


