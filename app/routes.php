<?php
/** Opening route 
 * Reroute to Login Page
*/
$app->get('/', function($request, $response){
    return $response->withRedirect($this->router->pathFor('admin.login'));
});

/** Authentication Routes
 *
 * Routes that Authenticate a User into the System
 */
$app->group('/auth', function() use ($app){
    /** Customer Online Register Routes */
    $this->get('/register', 'AuthController:getRegister')->setName('auth.register');
    $this->post('/register', 'AuthController:postRegister');

    /** Customer Login Routes 
    $this->get('/login', 'AuthController:getLogIn')->setName('auth.login');
    $this->post('/login', 'AuthController:postLogIn'); */

    /** Admin/Agent Login Routes */
    $this->get('/admin_login', 'AuthController:getAdminLogIn')->setName('admin.login');
    $this->post('/admin_login', 'AuthController:postAdminLogIn');

    /** Logout */
    $this->get('/logout', 'AuthController:logout')->setName('admin.logout');

});

/** Api Routes
 *
 * Api Routes that give remote access into the system
 */
$app->group('/api', function() use ($app){
    /** Get Session Data  */
    $this->get('/sessionData', 'ApiController:getSessionData');

    /** User Routes */
    $this->get('/Users', 'ApiController:getAllUsers'); 
    $this->get('/Customers', 'ApiController:getAllCustomers');
    $this->get('/Admin', 'ApiController:getAllAdmin');
    $this->get('/Agents', 'ApiController:getAllAgents'); 
    $this->get('/Deleted', 'ApiController:getAllDeleted');   
    $this->post('/addUser', 'ApiController:postAddUser');
    $this->get('/delete_user/{id}', 'ApiController:deleteUser'); 
    $this->get('/restore_user/{id}', 'ApiController:restoreUser');    
    
    /** Company Routes */
    $this->get('/allCompanies', 'ApiController:getAllCompanies');
    $this->post('/addCompany', 'ApiController:postAddCompany');    
    $this->get('/delete_company/{id}', 'ApiController:deleteCompany'); 

    /** Booking Routes */
    $this->get('/getAgentBookings', 'ApiController:getAgentBookings');
    $this->get('/getCompletedBookings', 'ApiController:getCompletedBookings');
    $this->get('/getUncompleteBookings', 'ApiController:getUncompletedBookings');
    $this->post('/addBooking', 'ApiController:postAddBooking');

    /** Package Routes */
    $this->get('/allPackages', 'ApiController:getAllPackages');
    $this->get('/getAgentPackages/{id}', 'ApiController:getAgentPackages');
    $this->get('/getActivePackages', 'ApiController:getActivePackages');
    $this->get('/getExpiredPackages', 'ApiController:getExpiredPackages');
    $this->post('/addPackage', 'ApiController:postAddPackage');

    /** Activity Routes */
    $this->get('/getAgentActivities', 'ApiController:getAgentAcitivity');
    $this->post('/addActivity', 'ApiController:postAddAcitivity');

    /** Wallet Routes */
    $this->get('/getSingleWallet/{id}', 'ApiController:getSingleWallet');

    /** Upload Routes */
    $this->post('/uploadCSV', 'ApiController:uploadCSV');
});

/** Admin Routes
 *
 * Routes used by the admin dashboard
 */
