<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use DateTime;

use Illuminate\Support\Facades\Http;


 
class AgencyPromotionController extends Controller
{
	
	public function promotion_data($id) {
    try {
       
        $users = User::findOrFail($id);
        $user_id = $users->id;
		
		$currentDate = Carbon::now()->subDay()->format('Y-m-d');

       
        $directSubordinateCount = User::where('referral_user_id', $user_id)->count();

       
        $totalCommission = User::where('id', $user_id)->value('commission');
		
		$referral_code = User::where('id', $user_id)->value('referral_code');
		
		$yesterday_total_commission = User::where('id', $user_id)->value('yesterday_total_commission');
		
		
		 $teamSubordinateCount = \DB::select("
            WITH RECURSIVE subordinates AS (
                SELECT id, referral_user_id, 1 AS level
                FROM users
                WHERE referral_user_id = ?

                UNION ALL

                SELECT u.id, u.referral_user_id, s.level + 1
                FROM users u
                INNER JOIN subordinates s ON s.id = u.referral_user_id
                WHERE s.level < 15
            )
            SELECT COUNT(*) as count
            FROM subordinates
        ", [$user_id]);
		
		
		$register = \DB::select("
    SELECT count(`id`) as register
    FROM `users`
    WHERE `referral_user_id` = ? 
    AND `created_at` LIKE '$currentDate %'
", [$user_id]);
		
		$deposit_number = \DB::select("SELECT count(`p`.`id`) as deposit_number, 
       		sum(`p`.`cash`) as deposit_amount 
			FROM `payins` p
			JOIN `users` u ON p.`user_id` = u.`id`
			WHERE u.`referral_user_id` = ?
  			AND u.`created_at` like '$currentDate %'", [$user_id]);
		
	  $first_deposit = \DB::select("
    SELECT count(`p`.`id`) as first_deposit
    FROM `payins` p
    JOIN `users` u ON p.`user_id` = u.`id`
    WHERE u.`referral_user_id` = $user_id
    AND u.`created_at` LIKE '$currentDate %' 
    AND u.`salary_first_recharge` = '0'
");


	$subordinates_register = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?

        UNION ALL

        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level < 15
    )
    SELECT count(*) as register 
    FROM users 
    WHERE referral_user_id = ? 
    AND created_at LIKE '$currentDate %'
", [$user_id, $user_id]);
		
			$subordinates_deposit = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level < 15
    )
    SELECT count(p.id) as deposit_number, 
           sum(p.cash) as deposit_amount 
    FROM payins p
    JOIN users s ON p.user_id = s.id
    WHERE s.referral_user_id = ? 
    AND s.created_at LIKE '$currentDate %'
", [$user_id, $user_id]);
		
    $subordinates_first_deposit = \DB::select("
	
	 WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level < 15
    )
	
    SELECT count(`p`.`id`) as first_deposit
    FROM `payins` p
    JOIN `users` u ON p.`user_id` = u.`id`
    WHERE u.`referral_user_id` = ?
    AND u.`created_at` LIKE '$currentDate %' 
    AND u.`salary_first_recharge` = '0'
    ", [$user_id, $user_id]);



      
        $result = [
			'yesterday_total_commission' => $yesterday_total_commission ?? 0,
			'register' => $register[0]->register ?? 0,
			'deposit_number' => $deposit_number[0]->deposit_number ?? 0,
			'deposit_amount' => $deposit_number[0]->deposit_amount ?? 0,
			'first_deposit' => $first_deposit[0]->first_deposit ?? 0,
			
			'subordinates_register' => $subordinates_register[0]->register ?? 0,
			'subordinates_deposit_number' => $subordinates_deposit[0]->deposit_number ?? 0,
			'subordinates_deposit_amount' => $subordinates_deposit[0]->deposit_amount ?? 0,
			'subordinates_first_deposit' => $subordinates_first_deposit[0]->first_deposit ?? 0,
			
            'direct_subordinate' => $directSubordinateCount ?? 0,
            'total_commission' => $totalCommission ?? 0,
            'team_subordinate' => $teamSubordinateCount[0]->count ?? 0,
			
			'referral_code' => $referral_code
        ];

	
                return response()->json($result,200);
		
     
    } catch (\Exception $e) {
       
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

	
	
	public function new_subordinate(Request $request){
	try {
		
		 $validator = Validator::make($request->all(), [
            'id' => 'required',
			'type' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
        if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
       
        $users = User::findOrFail($request->id);
        $user_id = $users->id;
		
		$currentDate = Carbon::now()->format('Y-m-d');
		$yesterdayDate  = Carbon::yesterday()->format('Y-m-d');
		$startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
		
		if($request->type == 1){
		$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->where('created_at', 'like', $currentDate . '%')
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
		}elseif($request->type == 2){
			
				$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->where('created_at', 'like', $currentDate . '%')
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
		}elseif($request->type == 3){
				$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
		}
		
		
		 } catch (\Exception $e) {
       
        return response()->json(['error' => $e->getMessage()], 500);
    }
 }
	
	
	
	
	
	public function tier(){
		try {
			
		$tier =	DB::table('mlm_levels')->select('name')->get();
			
			if($tier->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $tier]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
			} catch (\Exception $e) {
       
        	 return response()->json(['error' => $e->getMessage()], 500);
      }
		
		
	}
	
	
public function subordinate_data(Request $request) {
	
    try {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'tier' => 'required|integer|min:1',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ]; 
            return response()->json($response, 400);
        }

        $user_id = $request->id; 
        $tier = $request->tier; 
		$search_uid = $request->u_id;
        $currentDate = Carbon::now()->subDay()->format('Y-m-d');
		
		  if (!empty($search_uid)) {
           $subordinates_deposit = \DB::select("
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(SUM(bets.amount), 0) AS bet_amount, 
        COALESCE(SUM(payins.cash), 0) AS total_cash, 
        users.commission AS commission, 
        DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date 
    FROM users
    LEFT JOIN bets ON users.id = bets.userid AND bets.created_at LIKE ?
    LEFT JOIN payins ON users.id = payins.user_id AND payins.created_at LIKE ?
    WHERE users.u_id LIKE ?
    GROUP BY users.id, users.u_id, users.commission;
", [$currentDate . ' %', $currentDate . ' %', $search_uid .'%']);
			  
			 
			  $subordinates_data = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(payin_summary1.total_payins, 0) AS payin_count,
        COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
        COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
        COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid
    
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_payin_cash
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary2 ON users.id = payin_summary2.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_payins
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary1 ON users.id = payin_summary1.user_id

    LEFT JOIN (
        SELECT userid, COUNT(DISTINCT userid) AS total_bettors
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bettor_count ON users.id = bettor_count.userid
    WHERE users.id IN (
        SELECT id FROM subordinates WHERE level = ?
    )
    GROUP BY 
        users.id, 
        users.u_id, 
        payin_summary1.total_payins,
        bettor_count.total_bettors,
        bet_summary.total_bet_amount,
        payin_summary2.total_payin_cash
", [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]);



			  

        } else {
		
       $subordinates_deposit = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount, 
        COALESCE(payin_summary.total_cash, 0) AS total_cash,  
        users.commission AS commission, 
        DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date 
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid 
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_cash 
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary ON users.id = payin_summary.user_id
    WHERE users.id IN (
        SELECT id FROM subordinates WHERE level = ?
    )
    GROUP BY users.id, users.u_id, users.commission, bet_summary.total_bet_amount, payin_summary.total_cash
",[$user_id, $tier, $currentDate . ' %', $currentDate . ' %', $tier]);
		
	$subordinates_data = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(payin_summary1.total_payins, 0) AS payin_count,
        COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
        COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
        COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid
    
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_payin_cash
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary2 ON users.id = payin_summary2.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_payins
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary1 ON users.id = payin_summary1.user_id

    LEFT JOIN (
        SELECT userid, COUNT(DISTINCT userid) AS total_bettors
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bettor_count ON users.id = bettor_count.userid
    WHERE users.id IN (
        SELECT id FROM subordinates WHERE level = ?
    )
    GROUP BY 
        users.id, 
        users.u_id, 
        payin_summary1.total_payins,
        bettor_count.total_bettors,
        bet_summary.total_bet_amount,
        payin_summary2.total_payin_cash
", [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]);



		 }

        $result = [
			'number of deposit' => $subordinates_data[0]->payin_count,
			'payin amount' => $subordinates_data[0]->payin_amount,
			'number of bettor' => $subordinates_data[0]->bettor_count,
			'bet amount' => $subordinates_data[0]->bet_amount,
			'first deposit ' => $subordinates_data[0]->total_first_recharge ?? 0,
			'first deposit amount' => $subordinates_data[0]->total_first_deposit_amount ?? 0,
			
            'subordinates_data' => $subordinates_deposit ?? 0,
        ];

        return response()->json($result, 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
		
    }
}

public function turnover_new_old()
{
    // Get the current datetime and the date for the previous day
    $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');
    //dd($currentDate);

	
    DB::table('users')->update(['yesterday_total_commission' => 0]);

    // Fetch users who have a referral_user_id
    $referralUsers = DB::table('users')->whereNotNull('users.referral_user_id')->get();

    
    $referralUsersCount = $referralUsers->count();


    // Check if there are any users retrieved
   if ($referralUsersCount > 0) {
      
  

        foreach ($referralUsers as $referralUser) {
            $user_id = $referralUser->id;
	  
            $maxTier = 10;

   $subordinatesData = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        subordinates.level,
        COALESCE(SUM(bet_summary.total_bet_amount), 0) AS bet_amount,
        COALESCE(SUM(bet_summary.total_bet_amount), 0) * COALESCE(level_commissions.commission, 0) / 100 AS commission
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ?
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid 
    LEFT JOIN subordinates ON users.id = subordinates.id
    LEFT JOIN (
        SELECT id, commission
        FROM mlm_levels
    ) AS level_commissions ON subordinates.level = level_commissions.id
    WHERE subordinates.level <= ?
    GROUP BY users.id, subordinates.level, level_commissions.commission;
", [$user_id, $maxTier, $currentDate . '%', $maxTier]);

$totalCommission = 0;



foreach ($subordinatesData as $data) {
    $totalCommission += $data->commission;
    

   
}

			 //echo($user_id);
			 //die;
	// echo "<pre>"; print_r($subordinatesData); echo "<pre>";  die;

                DB::table('users')->where('id', $user_id)->update([
        'wallet' => DB::raw('wallet + ' . $totalCommission),
        'commission' => DB::raw('commission + ' . $totalCommission),
		'yesterday_total_commission' => $totalCommission,
        'updated_at' => $datetime,
    ]);
		
			  DB::table('wallet_history')->insert([
    'userid' => $user_id,
    'amount' => $totalCommission,
    'subtypeid' => 26,
    'created_at' => $datetime,
    'updated_at' => $datetime,
]);
			

           
        }

    
    }else{
         return response()->json(['message' => 'No referral users found.'], 400);
    }

   
}

public function turnover_new()
{
    // Get the current datetime and the date for the previous day
    $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');
    
    // Reset yesterday's total commission to 0 for all users
    DB::table('users')->update(['yesterday_total_commission' => 0]);

    // Fetch users who have a referral_user_id (i.e., have a referrer)
    $referralUsers = DB::table('users')->whereNotNull('referral_user_id')->get();
    //dd($referralUsers);
    $referralUsersCount = $referralUsers->count();

    // Check if there are any referral users
    if ($referralUsersCount > 0) {

        foreach ($referralUsers as $referralUser) {
            $user_id = $referralUser->id;
            $maxTier = 10;

            // Recursive query to fetch all subordinates within the maxTier levels
            $subordinatesData = \DB::select("
                WITH RECURSIVE subordinates AS (
                    -- Base case: Start from users directly referred by the current user
                    SELECT id, referral_user_id, 1 AS level
                    FROM users
                    WHERE referral_user_id = ?
                    UNION ALL
                    -- Recursive case: Get users referred by users in the previous level
                    SELECT u.id, u.referral_user_id, s.level + 1
                    FROM users u
                    INNER JOIN subordinates s ON s.id = u.referral_user_id
                    WHERE s.level + 1 <= ?
                )
                SELECT 
                    users.id, 
                    subordinates.level,
                    COALESCE(SUM(bet_summary.total_bet_amount), 0) AS bet_amount,
                    COALESCE(SUM(bet_summary.total_bet_amount), 0) * COALESCE(level_commissions.commission, 0) / 100 AS commission
                FROM users
                LEFT JOIN (
                    -- Sum bet amounts for each user for the previous day
                    SELECT userid, SUM(amount) AS total_bet_amount 
                    FROM bets 
                    WHERE created_at LIKE ?
                    GROUP BY userid
                ) AS bet_summary ON users.id = bet_summary.userid 
                LEFT JOIN subordinates ON users.id = subordinates.id
                LEFT JOIN (
                    -- Commission rates for each level
                    SELECT id, commission
                    FROM mlm_levels
                ) AS level_commissions ON subordinates.level = level_commissions.id
                WHERE subordinates.level <= ?
                GROUP BY users.id, subordinates.level, level_commissions.commission;
            ", [$user_id, $maxTier, $currentDate . '%', $maxTier]);
//return $subordinatesData;
            $totalCommission = 0;

            // Calculate the total commission for the user by summing all subordinate commissions
            foreach ($subordinatesData as $data) {
                $totalCommission += $data->commission;
            }

            // Update the user's wallet, commission, and yesterday's total commission
            DB::table('users')->where('id', $user_id)->update([
                'wallet' => DB::raw('wallet + ' . $totalCommission),
                'commission' => DB::raw('commission + ' . $totalCommission),
                'yesterday_total_commission' => $totalCommission,
                'updated_at' => $datetime,
            ]);

            // Insert a record into the wallet history for the user's commission
            DB::table('wallet_history')->insert([
                'userid' => $user_id,
                'amount' => $totalCommission,
                'subtypeid' => 26, // Assuming 26 is the subtype for commission
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }

    } else {
        return response()->json(['message' => 'No referral users found.'], 400);
    }
}







	  
	
}