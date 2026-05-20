<?php
namespace App\Http\Controllers\BrandKit;

use App\Http\Controllers\AesCipher;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BrandKit;
use App\Models\UserData;
use Mail;
use Carbon\Carbon;
use Validator;

class BrandKitApiController extends Controller
{

    function updateBrandKit(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $id = $request->get('id');
            $uid = $request->get('user_id');
            $brand_logo = $request->file('brand_logo');
            $profile_pic = $request->file('profile_pic');
            $name = $request->get('name');
            $business_name = $request->get('business_name');
            $business_designation = $request->get('business_designation');
            $business_tagline = $request->get('business_tagline');
            $primary_number = $request->get('primary_number');
            $secondary_number = $request->get('secondary_number');
            $email = $request->get('email');
            $website = $request->get('website');
            $address = $request->get('address');
            $facebook = $request->get('facebook');
            $facebook_url = $request->get('facebook_url');
            $linkedin = $request->get('linkedin');
            $linkedin_url = $request->get('linkedin_url');
            $instagram = $request->get('instagram');
            $instagram_url = $request->get('instagram_url');
            $twitter = $request->get('twitter');
            $twitter_url = $request->get('twitter_url');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $exists = UserData::where('uid', $uid)->first();
            if($exists == null) {
                $response['success'] = 0;
                $response['message'] = 'User not found';
                return $response;
            }

            $res = BrandKit::find($id);

            if($res == null) {
                $response['success'] = 0;
                $response['message'] = 'Brandkit not found';
                return $response;
            }

            $res->user_id = $uid;
            $res->name = $name;
            $res->business_name = $business_name;
            $res->business_designation = $business_designation;
            $res->business_tagline = $business_tagline;
            $res->primary_number = $primary_number;
            $res->secondary_number = $secondary_number;
            $res->email = $email;
            $res->website = $website;
            $res->address = $address;
            $res->facebook = $facebook;
            $res->facebook_url = $facebook_url;
            $res->linkedin = $linkedin;
            $res->linkedin_url = $linkedin_url;
            $res->instagram = $instagram;
            $res->instagram_url = $instagram_url;
            $res->twitter = $twitter;
            $res->twitter_url = $twitter_url;

            if($brand_logo != null) {
                $validator = Validator::make($request->all(), [
                    'brand_logo' => ['image', 'mimes:jpeg,png,jpg'],
                ]);

                if ($validator->fails()) {
                    $response['success'] = 0;
                    $response['message'] = 'Only PNG or JPG allowed.';
                    return $response;
                }

                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $brand_logo->getClientOriginalExtension();
                $brand_logo->storeAs('uploadedFiles/brandKit', $new_name, 'public'); 

                try {
                    unlink(storage_path("app/public/".$res->brand_logo));
                } catch (\Exception $e) {
                }

                $res->brand_logo = 'uploadedFiles/brandKit/'. $new_name;
            } 

            if($profile_pic != null) {
                $validator = Validator::make($request->all(), [
                    'profile_pic' => ['image', 'mimes:jpeg,png,jpg'],
                ]);

                if ($validator->fails()) {
                    $response['success'] = 0;
                    $response['message'] = 'Only PNG or JPG allowed.';
                    return $response;
                }

                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $profile_pic->getClientOriginalExtension();
                $profile_pic->storeAs('uploadedFiles/brandKit', $new_name, 'public'); 

                try {
                    unlink(storage_path("app/public/".$res->profile_pic));
                } catch (\Exception $e) {
                }
                
                $res->profile_pic = 'uploadedFiles/brandKit/'. $new_name;
            } 

            $res->save();

            $response['success'] = 1;
            $response['message'] = "Update Successfully.";
            $jsonObject = Brandkit::find($id);
            $jsonObject['url'] = env('APP_URL', '') . "/";
            $response['datas'] = $jsonObject;
            return $response;

        }
    }

    function getBrandKit(Request $request) {
        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $uid = $request->get('user_id');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $userData = UserData::where('uid', $uid)->first();
            if($userData == null) {
                $response['success'] = 0;
                $response['message'] = 'User not found';
            } else {
                $res = Brandkit::where('user_id', $uid)->first();
                if($res == null) {
                    $res = new Brandkit();
                    $res->user_id = $userData->uid;
                    $res->name = $userData->name;
                    $res->email = $userData->email;
                    if($userData->number != null) {
                        $res->primary_number = $userData->country_code." ".$userData->number;
                    }
                    $res->save();
                }

                $jsonObject = Brandkit::find($res->id);
                $jsonObject['url'] = env('APP_URL', '') . "/";
                $jsonObject['website_url'] = $jsonObject->website;
                $response['success'] = 1;
                $response['datas'] = $jsonObject;
            }
            
            return $response;
        }
    }

    function deleteBrandImage(Request $request) {

        if ($request->isMethod('get')) {
            $response['type'] = 'error';
            $response['message'] = 'error!';
            return $response;
        } else {
            $key = $request->get('key');
            $id = $request->get('id');
            $uid = $request->get('user_id');
            $path = $request->get('path');
            $type = $request->get('type');

            $key_table = DB::table('key_table')->where("id", "1")->first();

            if ($key != $key_table->android_key) {
                $response['success'] = 0;
                $response['message'] = 'Incorrect Data!';
                return $response;
            }

            $exists = UserData::where('uid', $uid)->first();
            if($exists == null) {
                $response['success'] = 0;
                $response['message'] = 'User not found';
                return $response;
            }

            $res = Brandkit::find($id);
            if($res == null) {
                $response['success'] = 0;
                $response['message'] = 'Order not found';
                return $response;
            }

            if($uid != $res->user_id) {
                $response['success'] = 0;
                $response['message'] = 'Action is invalid.';
                return $response;
            }

            try {
                unlink(storage_path("app/public/".$path));
                if($type == 1) {
                    $res->brand_logo = null; 
                } else {
                    $res->profile_pic = null;
                }
                
                $res->save();
                $response['success'] = 1;
                $response['message'] = "Delete Successfully.";
            } catch (\Exception $e) {
                $response['success'] = 0;
                $response['message'] = "Unknown error occured.";
            }
            
            return $response;
        }
    }
    
}
