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

class AgentController extends Controller
{    
    public function dash($request, $response)
    { 
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        /** Pass session variables into the view */
        $data = [
            'agentId'  => $_SESSION['agent_id'], 
            'userId'   => $_SESSION['user_id'],
            'adminId'  => $_SESSION['admin_id'],
            'walletId' => $_SESSION['wallet_id'],
        ]; 

        return $this->view->render($response,
            'agents/dash.twig',
            [
                'title'   => 'Dashboard',
                'type'    => '',
                'name'    => 'home',
                'nav'     => 'home',
                'user'    => $data
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
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        $packages = Package::where('packages.disabled', 0)
                ->where('packages.agent_id', $data['agentId'])
                ->leftJoin('countries', 'packages.location_country', 'countries.iso')
                ->leftJoin('package_categories', 'packages.category_id', 'package_categories.id')
                ->select(
                    'packages.id',
                    'packages.name',
                    'package_categories.name as package_category',
                    'packages.valid_from',
                    'packages.valid_to',
                    'packages.location_city',
                    'packages.price',
                    'countries.nicename as country'
                )
                ->get();

        return $this->view->render($response,
            'agents/packages_table2.twig',
            [
                'title'   => 'Packages',
                'type'    => 'packages-table',
                'name'    => 'packages',
                'nav'     => 'packages',
                'packages'    => $packages
            ]
        );
    }

    public function addPackage($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        /** Get Countries from the Database */
        $countries = Country::select('iso','nicename')->orderBy('nicename', 'asc')->get();
        
        /** Get Package Categories from Database */
        $package_categories = Package_category::select('id', 'name')->get();

        return $this->view->render($response,
            'agents/add_package2.twig',
            [
                'title'       => 'Packages',
                'type'        => 'packages-form',
                'name'        => 'packages',
                'nav'         => 'packages',
                'countries'   => $countries,
                'categories'  => $package_categories 
            ]
        );
    }

    public function postAddPackage($request, $response)
    {        
        /** Get Data as POST */
        $post = $request->getParsedBody();

        // Add check ro see whether Package Exits in the system 
        $old_package = Package::where('packages.name', $post['name'])->select('id','name')->first();
        if(!empty($old_package))
        {
            /** Redirect to Packages Page */
            return $response->withJson($rs)->withRedirect($this->router->pathFor('agent.packages'));
        }
        
        /** Create New Package in Database */
        $new_package = Package::create(
            [
                'name'             => $post['name'],
                'agent_id'         => $_SESSION['agent_id'],
                'description'      => $post['description'],
                'valid_from'       => $post['valid_from'],
                'valid_to'         => $post['valid_to'],
                'display_image'    => $post['display_image'],
                'images'           => $post['images'],
                'category_id'      => $post['category'],
                'location_country' => $post['country'],
                'location_city'    => $post['city'],
                'price'            => $post['price'],
            ]
        );

        /** Redirect to Packages Page */
        return $response->withJson($rs)->withRedirect($this->router->pathFor('agent.packages'));     
    }

    public function viewPackage($request, $response, $id)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
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
                    'countries.iso as iso',
                    'countries.nicename as country',
                    'agents.id as agentId',
                    'users.id as userId',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'
                )
                ->first();

        $package_cats = Package_category::select('id', 'name')->get();
        $countries = Country::select('iso','nicename')->orderBy('nicename', 'asc')->get();
                
        return $this->view->render($response,
                'agents/view_package.twig',
                [
                    'title'      => 'View Package',
                    'type'       => 'packages-form',
                    'name'       => 'packages',
                    'nav'        => 'packages',
                    'package'    => $package,
                    'categories' => $package_cats,
                    'countries'  => $countries
                ]
        );
    }

    public function editPackage($request, $response, $id)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        /** Get Package ID as a POST */
        $post = $request->getParsedBody();

