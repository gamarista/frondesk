<?php

namespace App\Validators;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\User;

class userValidator 
{

    public function messagesValidator()
    {
        $messages = [
            'IdRole.required' => 'The role is required',
            'IdMC.required' => 'The center is required',
            'dZone.required' => 'The zone is required',
            'IdVehicle.required' => 'The vehicle is required',
            'password.in' => 'The password must be matched',
        ];
        return $messages;
    }

    
    public function messagesValidatorResetPassword()
    {
        $messages = [
            'checkOldPassword.accepted' => 'The old password must be matched',
            'password.in' => 'The password must be matched',
        ];
        return $messages;
    }

    public function validatorResetPassword(array $data){

        return Validator::make($data, [
            'oldpassword' => ['required', 'string', 'max:20'],
            'password' => ['required','in:'.$data['confirm_password'], 'string', 'max:20'],
            'confirm_password' => ['required', 'string', 'max:20'],
            'checkOldPassword' => ['required','accepted', 'boolean'],
           
        ],$this->messagesValidatorResetPassword());

    }

    public function validator(array $data,User $user = null,$driver,$refreshDriver = null)
    {
        
        if ( $user == null  && !isset($driver)) {
          
            return Validator::make($data, [
                'name' => ['required', 'string', 'max:50'],
                'email' => ['required', 'unique:users,email','string', 'max:50'],
                'password' => ['required','in:'.$data['confirm_password'], 'string', 'max:20'],
                'confirm_password' => ['required', 'string', 'max:20'],
                'IdRole' => ['required', 'integer'],
            ],$this->messagesValidator());
        }elseif($user == null  && isset($driver)){
          
            return Validator::make($data, [
                'name' => ['required', 'string', 'max:50'],
                'email' => ['required', 'unique:users,email','string', 'max:50'],
                'password' => ['required','in:'.$data['confirm_password'], 'string', 'max:20'],
                'confirm_password' => ['required', 'string', 'max:20'],
                'IdVehicle' => ['required', 'integer'],
                'IdRole' => ['required', 'integer'],
                'driver_card_number' => ['required', 'unique:driver_assigments,driver_card_number','string', 'max:50'],
                'Phone1' => ['required', 'string', 'max:20'],
                'Address' => ['required', 'string', 'max:100'],
                'IdVehicle' => ['required', 'integer'],
                'dZone' => ['required', 'integer'],
                'IdMC' => ['required', 'integer'],
            ],$this->messagesValidator());
        }elseif(isset($user) && !isset($driver)){

            return Validator::make($data, [
                'name' => ['required', 'string', 'max:50'],
                'email' => ['required','string', 'max:50',Rule::unique('users')->ignore($user->id)],
                'IdRole' => ['required', 'integer'],
            ],$this->messagesValidator());

        }elseif(isset($user) && isset($driver) && isset($refreshDriver)){

            return Validator::make($data, [
                'name' => ['required', 'string', 'max:50'],
                'email' => ['required','string', 'max:50',Rule::unique('users')->ignore($user->id)],
                'IdVehicle' => ['required', 'integer'],
                'IdRole' => ['required', 'integer'],
                'driver_card_number' => ['required','string', 'max:50', Rule::unique('driver_assigments')->ignore($refreshDriver->Id)],
                'Phone1' => ['required', 'string', 'max:20'],
                'Address' => ['required', 'string', 'max:100'],
                'IdVehicle' => ['required', 'integer'],
                'dZone' => ['required', 'integer'],
                'IdMC' => ['required', 'integer'],
            ],$this->messagesValidator());

        }elseif(isset($user) && isset($driver) && !isset($refreshDriver)){

            
            return Validator::make($data, [
                'name' => ['required', 'string', 'max:50'],
                'email' => ['required','string', 'max:50',Rule::unique('users')->ignore($user->id)],
                'IdVehicle' => ['required', 'integer'],
                'IdRole' => ['required', 'integer'],
                'driver_card_number' => ['required','string', 'max:50','unique:driver_assigments,driver_card_number'],
                'Phone1' => ['required', 'string', 'max:20'],
                'Address' => ['required', 'string', 'max:100'],
                'IdVehicle' => ['required', 'integer'],
                'dZone' => ['required', 'integer'],
                'IdMC' => ['required', 'integer'],
            ],$this->messagesValidator());

        }
      
    }


}