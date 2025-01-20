<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\DB;




class PayinController extends Controller
{
    
      
	 public function payin(Request $request)
    {
       
         $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cash' => 'required|numeric|min:100',
            'type' => 'required',
        ]);
        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];

            return response()->json($response);
        }
	$cash = $request->cash;
    // $extra_amt = $request->extra_cash;
     $type = $request->type;
    $userid = $request->user_id;
	   //	$total_amt=$cash+$extra_amt+$bonus;
		 
              $date = date('YmdHis');
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;
        $datetime=now();
        $check_id = DB::table('users')->where('id',$userid)->first();
        $merchantid =DB::table('admin_settings')->where('id',12)->value('longtext');
        
        if($type == 1){
        if ($check_id) {
            $redirect_url = env('APP_URL')."api/checkPayment?order_id=$orderid";
            //dd($redirect_url);
            $insert_payin = DB::table('payins')->insert([
                'user_id' => $request->user_id,
                'cash' => $request->cash,
                'type' => $request->type,
                'order_id' => $orderid,
                'redirect_url' => $redirect_url,
                'status' => 1, // Assuming initial status is 0
				'typeimage'=>"https://root.winzy.app/uploads/fastpay_image.png",
                'created_at'=>$datetime,
                'updated_at'=>$datetime
            ]);
         // dd($redirect_url);
            if (!$insert_payin) {
                return response()->json(['status' => 400, 'message' => 'Failed to store record in payin history!']);
            }
 
            $postParameter = [
                'merchantid' =>$merchantid,
                'orderid' => $orderid,
                'amount' => $request->cash,
                'name' => $check_id->u_id,
                'email' => "abc@gmail.com",
                'mobile' => $check_id->mobile,
                'remark' => 'payIn',
                'type'=>$request->cash,
                'redirect_url' => env('APP_URL')."api/checkPayment?order_id=$orderid"
              // 'redirect_url' => config('app.base_url') ."/api/checkPayment?order_id=$orderid"
            ];
            // print_r($postParameter);die;
            // echo json_encode($postParameter);die;


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://indianpay.co.in/admin/paynow',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0, 
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postParameter),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Cookie: ci_session=1ef91dbbd8079592f9061d5df3107fd55bd7fb83'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
             
			echo $response;
		//	dd($response);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Internal error!'
            ]);
        }
            
        }else{
          return response()->json([
                'status' => 400,
                'message' => 'USDT is Not Supported ....!'
            ]); 
        }
    }

    public function checkPayment(Request $request)
    {
      // dd($request);
        $orderid = $request->input('order_id');
	//dd($orderid);
     //bonus = gift_cash
        if ($orderid == "") {
            return response()->json(['status' => 400, 'message' => 'Order Id is required']);
        } else {
            $match_order = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->first();
//dd($match_order);
            if ($match_order) {
                $uid = $match_order->user_id;
            
                $cash = $match_order->cash;
                $type = $match_order->type;
               
                $orderid = $match_order->order_id;
                 $datetime=now();
              // dd("UPDATE payins SET status = 2 WHERE order_id = $orderid AND status = 1 AND user_id = $uid");

              $update_payin = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->where('user_id', $uid)->update(['status' => 2]);
    
                if ($update_payin) {
                    
                    // $wallet = $cash + $bonus + $extra_cash;
                    // $bonusToAdd = $bonus;
                    //dd($uid);
    $referid=DB::select("SELECT referral_user_id,first_recharge FROM `users` WHERE id=$uid");
    //dd($referid);
    $first_recharge=$referid[0]->first_recharge;
    $referuserid=$referid[0]->referral_user_id;
  // dd($first_recharge);
 
if($first_recharge == 0){
    
    $extra=DB::select("SELECT * FROM `extra_first_deposit_bonus` WHERE `first_deposit_ammount`<=300 && max_amount >= 300
"); 
//  $extra=DB::select("SELECT * FROM `extra_first_deposit_bonus` WHERE `first_deposit_ammount`=$cash"); 
//     $id=$extra[0]->id;
//     $first_deposit_ammount=$extra[0]->first_deposit_ammount;
//     $bonus=$extra[0]->bonus;
//     $amount=$bonus+$first_deposit_ammount;
    //dd($amount);
    
    $extra = DB::select("SELECT * FROM `extra_first_deposit_bonus` WHERE `first_deposit_ammount`=$cash");

if (count($extra) > 0) {
    // Check if there are results
    $id = $extra[0]->id;
    $first_deposit_ammount = $extra[0]->first_deposit_ammount;
    $bonus = $extra[0]->bonus;
    $amount = $bonus + $first_deposit_ammount;
     DB::INSERT("INSERT INTO `extra_first_deposit_bonus_claim`( `userid`, `extra_fdb_id`, `amount`, `bonus`, `status`, `created_at`, `updated_at`) VALUES ('$uid','$id','$first_deposit_ammount','$bonus','0','$datetime','$datetime')");
} else {
    
}

    if(!empty($extra))
    {
   
                    $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $amount,
    first_recharge = 1,
    first_recharge_amount = first_recharge_amount + $amount,
    recharge = recharge + $amount,
    total_payin = total_payin + $amount,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $amount
    WHERE id = $uid;
    ");
    }else{
        $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $cash,
    first_recharge = 1,
    first_recharge_amount = first_recharge_amount + $cash,
    recharge = recharge + $cash,
    total_payin = total_payin + $cash,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $cash
    WHERE id = $uid;
    ");
    }
    //dd("hiii");
    // dd("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1,yesterday_first_deposit = yesterday_first_deposit + $cash WHERE id=$referuserid");
    //dd($referuserid);
    DB::UPDATE("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1,yesterday_first_deposit = yesterday_first_deposit + $cash WHERE id=$referuserid");
     return redirect()->away(env('APP_URL').'uploads/payment_success.php');
}else{
    
      $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $cash,
    recharge = recharge + $cash,
    total_payin = total_payin + $cash,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $cash
    WHERE id = $uid;
    ");
    
    //dd("hello");
     //dd($referuserid);
    DB::select("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1 WHERE id=$referuserid");
     return redirect()->away(env('APP_URL').'uploads/payment_success.php');
}

     
    
                    if ($updateUser) {
                        // Redirect to success page
                        //dd("hello");
                        return redirect()->away(env('APP_URL').'uploads/payment_success.php');
                    } else {
                        return response()->json(['status' => 400, 'message' => 'User balance update failed!']);
                    }
                } else {
                    return response()->json(['status' => 400, 'message' => 'Failed to update payment status!']);
                }
            } else {
                return response()->json(['status' => 400, 'message' => 'Order id not found or already processed']);
            }
        }
    }
	
public function withdraw_request(Request $request)
{

		  $date = date('Ymd');
        $rand = rand(1111111, 9999999);
        $transaction_id = $date . $rand;
	
		 $userid=$request->userid;
		 $amount=$request->amount;
		   $validator=validator ::make($request->all(),
        [
            'userid'=>'required',
			'amount'=>'required',
			
        ]);
        $date=date('Y-m-d h:i:s');
        if($validator ->fails()){
            $response=[
                'success'=>"400",
                'message'=>$validator ->errors()
            ];                                                   
            
            return response()->json($response,400);
        }
      
		 $datetime = date('Y-m-d H:i:s');
		 
         $user = DB::select("SELECT * FROM `users` where `id` =$userid");
		 $account_id=$user[0]->accountno_id;
		 $mobile=$user[0]->mobile;
		 $wallet=$user[0]->wallet;
		 $accountlist=DB::select("SELECT * FROM `bank_details` WHERE `id`=$account_id");
		 $insert= DB::table('transaction_history')->insert([
        'userid' => $userid,
        'amount' => $amount,
        'mobile' => $mobile,
		  'account_id'=>$account_id,
        'status' => 0,
			 'type'=>1,
        'date' => $datetime,
		  'transaction_id' => $transaction_id,
    ]);
		  DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount,`winning_wallet`=`winning_wallet`-$amount  WHERE `id`=$userid");
          if($insert){
          $response =[ 'success'=>"200",'data'=>$insert,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }
	
	 public function  callbackfunc()
    {
        $date=date('Y-m-s H:i:s');
            header("Access-Control-Allow-Methods: POST");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: Origin, Content-Type");
            header("Content-Type: application/json");
            header("Expires: 0");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
                    
            
            $date=date('Y-m-d H:i:s');
          $data = json_decode(file_get_contents("php://input"), true);
         $d=json_encode($data);
       
		$transactionid= $data['iop_txn_id'];
		 $invoice= $data['invoiceNumber'];
		$amount= $data['amount'];
		$status= $data['status'];
          DB::table('payincallback')->insert([
               'data'=>$d,
			  'datetime'=>$date
            ]);
		 
		if (isset($data['status']) && ($data['status'] == '0' || $data['status'] == 'SUCCESS') &&
    isset($data['gatewayResponseStatus']) && ($data['gatewayResponseStatus'] == '0' || $data['gatewayResponseStatus'] == 'SUCCESS')) {
    // Your code here
}

		 {



				$selecteddata = DB::table('payins')
					->select('cash as amount', 'user_id')
					->where('order_id', $invoice)
					->first();
			
			     $uid = $selecteddata->user_id;
                $cash = $selecteddata->amount;
				if (!empty($selecteddata)) {
					$up=DB::table('payins')
						->where('order_id', $invoice)
						->update(['status' => '2']);

					$referid = DB::table('users')
						->select('referral_user_id', 'first_recharge')
						->where('id', $uid)
						->first();

					$first_recharge = $referid->first_recharge;
					$referuserid = $referid->referral_user_id;
					$cash = $selecteddata->amount;

					if ($first_recharge == 0) {
						DB::table('users')
							->where('id', $uid)
							->update([
								'wallet' => DB::raw("wallet + $cash"),
								'first_recharge' => DB::raw("first_recharge + $cash"),
								'first_recharge_amount' => DB::raw("first_recharge_amount + $cash"),
								'recharge' => DB::raw("recharge + $cash"),
								'total_payin' => DB::raw("total_payin + $cash"),
								'no_of_payin' => DB::raw("no_of_payin + 1"),
								'deposit_balance' => DB::raw("deposit_balance + $cash"),
							]);

						DB::table('users')
							->where('id', $referuserid)
							->update([
								'yesterday_payin' => DB::raw("yesterday_payin + $cash"),
								'yesterday_no_of_payin' => DB::raw("yesterday_no_of_payin + 1"),
								'yesterday_first_deposit' => DB::raw("yesterday_first_deposit + $cash"),
							]);

				
					} else {
						DB::table('users')
							->where('id', $uid)
							->update([
								'wallet' => DB::raw("wallet + $cash"),
								'recharge' => DB::raw("recharge + $cash"),
								'total_payin' => DB::raw("total_payin + $cash"),
								'no_of_payin' => DB::raw("no_of_payin + 1"),
								'deposit_balance' => DB::raw("deposit_balance + $cash"),
							]);

						DB::table('users')
							->where('id', $referuserid)
							->update([
								'yesterday_payin' => DB::raw("yesterday_payin + $cash"),
								'yesterday_no_of_payin' => DB::raw("yesterday_no_of_payin + 1"),
							]);
					}
				}


			 
			 
		 }
        
          
          
          
         
        }
	
	
		 public function  callbackfunc_payout()
    {
        $date=date('Y-m-s H:i:s');
            header("Access-Control-Allow-Methods: POST");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: Origin, Content-Type");
            header("Content-Type: application/json");
            header("Expires: 0");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
                    
            $date=date('Y-m-d H:i:s');
          $data = json_decode(file_get_contents("php://input"), true);
         $d=json_encode($data);
         
          DB::table('withdrawal_calback')->insert([
               'data'=>$d,
			  'datetime'=>$date
            ]);
		 echo "hellow";
		 
		 if(isset($data['status'])&&$data['status']=='SUCCESS'&&isset($data['gatewayResponseStatus'])&&$data['gatewayResponseStatus']=='SUCCESS')
		 {

		 }
        
        }
	
	
	public function finixpay()
	{

			$url = 'http://api.finixpay.in/js988wuebk7hsn/api/v1/quick_transfers/FinixDynamicQR';
			$queryParams = array(
				'payin_ref' => '112233112',
				'amount' => '10.00',
				'fName' => 'anurag',
				'lName' => 'pandey',
				'mNo' => '7081472797',
				'email' => 'anurag@gmail.com',
				'add1' => 'lucknow',
				'city' => 'lucknow',
				'state' => 'uttar pradesh',
				'pCode' => '226021'
			);

			$url .= '?' . http_build_query($queryParams);

			// JSON data to be sent in the POST request
			$data = array(
				'merchantId' => '23432520',
				'clientid' => '96O58Z1A-SZD3-B79F-YZ6Z-LBBH85VX82PA',
				'clientSecretKey' => '8SQAHDNNMXEY2Z8ZT83Z3E48416PEDB1'
			);

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json'
			));

			$response = curl_exec($ch);

			if(curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			}

			curl_close($ch);

			echo $response;
			


	}
	
	
        public function redirect_success(){
            return view('success');
        }
	
	
//Skill Pay
	
	
	
    public function Skillpay(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'amount' => 'required|numeric',
            'mobile' => 'required|string|regex:/^[0-9]{10}$/', // Assuming mobile is 10 digits
            'orderid' => 'required|string',
        ]);

        // Extract validated input data
        $name = $validated['name'];
        $email = $validated['email'];
        $amount = number_format($validated['amount'], 2, '.', ''); // Format amount as "0.00"
        $mobile = $validated['mobile'];
        $orderid = $validated['orderid'];

        // Set timezone
        date_default_timezone_set('Asia/Kolkata');
    
        // Merchant details
        $authId = "M00006488";
        $authKey = "tE2Pl4nM4Bj1Ez4lA9kP9fu7Qc5jG4jT";
        $transactionId = $orderid;
        $paymentDate = now()->format('Y-m-d H:i:s');
        $paymentCallBackUrl = 'https://root.winzy.app/api/skillpaycallback';

        // Data to be encrypted
        $data = [
            "AuthID" => $authId,
            "AuthKey" => $authKey,
            "CustRefNum" => $transactionId,
            "txn_Amount" => $amount,
            "PaymentDate" => $paymentDate,
            "ContactNo" => $mobile,
            "EmailId" => $email,
            "IntegrationType" => "seamless",
            "CallbackURL" => $paymentCallBackUrl,
            "adf1" => "NA",
            "adf2" => "NA",
            "adf3" => "NA",
            "MOP" => "UPI",
            "MOPType" => "UPI",
            "MOPDetails" => "I"
        ];

        // Encrypt data
        $jsonData = json_encode($data);
        
        $iv = substr($authKey, 0, 16); // IV for encryption
    
        $encryptedData = $this->encryptData($jsonData, $authKey, $iv);
        if (!$encryptedData) {
            return response()->json(['error' => 'Encryption failed'], 500);
        }

        // Prepare POST data
        $postData = [
            'encData' => $encryptedData,
            'AuthID' => $authId,
        ];
    
        // Send cURL request to the payment gateway
        $url = 'https://dashboard.skill-pay.in/pay/paymentinit';
        $response = Http::asForm()->post($url, $postData);
    
        // Handle failed request
        if ($response->failed()) {
            return response()->json(['error' => 'Payment gateway request failed'], 500);
        }

   
        $responseData = $response->json();
        
        if (!isset($responseData['respData'])) {
            return response()->json(['error' => 'Invalid response from payment gateway'], 500);
        }
    
        // Decrypt response data
        $decoded = $responseData['respData'];
        $decryptedData = $this->decryptData($decoded, $authKey, $iv);
    
        if (!$decryptedData) {
            return response()->json(['error' => 'Decryption failed'], 500);
        }
    
        // Extract UPI Intent QR string
        $usersdata = json_decode($decryptedData);
        if (!isset($usersdata->qrString)) {
            return response()->json(['error' => 'QR String not found in response'], 500);
        }
    
        // Get UPI Intent link
        $intentlink = $usersdata->qrString;

        // Prepare data for encoding
        $linkdata = [
            'cname' => $name,
            'amount' => $amount,
            'order_id' => $transactionId,
            'intent_link' => $intentlink
        ];
    
        // Convert array to JSON and then base64 encode it
        $jsonLinkData = json_encode($linkdata);
        $encodedData = base64_encode($jsonLinkData);
    
        // Return a well-formed JSON response
        return response()->json([
            'status' => 'SUCCESS',
            'amount' => $amount,
            'order_id' => $transactionId,
            'payment_link' => 'https://root.winzy.app/skillpay/pay.php?data=' . urlencode($encodedData),
        ]);
    }


    private function encryptData($data, $key, $iv)
    {
        return openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    }

    private function decryptData($data, $key, $iv)
    {
        return openssl_decrypt($data, 'aes-256-cbc', $key, 0, $iv);
    }

  
    public function skillpaycallback(Request $request)
    {
        // Set CORS headers manually
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Origin, Content-Type');
        header('Expires: 0');
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . " GMT");
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
    
        // Get the raw POST data
        $data = $request->getContent();
    
        // Set the auth key
        $authKey = "tE2Pl4nM4Bj1Ez4lA9kP9fu7Qc5jG4jT";
    
        // Retrieve variables from the request
        $authid = $request->input('AuthID');
        $resp_data = $request->input('respData');
        $aggrefno = $request->input('AggRefNo');
    
        if (!$authid || !$resp_data || !$aggrefno) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
    
        // Decrypt the response data
        $originalData = str_replace(' ', '+', $resp_data);
        $iv = substr($authKey, 0, 16);
        $decryptedData = openssl_decrypt($originalData, 'aes-256-cbc', $authKey, 0, $iv);
    
        // Handle potential decryption failure
        if ($decryptedData === false) {
            Log::error('Decryption failed', ['data' => $originalData]);
            return response()->json(['error' => 'Decryption failed'], 500);
        }
    
        $datas = json_decode($decryptedData);
    
        // Check if decryption returned a valid object
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON decoding failed', ['data' => $decryptedData]);
            return response()->json(['error' => 'Invalid JSON data'], 400);
        }
    
        $orderid = $datas->CustRefNum ?? null;
    
        // Validate if order ID exists
        if (!$orderid) {
            Log::error('Missing order ID after decryption', ['data' => $datas]);
            return response()->json(['error' => 'Missing order ID'], 400);
        }
    
        // Insert into skillpaypayincallback table
        DB::table('skillpaypayincallback')->insert([
            'data' => $decryptedData,
            'AggRefNo' => $aggrefno,
            'auth_id' => $authid,
            'orderid' => $orderid,
        ]);
    
        // Update transaction_payin status
        
        DB::table('payins')->where('order_id', $orderid)->update(['status' => '2']);
        
       
        return response()->json(['success' => true], 200);
   }
    
  
	public function skillpay_payin(Request $request)
    {
    // Validate the request input
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cash' => 'required|numeric|min:0', // Ensuring cash is numeric and non-negative
            'type' => 'required|in:1,2,3', // Ensure 'type' is within acceptable values
        ]);
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first(),
            ]);
        }

        $cash = $request->cash;
        $type = $request->type;
        $userid = $request->user_id;
        $datetime = now();
    
        // Generate a unique order ID
        $orderid = now()->format('YmdHis') . rand(11111, 99999);

        // Check if the user exists
        $check_id = DB::table('users')->where('id', $userid)->first();

        if ($type == 3) {
            if ($check_id) {
                // Prepare redirect URL
                $redirect_url = env('APP_URL')."api/payin-successfully";

                // Insert payment record into payins table
                $insert_payin = DB::table('payins')->insert([
                    'user_id' => $userid,
                    'cash' => $cash,
                    'type' => $type,
                    'order_id' => $orderid,
                    'redirect_url' => $redirect_url,
                    'status' => 1, // Assuming initial status is 1
                    'typeimage' => "https://root.nandigame.live/uploads/fastpay_image.png",
                    'created_at' => $datetime,
                    'updated_at' => $datetime,
                ]);
    
                if (!$insert_payin) {
                    return response()->json(['status' => 400, 'message' => 'Failed to store record in payin history!']);
                }

                // Prepare parameters for the external API request
                $postParameter = [
                    'name' => $check_id->u_id,
                    'email' => "abc@gmail.com",
                    'amount' => $cash,
                    'mobile' => $check_id->mobile,
                    "orderid" => $orderid,
                    'redirect_url' => $redirect_url,
                ];



                $curl = curl_init();
                
                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'https://root.winzy.app/api/Skillpay',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS =>json_encode($postParameter),
                  CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                  ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                echo $response;
            } else {
                    return response()->json(['status' => 400, 'message' => 'User not found!']);
                }
        } else {
            return response()->json(['status' => 400, 'message' => 'USDT is not supported!']);
            }
    }


    public function checkSkillPayOrderId(Request $request)
    {
        $order_id = $request->input('order_id');
       

        // Fetch the data from the 'skillpaypayincallback' table
        $data = DB::table('skillpaypayincallback')->where('orderid', $order_id)->first();
  
        if (!empty($data->orderid)) {
        // Fetch the redirect URL if the transaction has a status of '1'
       
        $rdata = DB::table('payins')
            ->where('status', 2)
            ->where('order_id', $order_id)
            ->first();

     
        if($rdata){
            
            DB::table('users')->where('id', $rdata->user_id)->update([
								'wallet' => DB::raw("wallet + $rdata->cash")]);
        }

        $res = [
            'status' => 200,
            'msg'    => env('APP_URL')."api/payin-successfully" ?? 'Redirect URL not found',
        ];
        
        // $res = env('APP_URL')."api/payin-successfully";

        return response()->json($res);
        } else {
        $res = [
            'status' => 200,
            'msg'    => 'Pending',
        ];

        return response()->json($res);
    }
    }

	
	
	
	
}
