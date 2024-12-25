<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use App\Models\Coupon;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
  public function index()
  {
    $items = Cart::instance('cart')->content();
    return view('cart', compact('items'));
  }
  public function cart_add(Request $request)
  {
    Cart::instance('cart')->add($request->id, $request->name, $request->quantity, $request->price)->associate('App\Models\Product');
    return redirect()->back();
  }
  public function cart_increase($rowId)
  {
    $product = Cart::instance('cart')->get($rowId);
    $qty = $product->qty + 1;
    Cart::instance('cart')->update($rowId, $qty);
    return redirect()->back();
  }
  public function cart_decrease($rowId)
  {
    $product = Cart::instance('cart')->get($rowId);
    $qty = $product->qty - 1;
    Cart::instance('cart')->update($rowId, $qty);
    return redirect()->back();
  }
  public function cart_remove($rowId)
  {
    Cart::instance('cart')->remove($rowId);
    return redirect()->back();
  }
  public function cart_delete()
  {
    Cart::instance('cart')->destory();
    return redirect()->back();
  }
  public function coupon_apply(Request $request)
  {
    $coupon_code = $request->coupon_code;
    if (isset($coupon_code)) {
      $coupon = Coupon::where('code', $coupon_code)->where('expiry_date', '>=', Carbon::today())
        ->where('cart_value', '<=', Cart::instance('cart')->subtotal())->first();
      if (!$coupon) {
        return redirect()->back()->with('error', 'Coupon code is invalid');
      } else {
        Session::put('coupon', [
          'code' => $coupon->code,
          'type' => $coupon->type,
          'value' => $coupon->value,
          'cart_value' => $coupon->cart_value,
        ]);
        $this->clacuate_discount();
        return redirect()->back()->with('success', 'Coupon code is applied');
      }
    } else {
      return redirect()->back()->with('error', 'Please enter a coupon code');
    }
  }
  public function clacuate_discount()
  {
    $discount = 0;
    if (Session::has('coupon')) {
      if (session::get('coupon')['type'] == 'fixed') {
        $discount = session::get('coupon')['value'];
      } else {
        $discount = Cart::instance('cart')->subtotal() * (session::get('coupon')['value'] / 100);
      }
      $subtotal = Cart::instance('cart')->subtotal() - $discount;
      $tax = $subtotal * config('cart.tax') / 100;
      $total = $subtotal + $tax;
      Session::put('discounts', [
        'discount' => number_format(floatval($discount), 2, '.', ''),
        'subtotal' => number_format(floatval($subtotal), 2, '.', ''),
        'tax' => number_format(floatval($tax), 2, '.', ''),
        'total' => number_format(floatval($total), 2, '.', ''),
      ]);
    }
  }
  public function coupon_remove()
  {
    Session::forget('coupon');
    Session::forget('discounts');
    return redirect()->back()->with('success', 'Coupon code is removed');
  }
  public function checkout()
  {
    if (!Auth::check()) {
      return redirect()->route('login');
    } else {
      $address = Address::where('user_id', Auth::user()->id)
        ->where('isdefault', 1)->first();
      return view('checkout', compact('address'));
    }
  }
  public function place_an_order(Request $request)
  {
    $user = Auth::user()->id;
    $address = Address::where('user_id', $user)->where('isdefault', true)->first();
    if (!$address) {
      $request->validate([
        'name' => 'required',
        'phone' => 'required',
        'zip' => 'required',
        'state' => 'required',
        'city' => 'required',
        'address' => 'required',
        'locality' => 'required',
        'landmark' => 'required',
      ]);
      $address = new Address();
      $address->user_id = $user;
      $address->name = $request->name;
      $address->phone = $request->phone;
      $address->zip = $request->zip;
      $address->state = $request->state;
      $address->city = $request->city;
      $address->address = $request->address;
      $address->locality = $request->locality;
      $address->landmark = $request->landmark;
      $address->country = 'india';
      $address->isdefault = true;
      $address->save();
    }
    $this->setAmountforCheckout();
    $order = new Order();
    $order->user_id = $user;
    $order->discount = Session::get('checkout')['discount'];
    $order->subtotal = Session::get('checkout')['subtotal'];
    $order->tax = Session::get('checkout')['tax'];
    $order->total = Session::get('checkout')['total'];
    $order->name = $address->name;
    $order->phone = $address->phone;
    $order->zip = $address->zip;
    $order->state = $address->state;
    $order->city = $address->city;
    $order->address = $address->address;
    $order->locality = $address->locality;
    $order->landmark = $address->landmark;
    $order->country = $address->country;
    $order->save();
    foreach (Cart::instance('cart')->content() as $item) {
      $orderitem = new OrderItem();
      $orderitem->order_id = $order->id;
      $orderitem->product_id = $item->id;
      $orderitem->quantity = $item->qty;
      $orderitem->price = $item->price;
      $orderitem->save();
    }
    if ($request->mode == 'card') {
      // 
    } else if ($request->mode == 'paypal') {
      //
    } else if ($request->mode == 'cod') {
      $transaction = new Transaction();
      $transaction->user_id = $user;
      $transaction->order_id = $order->id;
      $transaction->mode = $request->mode;
      $transaction->status = 'pending';
      $transaction->save();
    }
    Cart::instance('cart')->destroy();
    Session::forget('checkout');
    Session::forget('coupon');
    Session::forget('discounts');
    Session::put('order_id', $order->id);
    return redirect()->route('order.confirmation');
  }
  public function setAmountforCheckout()
  {
    if (!Cart::instance('cart')->count() > 0) {
      Session::forget('checkout');
      return;
    }
    if (Session::has('coupon')) {
      Session::put('checkout', [
        'discount' => Session::get('discounts')['discount'],
        'subtotal' => Session::get('discounts')['subtotal'],
        'tax' => Session::get('discounts')['tax'],
        'total' => Session::get('discounts')['total'],
      ]);
    } else {
      Session::put('checkout', [
        'discount' => 0,
        'subtotal' => Cart::instance('cart')->subtotal(),
        'tax' => Cart::instance('cart')->tax(),
        'total' => Cart::instance('cart')->total(),
      ]);
    }
  }
  public function order_confirmation(){
    if(Session::has('order_id')){
      $order = Order::find(Session::get('order_id'));
      return view('order-confirmation',compact('order'));
    } else {
      return redirect()->route('cart.index');
    }
  }
}
