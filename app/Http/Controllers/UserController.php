<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
public function index(){
    return view("user.index");
}
public function order(){
    $orders=Order::where("user_id",Auth::user()->id)->orderBy('created_at','desc')->paginate(10);
    return view ('user.order',compact('orders'));
}
public function order_detail($id){
    $order=Order::where('user_id',Auth::user()->id)->where('id',$id)->first();
    if($order){
        $orderitem=OrderItem::where('order_id',$id)->paginate(10);
        $transaction=Transaction::where('order_id',$id)->first();
    }else{
        return redirect()->route('login');
    }
    return view('user.order_detail',compact('order','orderitem','transaction'));
}
public function cancel_order(Request $request){
    $order=Order::find($request->order_id);
    $transaction=Transaction::where('order_id',$request->order_id)->first();
    $transaction->status="refunded";
    $transaction->save();
        $order->status="canceled";
        $order->canceled_date=Carbon::now();
        $order->save();
    return redirect()->back()->with('success','Order Cancelled Successfully');
}
}
