<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use App\Models\Country;
use App\Models\Login;
use App\Models\User;
use App\Models\Agent;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use \Firebase\JWT\JWT;


class AuthController extends Controller
{
    public function getAdminLogIn($request, $response)
    {
        /** Check if the User is logged in */
        if(!empty($_SESSION))
        {
            if($_SESSION['user_type'] === 1)
            {
                return $response->withRedirect($this->router->pathFor('admin.dash'));
            }else {
                return $response->withRedirect($this->router->pathFor('agent.dash'));
            }
        }
        
        return $this->view->render($response, 'templates/adminLogin.twig');
    }

    public function postAdminLogIn($request, $response, array $args)
    {        
        /** Set Response codes */
        $rs = [];

        /** Get login as POST data */
        $post = $request->getParsedBody();

        /** Check Whether Email or Phone Number Exists */
        $check_email_address = Login::where('email_address', $post['login'])->get();
        $check_phone_number = Login::where('phone_number', $post['login'])->get();

        /** If Email / Phone Number Doesn't Exist, return error */
        if(!$check_email_address || !$check_phone_number)
        {
            $rs = [
                'success' => false,
            ];
        }else{
            /** Check if Input is email address or phone number */
            if(!filter_var($post['login'], FILTER_VALIDATE_EMAIL))
            {
                /** Get user Data */
                $data = User::where('users.phone_number', $post['login'])
                            ->leftJoin('countries', 'users.country', 'countries.iso')
                            ->select(
                                'users.id',
                                'users.first_name',
                                'users.middle_name',
                                'users.last_name',
                                'users.email_address',
                                'users.phone_number',
                                'users.user_type',
                                'countries.nicename',
                                'countries.phonecode'
                            )
                            ->first();

                /** Check if User is an admin OR an agent */
                if($data['user_type'] === 1)
                {
                    /** User is an Admin */
                    $admin = Admin::where('user_id', $data['id'])->select('admins.id')->first();
                }elseif($data['user_type'] === 2) {
                    /** User is an Agent */
                    $agent = Agent::where('user_id', $data['id'])->select('agents.id')->first();  
                }else{
                    /** Re-route User to Customer Client Section  */
                    return $response->withDirect('/');
                } 
                
                $token = JWT::encode(['id' => $data['id'], 'email' => $data['email_address']], $settings['jwt']['secret'], "HS256");
                
                /** Set the Session Data */
                $_SESSION['user_id'] = $data['id'];
                $_SESSION['name'] = $data['first_name'].' '.$data['middle_name'].' '.$data['last_name'];
                $_SESSION['email'] = $data['email_address'];
                $_SESSION['phone'] = $data['phone_number'];
                $_SESSION['phonecode'] = $data['phonecode'];
                $_SESSION['user_type'] = $data['user_type'];
                $_SESSION['country'] = $data['nicename'];
                $_SESSION['agent_id'] = $agent['id'];
                $_SESSION['wallet_id'] = $agent['wallet_id'];
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['token'] = $token;
                
                
                /** Set Response Data */
                $rs = [
                    success => true,
                    data    => $data['user_type']
                ];

            }else{
                /** Get user Data */
                $data = User::where('users.email_address', $post['login'])
                            ->leftJoin('countries', 'users.country', 'countries.iso')
                            ->select(
                                'users.id',
                                'users.first_name',
                                'users.middle_name',
                                'users.last_name',
                                'users.email_address',
                                'users.phone_number',
                                'users.user_type',
                                'countries.nicename',
                                'countries.phonecode'
                            )
                            ->first();
                            
                /** Check if User is an admin OR an agent */
                if($data['user_type'] === 1)
                {
                    /** User is an Admin */
                    $admin = Admin::where('user_id', $data['id'])->select('admins.id')->first();
                }elseif($data['user_type'] === 2) {
                    /** User is an Agent */
                    $agent = Agent::where('user_id', $data['id'])->select('agents.id')->first();  
                }else{
                    /** Re-route User to Customer Client Section  */
                    return $response->withDirect('/');
                } 
                
                $token = JWT::encode(['id' => $data['id'], 'email' => $data['email_address']], $settings['jwt']['secret'], "HS256");
                
                /** Set the Session Data */
                $_SESSION['user_id'] = $data['id'];
                $_SESSION['name'] = $data['first_name'].' '.$data['middle_name'].' '.$data['last_name'];
                $_SESSION['email'] = $data['email_address'];
                $_SESSION['phone'] = $data['phone_number'];
                $_SESSION['phonecode'] = $data['phonecode'];
                $_SESSION['user_type'] = $data['user_type'];
                $_SESSION['country'] = $data['nicename'];
                $_SESSION['agent_id'] = $agent['id'];
                $_SESSION['wallet_id'] = $agent['wallet_id'];
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['token'] = $token;
                
                /** Set Response Data */
                $rs = [
                    success => true,
                    data    => $data['user_type']
                ];
            }
        }
        
        return $response->withJson($rs, 200);
    }

    public function logout($request, $response, array $args)
    {
        /** Unset all Session Variables */
        session_unset();

        return $response->withRedirect($this->router->pathFor('admin.login'));
    }
}