<?php

namespace App\Controllers;

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

class ApiController extends Controller
{
    /** User Methods */

    public function getAllUsers($request, $response)
    {       

        /** Get Data from users table  */
        $users = User::where('users.disabled', 0)
            ->leftJoin('countries', 'users.country', 'countries.iso')
            ->select(
                'users.id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.phone_number',
                'users.email_address',
                'users.user_type',
                'countries.nicename',
                'countries.phonecode'
            )
            ->get();
        
        return $response->withJson($users);
    }

    public function getAllCustomers($request, $response)
    {
        /** Get Customers from the Database */
        $users = User::where('users.disabled', 0)
            ->where('users.user_type', 3)
            ->leftJoin('countries', 'users.country', 'countries.iso')
            ->select(
                'users.id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.phone_number as phone',   
                'users.email_address',             
                'users.city',
                'countries.nicename as country',
                'countries.phonecode'                
            )
            ->get();

        return $response->withJson($users);
    }  
    
    public function getAllAgents($request, $response)
    {
        /** Get Customers from the Database */
        $users = User::where('users.disabled', 0)
            ->where('users.user_type', 2)
            ->leftJoin('countries', 'users.country', 'countries.iso')
            ->select(
                'users.id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.phone_number as phone',   
                'users.email_address',             
                'users.city',
                'countries.nicename as country',
                'countries.phonecode'                
            )
            ->get();

        return $response->withJson($users);
    }

    public function getAllAdmin($request, $response)
    {
        /** Get Customers from the Database */
        $users = User::where('users.disabled', 0)
            ->where('users.user_type', 1)
            ->leftJoin('countries', 'users.country', 'countries.iso')
            ->select(
                'users.id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.phone_number as phone',   
                'users.email_address',             
                'users.city',
                'countries.nicename as country',
                'countries.phonecode'                
            )
            ->get();

        return $response->withJson($users);
    }  
    
    public function getAllDeleted($request, $response)
    {       

        /** Get Data from users table  */
        $users = User::where('users.disabled', 1)
            ->leftJoin('countries', 'users.country', 'countries.iso')
            ->select(
                'users.id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.phone_number',
                'users.email_address',
                'users.user_type',
                'countries.nicename',
                'countries.phonecode'
            )
            ->get(); 
        
        return $response->withJson($users);
    }
    
