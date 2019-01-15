<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Agent;
use App\Models\Booking;
use App\Models\Booking_type;
use App\Models\Country;
use App\Models\Company;
use App\Models\Custom_request;
use App\Models\Customer;
use App\Models\Login;
use App\Models\Package;
use App\Models\Package_category;
use App\Models\Payment_provider;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\Transaction_type;
use App\Models\User;
use App\Models\Wallet;
use \Firebase\JWT\JWT;

class AdminController extends Controller
{
    public function dash($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        return $this->view->render($response,
            'admin/dash.twig',
            [
                'title'   => 'Dashboard',
                'type'    => '',
                'name'    => 'home',
                'nav'     => 'home'
            ]
        );
    }

    /** User functions */
    /** Get all the Users */
    public function allUsers($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        } 
        
        return $this->view->render($response,
            'admin/users_table.twig',
            [
                'title' => 'All users',
                'type'  => 'users-table',                
                'nav'   => 'users',
                'users' => $users
            ]
        );
    }

    public function allAgents($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }
        
        return $this->view->render($response,
            'admin/users_table.twig',
            [
                'title' => 'Agents',
                'type'  => 'agents-table',                
                'nav'   => 'users',
                'users' => $users
            ]
        );
    }

    public function allAdmin($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }
        
        return $this->view->render($response,
            'admin/users_table.twig',
            [
                'title' => 'Administrators',
                'type'  => 'admin-table',                
                'nav'   => 'users',
                'users' => $users
            ]
        );
    }

    public function allCustomers($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }
        
        return $this->view->render($response,
            'admin/users_table.twig',
            [
                'title' => 'Customers',
                'type'  => 'customers-table',                
                'nav'   => 'users',
                'users' => $users
            ]
        );
    }

    public function allDeleted($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }
        
        return $this->view->render($response,
            'admin/users_table.twig',
            [
                'title' => 'Deleted Users',
                'type'  => 'deleted-users',                
                'nav'   => 'users',
                'users' => $users
            ]
        );
    }

    public function viewUser($request, $response, $id)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Customers from the Database */
        $user = User::where('users.id', $id)
            ->leftJoin('countries', 'users.country', 'countries.iso')
            ->leftJoin('customers', 'users.id', 'customers.user_id')
            ->leftJoin('agents', 'users.id', 'agents.user_id')
            ->leftJoin('admins', 'users.id', 'admins.user_id')
            ->select(
                'users.id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.phone_number as phone',   
                'users.email_address', 
                'users.address', 
                'users.user_type as userType', 
                'users.profile_picture',          
                'users.city',
                'users.dob',
                'admins.role',
                'countries.nicename as country',
                'countries.phonecode',
                'agents.id as agentId',
                'agents.agent_type as agentType',
                'customers.id as customerId'               
            )
            ->first(); 

        /** Get Wallet Balance from the Wallets Table */
        /** Chceck if User is a customer and get wallet info accordingly */
        if($user['customerId'] !== null )
        {
            $wallet = Customer::where('customers.user_id', $user['customerId'])
                    ->leftJoin('wallets', 'customers.wallet_id', 'wallets.id')
                    ->leftJoin('companies', 'customers.company_id', 'companies.id')
                    ->select(
                        'wallets.id', 
                        'wallets.balance', 
                        'wallets.bonus_balance',
                        'wallets.disabled',
                        'companies.id as companyId',
                        'companies.name as companyName'
                    )
                    ->first();
        }else if($user['agentId'] !== null)
        {
            $wallet = Agent::where('agents.user_id', $user['agentId'])
                    ->leftJoin('wallets', 'agents.wallet_id', 'wallets.id')                    
                    ->leftJoin('companies', 'agents.company_id', 'companies.id')
                    ->select(
                        'wallets.id', 
                        'wallets.balance', 
                        'wallets.bonus_balance',
                        'wallets.disabled',
                        'companies.id as companyId',
                        'companies.name as companyName'
                    )
                    ->first();
        }else{
            dump('There is no wallet'); die;
        }
        
        return $this->view->render($response,
            'admin/view_user.twig',
            [
                'title'  => 'View User',
                'type'   => 'users-table',                
                'nav'    => 'users',
                'user'   => $user,
                'wallet' => $wallet
            ]
        );

    }

    public function getAddUser($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        $countries = Country::select('iso','nicename')->orderBy('nicename', 'desc')->get(); 
        $companies = Company::select('id','name')->get();

        return $this->view->render($response,
            'admin/add_user2.twig',
            [
                'title'        => 'Add a User',
                'type'         => 'users-form',                
                'nav'          => 'users',
                'countries'    => $countries,
                'companies'    => $companies,
            ]
        );
    }

    public function postAddUser()
    {
        /** Get User Data as a POST */
        $post = $request->getParsedData();

        dump($post); die;
    }
    
    public function editUser($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Package ID as a POST */
        $post = $request->getattributes('id');

        /** Find package */   
        $package = Package::find($post['id']);
        
        /** Pass Package info to the view */
        return $this->view->render($response,
            'agents/edit_package.twig',
            [
                'title'   => 'Edit Package',
                'type'    => '',
                'name'    => 'packages',
                'nav'     => 'packages',
                'package'    => $package
            ]
        );

    }

    public function deleteUser($request, $response)
    { 
        /** Get Package ID as a POST */
        $post = $request->getattributes('id');

        /** Find and update package information */
        $user = User::find($post['id']);
        $user->disabled = 1;
        $user->save();

        return $response->withRedirect($this->router->pathFor('admin.users'));
    }

    public function uploadCSV($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        return $this->view->render($response,
            'admin/upload_csv.twig',
            [
                'title'     => 'Upload Users CSV',
                'type'      => 'csv-form',                
                'nav'       => 'users'
            ]
        );
    }

    
    public function userProfile($request, $response, array $args)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }
        
        /** Pass session variables into the view */
        $session = [
            'agentId'  => $_SESSION['agent_id'], 
            'userId'   => $_SESSION['user_id'],
            'adminId'  => $_SESSION['admin_id'],
            'walletId' => $_SESSION['wallet_id'],
        ]; 

        /** Get User Id as a POST */
        $id = $request->getAttribute('id');

        /** Get User Information from the database */
        $user = User::where('users.id', $session['userId'])
                ->leftjoin('admins', 'users.id', 'admins.user_id') 
                ->leftjoin('agents', 'users.id', 'agents.user_id')
                ->leftjoin('wallets', 'agents.wallet_id', 'wallets.id')               
                ->leftjoin('countries', 'users.country', 'countries.iso')
                ->select(
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name',
                    'users.phone_number',
                    'users.email_address',
                    'users.dob',
                    'users.gender',
                    'users.profile_picture',
                    'users.address',
                    'users.city',
                    'users.user_type',
                    'admins.role',
                    'countries.nicename as country',
                    'countries.phonecode',
                    'agents.rating',
                    'agents.company_id',
                    'agents.agent_type',
                    'wallets.balance',
                    'wallets.bonus_balance'
                )
                ->first();   
       
        return $this->view->render($response,
            'admin/profile.twig',
            [
                'title'     => 'My Profile',
                'type'      => 'profile-table',                
                'nav'       => 'profile',
                'user'      => $user
            ]
        );
    }

    /** Packages */
    public function getAllPackages($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        return $this->view->render($response,
            'admin/packages_table2.twig',
            [
                'title'    => 'Packages',
                'type'     => 'packages-table',
                'name'     => 'packages',
                'nav'      => 'packages',
                'packages' => $packages
            ]
        );
    }

    public function viewPackage($request, $response, $id)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Category data from the Database */
        $package = Package::where('packages.id', $id)
                ->leftJoin('countries', 'packages.location_country', 'countries.iso')
                ->leftJoin('package_categories', 'packages.category_id', 'package_categories.id')
                ->leftJoin('agents', 'packages.agent_id', 'agents.id')
                ->leftJoin('users', 'agents.user_id', 'users.id')
                ->select(
                    'packages.id',
                    'packages.name',
                    'packages.description',
                    'packages.images',
                    'packages.display_image',
                    'package_categories.name as categoryName',
                    'package_categories.id as categoryId',
                    'packages.valid_from',
                    'packages.valid_to',
                    'packages.location_city',
                    'packages.price',
                    'packages.agent_id',
                    'countries.nicename as country',
                    'agents.id as agentId',
                    'users.id as userId',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'
                )
                ->first();

        $package_cats = Package_category::select('id', 'name')->get();
                
        return $this->view->render($response,
                'admin/view_package.twig',
                [
                    'title'      => 'View Package',
                    'type'       => 'packages-form',
                    'name'       => 'packages',
                    'nav'        => 'packages',
                    'package'    => $package
                ]
        );
    }

    /** Package Categories */
    public function getAllPackageCategories($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        $package_cats = Package_category::where('package_categories.disabled', 0)
                ->leftJoin('users', 'package_categories.user_id', 'users.id')
                ->select(
                    'package_categories.id',
                    'package_categories.name',
                    'package_categories.description',
                    'users.first_name',                   
                    'users.middle_name',
                    'users.last_name'
                )
                ->get();

        return $this->view->render($response,
            'admin/package_cat_table2.twig',
            [
                'title'        => 'Package Categories',
                'type'         => 'packages-category-table',
                'name'         => 'packages',
                'nav'          => 'packages',
                'package_cats' => $package_cats
            ]
        );
    }

    public function addPackageCategory($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }              

        return $this->view->render($response,
            'admin/add_package_cat2.twig',
            [
                'title'        => 'Add a Package Category',
                'type'         => 'packages-category-form',
                'name'         => 'packages',
                'nav'          => 'packages'
            ]
        );
    }

    public function postAddPackageCategory($request, $response)
    {
       /** Get Category Data as a POST */
        $post = $request->getParsedBody();       

        /** Create a New Package Category */
        $new_category = Package_category::Create(
            [
                'user_id'     => $_SESSION['user_id'],
                'name'        => $post['name'],
                'description' => $post['description'],
            ]
        );

        /** Redirect to Package Categories Page */
        return $response->withJson($rs)->withRedirect($this->router->pathFor('admin.packageCategories'));
    }

    public function viewPackageCategory($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Category id as a POST */
        $post = $request->getAttributes('id');

        /** Get Category data from the Database */
        $category = Package_category::where('package_categories.id', $post['id'])
                ->select(
                    'id',
                    'name',
                    'description',
                    'user_id'
                )
                ->first();
                
        return $this->view->render($response,
                'admin/view_package_cat.twig',
                [
                    'title'        => 'View Package Category',
                    'type'         => 'packages-category-form',
                    'name'         => 'packages',
                    'nav'          => 'packages',
                    'package_cat' => $category
                ]
        );
    }
    
    public function postEditPackageCategory($request, $response, $id)
    {
        /** Get data to be updated as a POST */
        $post = $request->getParsedBody();
        
        /** Update the Package Category */
        $update = Package_category::where('package_categories.id', $id)
                ->update($post);

        /** Redirect to Package Categories Pages */
        return $response->withRedirect($this->router->pathFor('admin.packageCategories'));
    }

    public function deletePackageCategory($request, $response, $id){}

    /** Companies */
    public function allCompanies($request, $response, array $args)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        return $this->view->render($response, 
                'admin/companies_table.twig',
                [
                    'title'     => 'All Companies',
                    'type'      => 'companies-table',                
                    'nav'       => 'companies',
                    'companies' => $companies
                ]
        );
    }

    public function viewCompany($request, $response, $id)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Companies Data from the Database */
        $company = Company::where('companies.id', $id)
                ->leftJoin('countries', 'companies.country', 'countries.iso')
                ->leftJoin('agents', 'companies.id', 'agents.company_id')
                ->select(
                    'companies.id',
                    'companies.name',
                    'companies.description',
                    'companies.phone_number as phone',
                    'companies.email_address as email',
                    'companies.city',
                    'companies.country',
                    'companies.logo_url',
                    'companies.website_url',
                    'countries.nicename as country',
                    'countries.phonecode',
                    'countries.iso',
                    'agents.id as agentId'
                )
                ->first();
                
        $countries = Country::select('iso','nicename')->get();

        return $this->view->render($response, 
                'admin/view_company.twig',
                [
                    'title'     => 'View Company',
                    'type'      => 'companies-form',                
                    'nav'       => 'companies',
                    'company'   => $company,
                    'countries' => $countries
                ]
        );
    }
    
    public function addCompany($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        $countries = Country::select('iso','nicename')->orderBy('nicename', 'asc')->get();

        return $this->view->render($response,
            'admin/add_company2.twig',
            [
                'title'     => 'Add a Company',
                'type'      => 'company-form',                
                'nav'       => 'companies',
                'countries' => $countries,
            ]
        );
    }

    public function postAddCompany($request, $response)
    {
        /** Get Company Info as a POST */
        $post = $request->getParsedBody();

        /** Get Phone Number code */
        $phone = Country::where('countries.iso', $post['country'])->select('phonecode')->first();        

        /** Create a New Company */
        $company = Company::create(
            [
                'name'              => $post['name'],
                'description'       => $post['description'],
                'address'           => $post['address'],
                'city'              => $post['city'],
                'country'           => $post['country'],
                'email_address'     => $post['email'],
                'phone_number'      => ltrim($post['phone_number'], '0'),
                'phone_number_code' => $phone['phonecode'],
                'logo_url'          => $post['logo'],
                'website_url'       => $post['website'],
                'disabled'          => 0,
                ]
        );

        return $response->withRedirect('/admin/companies');
    }
    
    public function editCompany($request, $response, $id)
    {
        /** Get changes as a POST */
        $post = $request->getParsedBody();        

        /** Get Country Phone Code */
        $phonecode = Country::where('countries.iso', $post['country'])->select('phonecode')->first();

        /** Update the Company in the database */
        $company = Company::where('companies.id', $id)
                ->update(
            [
                'name'              => $post['name'],
                'description'       => $post['description'],
                'phone_number'      => ltrim($post['phone'], $phonecode.'-'),
                'email_address'     => $post['email'],
                'website_url'       => $post['website'],
                'logo_url'          => $post['logo'],
                'phone_number_code' => $phoneCode,
                'city'              => $post['city'],
                'country'           => $post['country']
            ]
        );

        return $response->withRedirect($this->router->pathFor('admin.companies'));
    }
    
    /** Wallets */
    public function allWallets($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Wallets from the database */        
        $wallets = Wallet::where('wallets.disabled', 0)
                ->leftJoin('customers', 'wallets.id', 'customers.wallet_id')
                ->leftJoin('agents', 'wallets.id', 'agents.wallet_id')
                ->select(
                    'wallets.id as id',
                    'wallets.balance',
                    'wallets.bonus_balance',
                    'wallets.active as status',
                    'wallets.created_at as dateCreated'
                )
                ->get();

        return $this->view->render($response,
            'admin/wallets_table.twig',
            [
                'title'     => 'Wallets',
                'type'      => 'company-form',                
                'nav'       => 'wallets',
                'wallets'   => $wallets,
            ]
        );
    } 
    
    /** Bookings */
    public function getUpcomingBookings($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Current Date */
        $now = date('Y-m-d');
        
        /** Get the bookings by date */
        $bookings = Booking::where('bookings.disabled', 0)
                ->where('bookings.booking_date', '>=', $now)
                ->leftJoin('booking_types', 'bookings.booking_type', 'booking_types.id')
                ->leftJoin('customers', 'bookings.customer_id', 'customers.id')
                ->leftJoin('users', 'customers.user_id', 'users.id')
                ->select(
                    'bookings.id',
                    'bookings.customer_id',
                    'bookings.agent_id',
                    'bookings.booking_date',
                    'bookings.booking_information',
                    'bookings.custom_request_id',
                    'booking_types.title as booking_type',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'
                )
                ->get();        

        return $this->view->render($response,
            'admin/bookings_table.twig',
            [
                'title'        => 'Upcoming Bookings',
                'type'         => 'upcomingBooking-table',
                'name'         => 'Bookings',
                'nav'          => 'bookings',
                'bookings'     => $bookings,
            ]
        );
    }

    /** Get Completed Bookings */
    public function getCompletedBookings($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Current Date */
        $now = date('Y-m-d'); 
        
        /** Get the bookings by date */
        $bookings = Booking::where('bookings.disabled', 0)
                ->whereDate('bookings.booking_date', '<', $now)
                ->leftJoin('booking_types', 'bookings.booking_type', 'booking_types.id')
                ->leftJoin('customers', 'bookings.customer_id', 'customers.id')
                ->leftJoin('users', 'customers.user_id', 'users.id')
                ->select(
                    'bookings.id',
                    'bookings.customer_id',
                    'bookings.agent_id',
                    'bookings.booking_date',
                    'bookings.booking_information',
                    'bookings.custom_request_id',
                    'booking_types.title as booking_type',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'
                )
                ->get();


        return $this->view->render($response,
            'admin/bookings_table.twig',
            [
                'title'   => 'Completed Bookings',
                'type'    => 'completedBooking-table',
                'name'    => 'Bookings',
                'nav'     => 'bookings',
                'bookings' => $bookings
            ]
        );
    }

    public function viewBooking($request, $response, $id)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Category data from the Database */
        $booking = Booking::where('bookings.id', $id)
                ->leftJoin('customers', 'bookings.customer_id', 'customers.id')
                ->leftJoin('users', 'bookings.customer_id', 'users.id')
                ->leftJoin('agents', 'bookings.agent_id', 'agents.id') 
                ->leftJoin('companies', 'agents.company_id', 'companies.id') 
                ->leftJoin('countries', 'companies.country', 'countries.iso')
                ->leftJoin('booking_types', 'bookings.booking_type', 'booking_types.id')              
                ->select(
                    'bookings.id',
                    'bookings.customer_id as customerId',
                    'bookings.agent_id as agentId',
                    'bookings.booking_date as date',
                    'bookings.custom_request_id',
                    'users.id as customerId',
                    'users.first_name as customerFirstName',
                    'users.middle_name as customerMiddleName',
                    'users.last_name as customerLastName',
                    'companies.name as companyName',
                    'companies.logo_url',
                    'companies.website_url',
                    'companies.email_address as email',
                    'companies.phone_number',
                    'booking_types.id as typeId',
                    'booking_types.title as typeName'
                )
                ->first(); 

        $bookingTypes = Booking_type::select('id', 'title')->get(); 

        return $this->view->render($response,
                'admin/view_booking.twig',
                [
                    'title'        => 'View Booking',
                    'type'         => 'booking-form',
                    'name'         => 'booking',
                    'nav'          => 'booking',
                    'booking'      => $booking ,
                    'types'        => $bookingTypes               ]
        );
    }    

    public function editBooking($request, $response, $id)
    {
        /** Get changes as a POST */
        $post = $request->getParsedBody();

        /** Get Country Phone Code */
        $phonecode = Country::where('countries.iso', $post['country'])->select('phonecode')->first();

        /** Update the Company in the database */
        $company = Company::where('companies.id', $id)
                ->update(
            [
                'name'              => $post['name'],
                'description'       => $post['description'],
                'phone_number'      => ltrim($post['phone'], $phonecode.'-'),
                'email_address'     => $post['email'],
                'website_url'       => $post['website'],
                'logo_url'          => $post['logo'],
                'phone_number_code' => $phoneCode,
                'city'              => $post['city'],
                'country'           => $post['country']
            ]
        );

        return $response->withRedirect($this->router->pathFor('admin.companies'));
    }

    /** Booking Types */
    public function getAllBookingTypes($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Booking Types from the database */
        $booking_types = Booking_type::leftJoin('users', 'booking_types.user_id', 'users.id')
                ->select(
                    'booking_types.id', 
                    'booking_types.title',
                    'booking_types.user_id',
                    'booking_types.description',
                    'users.first_name as firstName',
                    'users.middle_name as middleName',
                    'users.last_name as lastName'
                )->get();

        return $this->view->render($response,
            'admin/booking_types_table.twig',
            [
                'title'   => 'Booking Types',
                'type'    => 'BookingTypes-form',
                'name'    => 'Bookings',
                'nav'     => 'bookings',
                'types'   => $booking_types
            ]
        );
    }

    public function addBookingType($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        return $this->view->render($response,
            'admin/add_booking_type.twig',
            [
                'title'   => 'Add a Booking Type',
                'type'    => 'BookingTypes-form',
                'name'    => 'Bookings',
                'nav'     => 'bookings'
            ]
        );
    }

    public function postAddBookingType($request, $response)
    {
        /** Get New Booking as POST data */
        $post = $request->getParsedBody();

        /** Create New Booking Type */
        $new_booking_type = Booking_type::create(
            [
                'title'       => $post['name'],
                'description' => $post['description'],
                'user_id'     => $_SESSION['user_id']
            ]
        );

        return $response->withRedirect($this->router->pathFor('admin.bookingTypes'));
        
    }

    public function viewBookingType($request, $response, $id)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 2){
            return $response->withRedirect($this->router->pathFor('agent.dash'));
        }

        /** Get Booking Type data from the Database */
        $type = Booking_type::where('booking_types.id', $id)
                ->leftJoin('users', 'booking_types.user_id', 'users.id')
                ->select(
                    'booking_types.id',
                    'booking_types.title as name',
                    'booking_types.description',
                    'booking_types.created_at',
                    'users.first_name as userFirstName',
                    'users.middle_name as userMiddleName',
                    'users.last_name as userLastName',
                    'users.id as userId'
                    
                )  
                ->first();         

        return $this->view->render($response,
                'admin/view_booking_type.twig',
                [
                    'title'        => 'View Booking Types',
                    'type'         => 'booking-form',
                    'name'         => 'booking',
                    'nav'          => 'booking',
                    'type'         => $type 
                ]
        );
    }

    public function editBookingType($request, $response, $id)
    {
        /** Get changes as a POST */
        $post = $request->getParsedBody();

        /** Update the Booking Type in the database */
        $booking_type = Booking_type::where('booking_types.id', $id)
                ->update(
            [
                'title'             => $post['name'],
                'description'       => $post['description'],
            ]
        );

        return $response->withRedirect($this->router->pathFor('admin.bookingTypes'));
    }

    public function deleteBookingType($request, $response, $id)
    {
        //
    }

}

    