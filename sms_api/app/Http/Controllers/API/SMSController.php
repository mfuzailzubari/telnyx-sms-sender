<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\ClientException;

class SMSController extends Controller
{
    /**
     * Sends SMS.
     *
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request)
    {
        try 
        {
            $data = json_decode($request->getContent(), true);

            $validator = Validator::make($request->all(), [
                    'to' => 'required|regex:/([+])[0-9]{9}/',
                    'message' => 'required|max:255',
                ],
                [
                    'to.regex' => "The 'to' format is '+125478585' with atleast 9 digits",
                ]
            );
            
            if ($validator->fails()) {

                $errors = $validator->errors();
                return $errors->toJson();
            }
        
            $client = new \GuzzleHttp\Client(["base_uri" => "https://sms.telnyx.com"]);
            $options = [
                'json' => [
                    "from" => config('app.phone_number'),
                    "to" => $data['to'],
                    "body" => $data['message']
                ],
                'headers' => [
                    'x-profile-secret' => config('app.telnyx_app_secret')
                ]
            ]; 

            $response = $client->post("/messages", $options);
            return $response;
        } 
        catch (\Exception $e) {
            
            $error = ['message' => $e->getMessage()];
            return response($error, 400)->header('Content-Type', 'application/json');
        }
    }
}