    public function getUserProfile($request, $response, array $args)
    {
        /** Pass Session data into an array */
        $session = [
            'agentId'  => $_SESSION['agent_id'], 
            'userId'   => $_SESSION['user_id'],
            'adminId'  => $_SESSION['admin_id'],
            'walletId' => $_SESSION['wallet_id'],
        ];

        /** Check if User is an Admin */
        if(!empty($session['adminId']))
        {
            $user = User::where('users.id', $session['userId'])
                ->leftjoin('admins', 'users.id', 'admins.user_id')                
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
                    'users.verified',
                    'users.disabled',
                    'users.is_old',
                    'users.user_type',
                    'countries.nicename as country',
                    'countries.phonecode',
                    'admins.role'
                )
                ->first();
        }else{
            $user = User::where('users.id', $session['userId'])
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
                    'users.verified',
                    'users.disabled',
                    'users.is_old',
                    'users.user_type',
                    'countries.nicename as country',
                    'countries.phonecode',
                    'agents.rating',
                    'agents.company_id',
                    'agents.agent_type',
                    'agents.verified',
                    'wallets.balance',
                    'wallets.bonus_balance'
                )
                ->first();
        }        

        return $response->withJson($user);
    }

    public function postAddUser($request, $response)
    {
        /** Get POST data  */
        $post = $request->getParsedBody();

        /** Check if Customer exists */
        $check_phone_number = User::where('phone_number',$post['phone_number'])->first();
        $check_email_address = User::where('email_address',$post['email_address'])->first();

        /** If Email exists halt progress and return error */
        if(!empty($check_phone_number) || !empty($check_email_address))
        {
            $rs = [
                'success' => false,
                'error' => [
                    'err_no' => 400,
                    'err_message' => 'User Already in the System'
                ],
            ];
        }else{
            /** Get Uses Phone Code */
            $country = Country::where('iso', $post['country'])
                ->select('phonecode')
                ->first();

            $dob = $post['dob_year'].'-'.$post['dob_month'].'-'.$post['dob_day'];

            /** Create new User*/
            $user = User::create(
                [
                    'first_name'        => $post['first_name'],
                    'middle_name'       => $post['middle_name'],
                    'last_name'         => $post['last_name'],
                    'phone_number'      => ltrim($post['phone_number'], '0'),
                    'phone_number_code' => $country['phonecode'],
                    'email_address'     => $post['email_address'],
                    'dob'               => $post['dob'],
                    'gender'            => $post['gender'],
                    'profile_picture'   => $post['profile_picture'],
                    'address'           => $post['address'],
                    'city'              => $post['city'],
                    'country'           => $post['country'],
                    'dob'               => $dob,
                    'user_type'         => 3,
                ]
            );

            /** Create new Wallet */
            $wallet = Wallet::create();

            /** Create new Customer */
            $customer = Customer::create(
                [
                    'user_id'   => $user['id'],
                    'wallet_id' => $wallet['id'],
                ]
            );

            /** Generate New Login */
            $password = Login::generatePassword();
            $login = Login::create(
                [
                    'user_id'       => $user['id'],
                    'email_address' => $user['email_address'],
                    'phone_number'  => $user['phone_number'],
                    'password'      => password_hash($password, PASSWORD_DEFAULT)
                ]
            );

            $data = [
                'user_id'     => $user['id'],
                'wallet_id'   => $wallet['id'],
                'customer_id' => $customer['id'],
                'password'    => $password,
            ];
            
        }  
        
        return $response->withJson($data, 200);
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

    public function restoreUser($request, $response)
    { 
        /** Get Package ID as a POST */
        $post = $request->getattributes('id');

        /** Find and update package information */
        $user = User::find($post['id']);
        $user->disabled = 0;
        $user->save();

        return $response->withRedirect($this->router->pathFor('admin.users'));
    }

    /** Company Methods */

    public function getAllCompanies($request, $response)
    {       

        /** Get Data from users table  */
        $companies = Company::where('companies.disabled', 0)
            ->leftJoin('countries', 'companies.country', 'countries.iso')
            ->select(
                'companies.id',
                'companies.name',
                'companies.city',                
                'companies.email_address',
                'companies.phone_number',
                'companies.logo_url',
                'companies.website_url',
                'countries.nicename',
                'countries.phonecode'
            )
            ->get(); 

            dump($companies); die;
        
        return $response->withJson($companies);
    } 
    
    public function postAddCompany($request, $response)
    {
        /** Get data as a POST */
        $post = $request->getParsedBody();
        dump($post);

        /** Get the phone code */
        $phone = Country::where('iso', $post['country'])->select('phonecode')->first();

        dump($phone[array('phonecode')]); 

        /** Create New Company */
        $company = Company::create([
            'name'              => $post['name'],
            'description'       => $post['description'],
            'address'           => $post['address'],
            'city'              => $post['city'],
            'country'           => $post['country'],
            'email_address'     => $post['email_address'],
            'phone_number'      => ltrim($post['phone_number'], '0'),
            'phone_number_code' => $phone['phonecode'],
            'logo_url'          => $post['logo_url'],
            'website_url'      => $post['website_url'],
            'disabled'          => 0,
        ]);

        dump($company); die;

        return $response->withJson($company, 200);
    }

    public function deleteCompany($request, $response)
    { 
        /** Get Package ID as a POST */
        $post = $request->getattributes('id');

        /** Find and update package information */
        $company = Company::find($post['id']);
        $company->disabled = 1;
        $company->save();

        return $response->withRedirect($this->router->pathFor('admin.companies'));
    }
    
    /** Transaction Methods */
    public function getTransactions($request, $response, array $args)
    {
        /** Get all Transactions */
        $transaction = Transaction::leftJoin('transaction_types', 'transactions.tx_type', 'transaction_types.id')
            ->select(
                'id',
                'ref_no',
                'wallet_id',
                'user_id',
                'amount',
                'currency',
                'tx_type' 
            )
            ->get();
        
        /** Return data with status code */
        return $response->withJson($transaction);
    }  
    
    /** Wallet methods */
    /** Get all Wallets */
    public function getWallets($request, $response, array $args)
    {
        /** Get User Type */
        //$user_type = $response->getParsedBody();       

        $wallets = Wallet::where('wallets.disabled', 0)
                ->leftJoin('customers', 'wallets.id', 'customers.wallet_id')
                ->leftJoin('users', 'customers.user_id', 'users.id')
                ->select(
                    'customers.id',
                    'customers.user_id',
                    'customers.wallet_id',
                    'wallets.balance',
                    'wallets.bonus_balance',
                    'wallets.activated',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'           
                )
                ->get();

        return $response->withJson($wallets);
    } 

    /** Get single user wallet */
    public function getSingleWallet($request, $response, array $args)
    {
        /** Get Agent Id / User Id as a POST */
        $post = $request->getAttribute('id');

        /** Get Users Wallet Id */
        /** Check whether User Id belongs to an agent or to a User */
        $walletId = Customer::where('customers.user_id', $post)
                ->select('wallet_id')
                ->first(); // If User Id belongs to a Customer
        dump($walletId);
       
        if(empty($walletId))
        {
            $walletId = Agent::where('agents.user_id', $post)
                ->select('wallet_id')
                ->first(); // If User Id belongs to an Agent
        }
        dump($walletId);

       /** Get Wallet Info */
       $wallet = Wallet::where('wallets.id', $walletId)
                ->select(
                    'wallets.id',
                    'wallets.balance',
                    'wallets.bonus_balance'
                )
                ->get();

        dump($wallet); die;

        $transactions = Transaction::where('transactions.wallet_id', $walletId)
                ->join('transaction_types', 'transactions.transaction_type', 'transaction_types.id')
                ->select(
                    'transactions.id',
                    'transactions.ref_no',
                    'transactions.amount',
                    'transactions.currency',
                    'transaction_types.type'
                )
                ->limit(10)
                ->get();

        dump($transactions); die;

        $data = [$wallet,$transactions];

        return $response->withJson($data);
    }

    /** Booking Methods */
    public function getAgentBookings($request, $response, array $args)
    {
        /**Get Agent Id as a POST */
        $post = $request->getAttributes('id');

        $bookings = Booking::where('bookings.disabled', 0)
                ->where('bookings.agent_id', $post['id'])
                ->leftJoin('booking_types', 'bookings.booking_type', 'booking_types.id') 
                ->leftJoin('customers','bookings.customer_id', 'customers.id')       
                ->leftJoin('users', 'customers.user_id', 'users.id')              
                ->leftJoin('agents', 'bookings.agent_id', 'agents.id')
                ->select(
                    'bookings.id',
                    'bookings.customer_id',
                    'bookings.agent_id',
                    'bookings.booking_type',
                    'bookings.booking_date AS date',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'
                )
                ->get();

        return $response->withJson($bookings);
    }  

    public function getCompletedBookings($request, $response, array $args)
    {
        /** Get Agent Id as a POST */
        $post = $request->getAttributes('id');

        /** Current Date */
        $date = date('Y-m-d');

        /** Get Completed Bookings  */
        $bookings = Booking::where('booking_date', '<=', $date)->get();

        return $response->withJson($bookings);
    }

    public function getUncompletedBookings($request, $response, array $args)
    {
        /** Get Agent Id as a POST */
        $post = $request->getAttributes('id');

        /** Current Date */
        $date = date('Y-m-d');

        /** Get Uncompleted Bookings  */
        $bookings = Booking::where('booking_date', '=>', $date)->get();

        return $response->withJson($bookings);
    }
    
    /** Packages methods */
    public function getAllPackages($request, $response)
    {
        /** Get all Packages */
        $packages = Package::where('packages.disabled', 0)
                ->leftJoin('countries', 'packages.location_country', 'countries.iso')
                ->leftJoin('package_categories', 'packages.category_id', 'package_categories.id')
                ->leftJoin('agents', 'packages.agent_id', 'agents.id')
                ->leftJoin('users', 'agents.user_id', 'users.id')
                ->select(
                    'packages.id',
                    'packages.name',
                    'package_categories.name as package_category',
                    'packages.valid_from',
                    'packages.valid_to',
                    'packages.location_city',
                    'packages.price',
                    'countries.nicename as country',
                    'agents.id as agentId',
                    'users.id as userId',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'
                )
                ->get();

                dump($packages); die;

            return $response->withJson($bookings);
    }

    public function getAgentPackages($request, $response, array $args)
    {
        /** Get Agent Id as a POST */
        $post = $request->getAttributes('id');

        $packages = Package::where('packages.agent_id', $agentId)
                ->leftJoin('countries', 'packages.location_country', 'countries.iso')
                ->leftJoin('package_categories', 'packages.category_id', 'package_categories.id')
                ->select(
                    'packages.id',
                    'packages.title',
                    'package_categories.name as package_category',
                    'packages.valid_from',
                    'packages.valid_to',
                    'packages.location_city',
                    'packages.price',
                    'countries.nicename as country'
                )
                ->get();
        
        return $response->withJson($packages);
    }  

    public function getActivePackages($request, $response, array $args)
    {
        /** Get Agent Id as a POST */
        $post = $request->getAttributes('id');

        /** Current Date */
        $date = date('Y-m-d');

        /** Get the active Packages */
        $packages = Package::where('valid_to', '<=', $date)->get();

        return $response->withJson($packages);

    }
    
    public function getExpiredPackages($request, $response, array $args)
    {
        /** Get Agent Id as a POST */
        $post = $request->getAttributes('id');

        /** Current Date */
        $date = date('Y-m-d');

        /** Get the expired packages */
        $packages = Package::where('valid_to', '=>', $date)->get();

        return $response->withJson($packages);

    }

    /** Get Session Info */
    public function getSessionData($request, $response)
    {
        /** Get Session Variables */
        $data = [
            'agentId'  => $_SESSION['agent_id'], 
            'userId'   => $_SESSION['user_id'],
            'adminId'  => $_SESSION['admin_id'],
            'walletId' => $_SESSION['wallet_id'],
        ];

        return $response->withJson($data);
    }

    public function uploadCSV($request, $response)
    {
        $directory = $this->get('upload_directory');

        $uploadedFiles = $request->getUploadedFiles();

        /** Handler for single Upload */
        $uploadedFile = $uploadedFiles['csv-upload'];
        if($uploadedFile->getError() === UPLOAD_ERROR_OK)
        {
            $filename = moveUploadedFile($directory, $uploadedFile);
            $response->write('uploaded'. $filename . '<br>');
        }


        /** Get File upload as a Post */
        $upload = $request->getParsedBody();

        dump($upload); die;
    }

}