$app->group('/admin', function() use ($app){

    /** Reroute to dashboard */
    $this->get('/', function($resquest, $response, array $args)
    {
        return $response->withRedirect($this->router->pathFor('admin.dash'));
    });

    /** Dashboard */
    $this->get('/dash','AdminController:dash')->setName('admin.dash');

    /** Profile */
    $this->get('/profile', 'AdminController:userProfile')->setName('admin.profile');

    /** Users */
    $this->get('/users', 'AdminController:allUsers')->setName('admin.users');
    $this->get('/customers', 'AdminController:allCustomers')->setName('admin.customers');
    $this->get('/agents', 'AdminController:allAgents')->setName('admin.agents');
    $this->get('/admin', 'AdminController:allAdmin')->setName('admin.admin');
    $this->get('/deleted', 'AdminController:allDeleted')->setName('admin.deleted');
    $this->get('/view_user/{id}', 'AdminController:viewUser')->setName('admin.viewUser');
    $this->get('/add_a_user', 'AdminController:getAddUser')->setName('admin.addUser');
    $this->post('/add_a_user', 'AdminController:postAddUser')->setName('admin.postAddUser');
    $this->post('/edit_user/{id}', 'AdminController:postEditUser')->setName('admin.postEditUser');
    $this->get('/delete_user/{id}', 'AdminController:deleteUser')->setName('admin.deleteUser');

    /** Companies */
    $this->get('/companies', 'AdminController:allCompanies')->setName('admin.companies');
    $this->get('/add_a_company', 'AdminController:addCompany')->setName('admin.addCompany');
    $this->post('/add_a_company', 'AdminController:postAddCompany')->setName('admin.postAddCompany');
    $this->post('/edit_a_company/{id}', 'AdminController:editCompany')->setName('admin.editCompany');
    $this->get('/view_company/{id}', 'AdminController:viewCompany')->setName('admin.viewCompany');

    /** Packages */    
    $this->get('/packages', 'AdminController:getAllPackages')->setName('admin.packages');
    $this->get('/view_package/{id}', 'AdminController:viewPackage')->setName('admin.viewPackage');

    /** Package Categories */
    $this->get('/package_categories', 'AdminController:getAllPackageCategories')->setName('admin.packageCategories');
    $this->get('/add_a_package_category', 'AdminController:addPackageCategory')->setName('admin.addPackageCategory');
    $this->post('/add_a_package_category', 'AdminController:postAddPackageCategory')->setName('admin.postAddPackageCategory');
    $this->get('/view_package_category/{id}', 'AdminController:viewPackageCategory')->setName('admin.viewPackageCategory');
    $this->post('/edit_package_category/{id}', 'AdminController:postEditPackageCategory')->setName('admin.postEditPackageCategory');
    $this->post('/delete_package_category', 'AdminController:deletePackageCategory')->setName('admin.deletePackageCategory');


    /** Bookings */ 
    $this->get('/completed_bookings', 'AdminController:getCompletedBookings')->setName('admin.completedBookings');
    $this->get('/upcoming_bookings', 'AdminController:getUpcomingBookings')->setName('admin.upcomingBookings');
    $this->get('/view_booking/{id}', 'AdminController:viewBooking')->setName('admin.viewBooking');
    $this->get('/edit_a_booking/{id}', 'AdminController:EditBooking')->setName('admin.editBooking');

    /** Booking Types */
    $this->get('/booking_types', 'AdminController:getAllBookingTypes')->setName('admin.bookingTypes');      
    $this->get('/add_a_booking_type', 'AdminController:addBookingType')->setName('admin.addBookingType');   
    $this->post('/add_a_booking_type', 'AdminController:postAddBookingType')->setName('admin.postAddBookingType');    
    $this->get('/view_booking_type/{id}', 'AdminController:viewBookingType')->setName('admin.viewBookingType');
    $this->get('/delete_booking_type/{id}', 'AdminController:deleteBookingType')->setName('admin.deleteBookingType');
    $this->post('/edit_booking_type/{id}', 'AdminController:editBookingType')->setName('admin.editBookingType');

    /** Wallets */
    $this->get('/wallets', 'AdminController:allWallets')->setName('admin.wallets');

    /** Upload CSV file */
    $this->get('/upload_csv', 'AdminController:uploadCSV')->setName('admin.uploadCSV');
});

/** Agent Routes
 *
 * App Routes used by the Agent
 */
$app->group('/agent', function() use ($app){

    /** Reroute to dashboard */
    $this->get('/', function($resquest, $response, array $args)
    {
        return $response->withRedirect($this->router->pathFor('agent.dash'));
    });

    /** Dashboard */
    $this->get('/dash', 'AgentController:dash')->setName('agent.dash');

    /** Profile */
    $this->get('/profile', 'AgentController:userProfile')->setName('agent.profile');

    /** Packages */
    $this->get('/my_packages', 'AgentController:getAllPackages')->setName('agent.packages');
    $this->get('/add_a_package', 'AgentController:addPackage')->setName('agent.addPackage');
    $this->post('/add_a_package', 'AgentController:postAddPackage')->setName('agent.postAddPackage');
    $this->get('/view_package/{id}', 'AgentController:viewPackage')->setName('agent.viewPackage');
    $this->post('/edit_package/{id}', 'AgentController:editPackage')->setName('agent.editPackage');
    $this->get('/delete_package/{id}', 'AgentController:deletePackage')->setName('agent.deletePackage');

    /** Bookings */
    $this->get('/completed_bookings', 'AgentController:getCompletedBookings')->setName('agent.completedBookings');
    $this->get('/upcoming_bookings', 'AgentController:getUpcomingBookings')->setName('agent.upcomingBookings');
    $this->get('/add_a_booking', 'AgentController:addBooking')->setName('agent.addBooking');
    $this->post('/add_a_booking', 'AgentController:postAddBooking')->setName('agent.postAddBooking');
    $this->get('/view_booking/{id}', 'AgentController:viewBooking')->setName('agent.viewBooking');
    $this->post('/edit_a_booking/{id}', 'AgentController:editBooking')->setName('agent.editBooking');
    $this->get('/delete_booking/{id}', 'AgentController:deleteBooking')->setName('agent.deleteBooking');

    /** Activities */
    $this->get('/all_activities', 'AgentController:getAllActivities')->setName('agent.activities');
    $this->get('/add_an_activity', 'AgentController:addActivity')->setName('agent.addActivity');

    /** Wallet */
    $this->get('/wallet', 'AgentController:getAgentWallet')->setName('agent.wallet');

    /** Transactions */
    $this->get('/transactions', 'AgentController:getAgentTransactions')->setName('agent.transactions');
});

