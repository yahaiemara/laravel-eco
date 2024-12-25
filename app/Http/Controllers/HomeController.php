<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Contact;

class HomeController extends Controller
{
 

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $slides=Slide::where("status","1")->take(3)->get();
       $categories=Category::orderBy("name")->get();
       $products=Product::whereNotNull('sale_price')->where('sale_price','<>','')->inRandomOrder()->take(8)->get();
       $fproducts=Product::where('featured','1')->take(8)->get();
       return view('layouts.index',compact('slides','categories','products','fproducts'));
    }
    public function contact()
    {
        return view('contact');
    }
    public function contact_store(Request $request){
        $contact=new Contact();
        $contact->name=$request->name;
        $contact->email=$request->email;
        $contact->phone=$request->phone;
        $contact->comment=$request->comment;
        $contact->save();
        return redirect()->back()->with('success','Your message has been sent successfully');
    }
   public function search(Request $request){
    $query=$request->input('query');
    $search=Product::where('name','like',"%$query%")->get()->take(8);
    return response()->json($search);
   }

}
