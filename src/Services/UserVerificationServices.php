<?php 


namespace App\Services;


class UserVerificationServices
{
    public function checkIfConnectedUserIsAdmin($token)
    {
        // A small verification to see if the one that is trying to delete the video have the admin role or not.
        // This verification is not really needed as you need to be connected with 
        // ROLE_ADMIN to even access this page (you can check that in security.yaml).
        // But i still added it as a double wall in case.


        
        // return an access key if the user is admin
        if ($token->getToken()) {
            $user = $token->getToken()->getUser();
            $userRoles = $user->getRoles();
        }

        $acceptedRoles = array("ROLE_ADMIN");
        $accessKey = false;

        //Verify if Role of connected user is an accepted role or not
        if ($userRoles && count(array_intersect($userRoles , $acceptedRoles)) > 0) {
            $accessKey = true;
        }
        return $accessKey;
    }
}