        /** Find package */   
        $package = Package::where('packages.id', $id)
            ->update(
                [
                    'name' => $post['name'],
                    'agent_id' => $_SESSION['agent_id'],
                    'description' => $post['description'],
                    'valid_from' => $post['valid_from'],
                    'valid_to' => $post['valid_to'],
                    'display_image' => $post['display_image'],
                    'images' => $post['images'],
                    'category_id' => $post['category'],
                    'location_country' => $post['country'],
                    'location_city' => $post['city'],
                    'price' => $post['price'],
                ]
            );
        
       /** Return to Packages Page */
       return $response->withRedirect($this->router->pathFor('agent.packages'));

    }

    public function deletePackage($request, $response)
    { 
        /** Get Package ID as a POST */
        $post = $request->getattributes('id');

        /** Find and update package information */
        $package = Package::find($post['id']);
        $package->disabled = 1;
        $package->save();

        return $response->withRedirect($this->router->pathFor('agent.packages'));
    }

    /** Bookings */
    /** Get Upcoming Bookings */
    public function getUpcomingBookings($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        /** Current Date */
        $now = date('Y-m-d');
        
        /** Get the bookings by date */
        $bookings = Booking::where('bookings.disabled', 0)
                ->where('agent_id', $_SESSION['agent_id'])
                ->where('booking_date', '>=', $now)
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
            'agents/bookings_table.twig',
            [
                'title'    => 'Upcoming Bookings',
                'type'     => 'upcomingBooking-table',
                'name'     => 'Bookings',
                'nav'      => 'bookings',
                'bookings' => $bookings
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
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        /** Current Date */
        $now = date('Y-m-d');
        
        /** Get the bookings by date */
        $bookings = Booking::where('bookings.disabled', 0)
                ->where('agent_id', $_SESSION['agent_id'])
                ->where('booking_date', '<=', $now)
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
            'agents/bookings_table.twig',
            [
                'title'   => 'Completed Bookings',
                'type'    => 'completedBooking-table',
                'name'    => 'Bookings',
                'nav'     => 'bookings',
                'bookings' => $bookings
            ]
        );
    }

    /** Add a Booking */
    public function addBooking($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        /** Get Booking Package from Database */
        $packages = Package::select('id','name')->get();

        /** Get Countries from the Database */
        $countries = Country::select('iso', 'nicename')->get();

        /** Get Customer details from the database */
        $customers = Customer::leftJoin('users', 'customers.user_id', 'users.id')
                ->select(
                    'customers.id',
                    'users.first_name as firstName',
                    'users.middle_name as middleName',
                    'users.last_name as lastName'
                )
                ->get();

        /** Get Booking Types from the database */
        $bookingTypes = Booking_type::select('id', 'title')->get();

        return $this->view->render($response,
            'agents/add_booking2.twig',
            [
                'title'        => 'Add a Booking',
                'type'         => 'booking-form',
                'name'         => 'Bookings',
                'nav'          => 'bookings',
                'packages'     => $packages,
                'countries'    => $countries,
                'customers'    => $customers,
                'bookingTypes' => $bookingTypes
            ]
        );
    }

    /** Add Booking to Database */
    public function postAddBooking($request, $response)
    {        
        /** Get Data as POST */
        $post = $request->getParsedBody();

        // TODO: add check for booking in the system

        /** Create New Package in Database */
        $new_booking = Booking::create(
            [
                'customer_id'         => $post['customer_id'],
                'agent_id'            => $_SESSION['agent_id'],
                'booking_type'        => $post['booking_type'],
                'booking_information' => json_encode($post['booking_info']),
                'booking_date'        => $post['date'],
            ]
        );

        /** Redirect to Packages Page */
        return $response->withJson($rs)->withRedirect($this->router->pathFor('agent.bookings'));
    }

    public function viewBooking($request, $response, $id)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
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
                'agents/view_booking.twig',
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
        /** Get Update Data as a POST */
        $post = $request->getParsedBody();
        //dump($post, $id); die;

        $booking = Booking::where('bookings.id', $id)
                ->update(
                    [
                        'customer_id'         => null,
                        'agent_id'            => $_SESSION['agent_id'],
                        'booking_type'        => $post['category'],
                        'booking_information' => $post['booking_info'],
                        'custom_request_id'   => $post['custom_request_id'],
                        'booking_date'        => $post['date'],
                    ]
                );

        /** Return to Bookings Page */
        return $response->withRedirect($this->router->pathFor('agent.upcomingBookings'));
    }

    /** Get Agent Wallet Data */
    public function getAgentWallet($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        /** Pass session variables into the view */
        $data = [
            'agentId'  => $_SESSION['agent_id'], 
            'userId'   => $_SESSION['user_id'],
            'adminId'  => $_SESSION['admin_id'],
        ];

        /** Get Wallet Id */
        $walletId = Agent::where('wallets.id', $data['agentId'])
                ->select('wallet_id')
                ->first();
        
       /** Get Wallet info */
        
        return $this->view->render($response,
            'agents/wallet.twig',
            [
                'title' => 'wallet',
                'type'  => 'agentWallet-table',
                'name'  => 'wallets',
                'nav'   => 'wallets',
                'wallet'    => $wallet
            ]
        );
    }

    public function userProfile($request, $response, array $args)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
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
            'agents/profile.twig',
            [
                'title'     => 'My Profile',
                'type'      => 'profile-table',                
                'nav'       => 'profile',
                'user'      => $user
            ]
        );
    }

    public function getAgentTransactions($request, $response)
    {
        /** Check if User is logged in */
        if(empty($_SESSION))
        {
            return $response->withRedirect($this->router->pathFor('admin.login'));
        }elseif($_SESSION['user_type'] === 1){
            return $response->withRedirect($this->router->pathFor('admin.dash'));
        }

        /** Pass session variables into the view */
        $data = [
            'agentId'  => $_SESSION['agent_id'], 
            'userId'   => $_SESSION['user_id'],
            'adminId'  => $_SESSION['admin_id'],
        ];

        /** Get Wallet Id */
        $wallet = Agent::where('agents.id', $data['agentId'])
                ->select('wallet_id')
                ->first();

        /** Get Transaction data  */
        $transactions = Transaction::where('transactions.disabled', 0)
                    ->where('transactions.wallet_id', $wallet['wallet_id'])
                    ->leftJoin('transaction_types', 'transactions.transaction_type', 'transactions.id')
                    ->select(
                        'transactions.ref_no',
                        'transactions.amount',
                        'transactions.currency',
                        'transactions.wallet_id',
                        'transactions.created_at as date',
                        'transaction_types.name as transactionType'                        
                    )
                    ->get();

        $users = User::where('users.id', $data['userId'])
                ->leftJoin('countries', 'users.country', 'countries.iso')
                ->leftJoin('agents', 'users.id', 'agents.user_id')
                ->leftJoin('companies', 'agents.company_id', 'companies.id')
                ->leftJoin('wallets', 'agents.wallet_id', 'wallets.id')
                ->select(
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name',
                    'users.id',
                    'users.email_address',
                    'users.phone_number',
                    'users.address',
                    'users.city',
                    'agents.id as agent_id',
                    'agents.rating',
                    'agents.company_id',
                    'agents.agent_type',
                    'countries.nicename as country',
                    'countries.phonecode',
                    'companies.name',
                    'wallets.balance as walletBalance'
                )
                ->first();

        return $this->view->render($response,
            'agents/transactions.twig',
            [
                'title'          => 'My Transactions',
                'type'           => 'transactions-table',                
                'nav'            => 'transactions',
                'transactions'   => $transactions,
                'user'           => $users,
                'wallet_balance' => $wallet['balance']
            ]
        );
    }
}

    