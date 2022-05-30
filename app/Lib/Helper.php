<?php
namespace App\Lib;
use Mail;
use Hash;
use App\Models\Account;
use App\Models\AccountSetting;
use App\Models\Session;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyStep;
use App\Models\SurveyItem;
use App\Models\EmailTemplate;
class Helper {
	
	public static function randomPassword() {
	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%&*';
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < 8; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); 
	}

	public static function storeAccount($shopifyShop, $shop_code){
		$account = new Account();
        $account->name = $shopifyShop->name;
        $account->email = $shopifyShop->email;
        $account->is_active = 1;
        $account->shop = $shopifyShop->domain;;
        $account->domain = $shopifyShop->domain;
        $account->country = $shopifyShop->country;
        $account->currency = $shopifyShop->currency;
        $account->plan_name = $shopifyShop->plan_name;
        $account->owner = $shopifyShop->shop_owner;
        $account->customer_email = $shopifyShop->customer_email;
        $account->has_discounts = $shopifyShop->has_discounts;
        $account->has_gift_cards = $shopifyShop->has_gift_cards;
        $account->save();

        $setting = new AccountSetting();
        $setting->name = $account->name;
        $setting->account_id = $account->id;
        $setting->order_prefixes = '#';
        $setting->code = $shop_code;
        $setting->save();

        return $account;
	}

	public static function storeUser($account, $password){
		$user = new User();
        $user->account_id = $account->id;
        $user->name = 'Owner';
        $user->email = Hash::make($account->shop);
        $user->role_id = 1;
        $user->password = Hash::make(self::randomPassword());
        $user->save();

		$user = new User();
        $user->account_id = $account->id;
        $user->name = $account->owner;
        $user->email = $account->email;
        $user->role_id = 2;
        $user->password = Hash::make($password);
        $user->save();

        return $user;
	}

	public static function storeSurvey($account_id){
		 $survey = new Survey();
        $survey->account_id = $account->id;
        $survey->name = 'Default survey';
        $survey->is_active = true;
        $survey->product_types = '';
        $survey->save();
        $surveyData = [
            ['name' => 'Why are you returning?', 'items' => ['Wrong item', 'Quality/Defective', 'Other']], 
            ['name' => 'Help us improve', 'items' => ['Help us improve']]
        ];
        $lastItem = null;
        foreach ($surveyData as $key => $data) {
            $step = new SurveyStep();
            $step->name = $data['name'];
            $step->sort_number = $key + 1;
            $step->survey_id = $survey->id;
            $step->save();
            foreach ($data['items'] as $index => $label) {
                $surveyItem = new SurveyItem();
                $surveyItem->label = $label;
                $surveyItem->value = $label;
                $surveyItem->type = $key == 0 ? 'radio' : 'textarea';
                $surveyItem->survey_id = $survey->id;
                $surveyItem->parent_id = $key == 1 ? $lastItem->id : null;
                $surveyItem->survey_step_id = $step->id;
                $surveyItem->sort_number = $index + 1;
                $surveyItem->save();    
                $lastItem = $surveyItem;
            }     
        }
	}

	public static function sendEmail($user, $password){
		Mail::send('emails.user', ['user' => $user, 'password' => $password], function ($mailer) use ($user) {
            $mailer->from('help@mottandbow.com', 'MB');
            $mailer->to('ealexander.zm@gmail.com', $user->name)->subject('Credentials ready');
            // $mailer->to($user->email, $user->name)->subject('Credentials ready');
        });
	}

	public static function storeEmailTemplates($account){
		$email = new EmailTemplate();
        $email->account_id = $account->id;
        $email->name = 'New refund request';
        $email->code =  'return';
        $email->is_admin = 0;
        $email->subject = 'New refund request for order {{order_number}}';
        $email->content = '<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse; background-color:#ffffff" width="100%"><tbody><tr><td align="center" valign="top"><div style="width:100%; background:#fff;"><div class="border" style="max-width:400px; margin:auto; border-bottom: solid 1px #ccc; height:2px;">&nbsp;</div></div><div class="bodyContent" mc:edit="body_content" style="width:768px; margin-left:auto; margin-right: auto"><div style="width:100%;margin:auto"><div class="block" style="padding-bottom:10px; padding-top:30px; margin-bottom:20px; border-bottom:solid 1px #C8C8C7"><h2 style="letter-spacing:1px; font-size:18px; text-transform:uppercase; text-align:center;">new Request!</h2><p style="text-align:center">We&rsquo;ve received a {{transaction_type}} request for order #{{order_number}}.&nbsp;</p></div><div class="block" style=" width: 768px; padding:0 0px 10px; margin:20px auto; border-bottom:solid 0px #C8C8C7"><div class="block_in" style=" display:none; padding:0 50px 0px;">&nbsp;</div></div></div></div></td></tr></tbody></table>';
        $email->save();

        $email = new EmailTemplate();
        $email->account_id = $account->id;
        $email->name = 'New exchange request';
        $email->code =  'exchange';
        $email->is_admin = 0;
        $email->subject = 'New exchange request for order {{order_number}}';
        $email->content = '<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse; background-color:#ffffff" width="100%"><tbody><tr><td align="center" valign="top"><div style="width:100%; background:#fff;"><div style="clear:both; float:none">&nbsp;</div><div class="border" style="max-width:400px; margin:auto; border-bottom: solid 1px #ccc; height:2px;">&nbsp;</div></div><div class="bodyContent" mc:edit="body_content" style="width:768px; margin-left:auto; margin-right: auto"><div style="width:100%;margin:auto"><div class="block">&nbsp;</div><div class="block" style="padding-bottom:10px; padding-top:30px; margin-bottom:20px; border-bottom:solid 1px #C8C8C7"><h2 style="letter-spacing:1px; font-size:18px; text-transform:uppercase; text-align:center;">Exchange Request!</h2><p style="text-align:center">We&rsquo;ve received your Exchange request #{{order_number}}. . We&rsquo;ll send you an email when your return package arrives at our warehouse.</p></div><div class="block" style=" width: 768px; padding:0 0px 10px; margin:20px auto; border-bottom:solid 0px #C8C8C7"><div class="block_in" style=" display:none; padding:0 50px 0px;">&nbsp;</div></div></div></div></td></tr></tbody></table>';
        $email->save();


        $email = new EmailTemplate();
        $email->account_id = $account->id;
        $email->name = 'New request admin';
        $email->code =  'return';
        $email->is_admin = 1;
        $email->subject = 'New refund request for order {{order_number}}';
        $email->content = '<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse; background-color:#ffffff" width="100%"><tbody><tr><td align="center" valign="top"><div style="width:100%; background:#fff;"><div class="border" style="max-width:400px; margin:auto; border-bottom: solid 1px #ccc; height:2px;">&nbsp;</div></div><div class="bodyContent" mc:edit="body_content" style="width:768px; margin-left:auto; margin-right: auto"><div style="width:100%;margin:auto"><div class="block" style="padding-bottom:10px; padding-top:30px; margin-bottom:20px; border-bottom:solid 1px #C8C8C7"><h2 style="letter-spacing:1px; font-size:18px; text-transform:uppercase; text-align:center;">new Request!</h2><p style="text-align:center">We&rsquo;ve received a {{transaction_type}} request for order #{{order_number}}.&nbsp;</p></div><div class="block" style=" width: 768px; padding:0 0px 10px; margin:20px auto; border-bottom:solid 0px #C8C8C7"><div class="block_in" style=" display:none; padding:0 50px 0px;">&nbsp;</div></div></div></div></td></tr></tbody></table>';
        $email->save();

        $email = new EmailTemplate();
        $email->account_id = $account->id;
        $email->name = 'Discount code - Order';
        $email->code =  'discount';
        $email->is_admin = 0;
        $email->subject = 'Discount code - Order {{order_number}}';
        $email->content = '<div style="width:700px; background:#fff; display: block; margin: 0 auto"><table border="0" cellpadding="0" cellspacing="0" style="margin:50px"><tbody><tr><td class="action-content"><p style="text-align: left; font-size: 1.2rem; color: #333; margin-bottom: 20px">&nbsp;</p><p style="text-align: left; font-size: 1.5rem; color: #333; margin-bottom: 20px">Hi {{firstname}},</p><p style="text-align: left;font-size: 1.5rem; color: #333">We&rsquo;ve generated {{currency}}{{amount}} the special discount code below for you&nbsp;to redeem at checkout on your next purchase.</p><p style="text-align: center;font-size: 1.5rem; color: #333; padding: 10px; background: #eee"><b>{{code}}</b></p><p style="text-align: left;font-size: 1.5rem; color: #333">Let us know if you have any questions. Thank you for your preference!</p><p style="text-align: left;font-size: 1.5rem; color: #333">Best,</p><br /><b><b>&nbsp;</b></b></td></tr></tbody></table></div>';
        $email->save();

        if($account->plan_name == 'shopify_plus'){
			$email = new EmailTemplate();
	        $email->account_id = $account->id;
	        $email->name = 'Gift Card for return from order';
	        $email->code =  'giftcard';
	        $email->is_admin = 0;
	        $email->subject = 'Gift Card for return from order {{order_number}}';
	        $email->content = '<div style="width:700px; background:#fff; display: block; margin: 0 auto"><table border="0" cellpadding="0" cellspacing="0" style="margin:50px"><tbody><tr><td class="action-content"><p style="text-align: left; font-size: 1.2rem; color: #333; margin-bottom: 20px">&nbsp;</p><p style="text-align: left; font-size: 1.5rem; color: #333; margin-bottom: 20px">Hi {{firstname}},</p><p style="text-align: left;font-size: 1.5rem; color: #333">We&rsquo;ve generated a {{currency}}{{amount}} gift card for the return of order {{order_number}}. Below you&#39;ll find the code to redeem at checkout on your next purchase.</p><p style="text-align: center;font-size: 1.5rem; color: #333; padding: 10px; background: #eee"><b>{{code}}</b></p><p style="text-align: left;font-size: 1.5rem; color: #333">Let us know if you have any questions. Thank you for your preference!</p><p style="text-align: left;font-size: 1.5rem; color: #333">Best,</p><br /><b><b>&nbsp;</b></b></td></tr></tbody></table></div>';
	        $email->save();        	
        }
	}
}