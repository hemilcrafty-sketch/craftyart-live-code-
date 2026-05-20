<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use App\Models\UserData;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends AppBaseController
{

  public function index(Request $request): Factory|View|Application
  {
    $searchableFields = [['id' => 'id', 'value' => "id"], ['id' => 'promo_code', 'value' => "Promo Code"], ['id' => 'additional_day', 'value' => "Day"], ['id' => 'status', 'value' => "Status"], ['id' => 'expiry_date', 'value' => "Expire Date"]];
    $query = PromoCode::query();
    $promoCodes = $this->applyFiltersAndPagination($request, $query, $searchableFields);

    return view('promo_code.index', compact('promoCodes', 'searchableFields'));
  }


  public function store(Request $request): JsonResponse
  {
    // dd($request->all());
    $request->validate([
      'promo_code' => 'required|string',
      'disc' => 'required|numeric|min:0',
      'disc_upto_inr' => 'required|numeric|min:0'
    ]);

    $userIds = $request->user_id;

    // Check for duplicate promo code
    $existing = PromoCode::where('id', '!=', $request->id ?? 0)
      ->where('promo_code', $request->promo_code)
      ->first();

    if ($existing) {
      return response()->json(['error' => 'Promo code already exists!']);
    }


    $exceptData = ['_token', '_method', '/promocode'];
    if ($request->id) {
      $exceptData = ['_token', '_method', '/promocode', 'disc', 'disc_upto_inr', 'min_cart_inr', 'disc_upto_usd', 'min_cart_usd'];
    }


    $data = $request->except($exceptData);
    $data['user_id'] = $userIds ? json_encode($userIds) : null;

    // return response()->json(['error' => json_encode($data)]);

    $request->id ? PromoCode::where('id', $request->id)->update($data) : PromoCode::create($data);
    return response()->json(['success' => true, 'message' => $request->id ? 'Promo code Update successfully!' : 'Promo code added successfully!']);
  }


  public function destroy($id): JsonResponse
  {
    try {
      $promoCode = PromoCode::findOrFail($id);
      $promoCode['status'] = 0;
      $promoCode->save();
      //      $promoCode->delete();
      return response()->json([
        'message' => 'PromoCode has been deleted successfully.'
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'error' => 'Failed to delete: ' . $e->getMessage()
      ], 500);
    }
  }
  public function getUsersByEmail(Request $request): JsonResponse
  {
    $query = $request->input('q');
    $users = UserData::where(function ($q) use ($query) {
      $q->where('email', 'like', "%{$query}%")
        ->orWhere('id', 'like', "%{$query}%");
    })
      ->limit(100)
      ->get(['id', 'uid', 'email']);

    return response()->json(
      $users->map(fn($user) => ['id' => $user->uid, 'text' => "{$user->id} - {$user->email}"])
    );
  }

  public function getUsersByIds(Request $request): JsonResponse
  {
    $userIds = $request->input('user_ids', []);

    $users = UserData::whereIn('uid', $userIds)->get(['id', 'uid', 'email']);
    return response()->json($users);
  }

}