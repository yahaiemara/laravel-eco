<?php

namespace App\Http\Controllers;

use App\Models\Brands;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Slide;
use App\Models\Transaction;

class AdminController extends Controller
{
    public function index()
    {
        $order=Order::orderBy('created_at','desc')->get()->take(10);
        $dashboardDatas = DB::select("Select sum(total) As TotalAmount,
sum(if(status='ordered',total,0)) As TotalOrderedAmount,
sum(if(status='delivered',total,0)) As TotalDeliveredAmount,
sum(if(status='canceled',total,0)) As TotalCanceledAmount,
Count(*) As Total,
sum(if(status='ordered',1,0)) As TotalOrdered,
sum(if(status='delivered',1,0)) As TotalDelivered,
sum(if(status='canceled',1,0)) As TotalCanceled
From Orders
");
$monthlyDatas = DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
    IFNULL(D.TotalAmount, 0) AS TotalAmount,
    IFNULL(D.TotalOrderedAmount, 0) AS TotalOrderedAmount,
    IFNULL(D.TotalDeliveredAmount, 0) AS TotalDeliveredAmount,
    IFNULL(D.TotalCanceledAmount, 0) AS TotalCanceledAmount
FROM month_names M
LEFT JOIN (
    SELECT DATE_FORMAT(created_at, '%b') AS MonthName,
           MONTH(created_at) AS MonthNo,
           SUM(total) AS TotalAmount,
           SUM(IF(status = 'ordered', total, 0)) AS TotalOrderedAmount,
           SUM(IF(status = 'delivered', total, 0)) AS TotalDeliveredAmount,
           SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
    FROM Orders
    WHERE YEAR(created_at) = YEAR(NOW())
    GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
    ORDER BY MONTH(created_at)
) D ON D.MonthNo = M.id");
$AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
$OrderedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
$DeliveredAmountM = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
$CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());
$TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
$TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
$TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
$TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');



        return view("admin.index",compact("order","dashboardDatas",'AmountM','OrderedAmountM','DeliveredAmountM','CanceledAmountM','TotalAmount','TotalOrderedAmount','TotalDeliveredAmount','TotalCanceledAmount'));
    }
    public function brands()
    {
        $brand = Brands::orderBy(column: "id", direction: "DESC")->paginate(10);
        return view("admin.brand", compact("brand"));
    }
    public function brand_add()
    {
        return view("admin.brand_add");
    }
    public function brandstore(Request $request)
    {
        // Validation
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'slug' => 'required|string|unique:brands,slug|max:255',
        //     'image' => 'required|mimes:jpg,jpeg,png|max:2048',
        // ]);
        $brand = new Brands();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        // dd($brand);
        $image = $request->file("image");
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->generateThumbnailImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brand')->with('success', 'Brand has been successfully saved!');
    }
    public function brand_edit($id)
    {
        $brand = Brands::find($id);
        return view('admin.brand_edit', compact('brand'));
    }

    public function brandupdate(Request $request, $id)
    {
        $brand = Brands::find($id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('upload/brand') . '/' . $request->image)) {
                File::delete(public_path('upload/brand') . '/' . $request->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->generateThumbnailImage($image, $file_name);
            $brand->image = $file_name;
        }
        $brand->save();
        return redirect()->route('admin.brand')->with('success', 'Brand has been successfully Update!');
    }

    public function brand_delete($id)
    {
        $brand = Brands::find($id);
        if (File::exists(public_path('upload/brand') . '/' . $brand->image)) {
            File::delete(public_path('upload/brand') . '/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brand')->with('success', 'Brand has been successfully Deleted!');
    }

    public function category()
    {
        $categories = Category::orderBy('id', 'asc')->paginate(10);
        return view('admin.category', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category_add');
    }
    public function category_store(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->generateCategoryImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.category')->with('success', 'Categories has been successfully saved!');
    }
    public function generateCategoryImage($image, $imageName)
    { {
            $destinationPath = public_path('upload/category');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Resize and save the image
            $img = Image::make($image->path());
            $img->resize(124, 124, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $imageName);
        }
    }
    public function category_delete($id)
    {
        $cateogry = Category::find($id);
        if (File::exists(public_path('upload/category') . '/' . $cateogry->image)) {
            File::delete(public_path('upload/cateogry') . '/' . $cateogry->image);
        }
        $cateogry->delete();
        return redirect()->route('admin.category')->with('success', 'Category has been successfully Deleted!');
    }
    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category_edit', compact('category'));
    }
    public function category_update(Request $request, $id)
    {
        $category = Category::find($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('upload/category') . '/' . $request->image)) {
                File::delete(public_path('upload/category') . '/' . $request->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->generateThumbnailImage($image, $file_name);
            $category->image = $file_name;
        }
        $category->save();
        return redirect()->route('admin.category')->with('success', 'Category has been successfully Updated!');
    }
    public function generateThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('upload/brand');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        // Resize and save the image
        $img = Image::make($image->path());
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }
    public function product()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.product', compact('products'));
    }
    public function product_add()
    {
        $category = Category::select('id', 'name')->orderBy('name')->get();
        $brand = Brands::select('id', 'name')->orderBy('name')->get();
        return view('admin.product_add', compact('category', 'brand'));
    }
    public function product_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'category_id' => 'required',
            'brand_id' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'quantity' => 'required',
            'stock_status' => 'required',
            'featured' => 'required'
        ]);
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->quantity = $request->quantity;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $current_timestamp = Carbon::now()->timestamp;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->generateCategoryImageProduct($image, $imageName);
            $product->image = $imageName;
        }
        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            $allowedFileExtensions = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension(); // الحصول على الامتداد
                if (in_array($extension, $allowedFileExtensions)) { // التحقق من الامتداد المسموح
                    $fileName = $current_timestamp . '-' . $counter . '.' . $extension; // إنشاء اسم فريد للصورة
                    $this->generateCategoryImageProduct($file, $fileName);
                    array_push($gallery_arr, $fileName); // إضافة اسم الصورة إلى المصفوفة
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images = $gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('success', value: 'Product has been successfully Saved!');
    }
    public function generateCategoryImageProduct($image, $imageName)
    {
        $destinationThumbnails = public_path('upload/product/thumbnails');
        $destinationPath = public_path('upload/product');

        $img = Image::make($image->path());

        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);

        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationThumbnails . '/' . $imageName);
    }
    public function product_edit($id)
    {
        $product = Product::findOrFail($id);
        $category = Category::select('id', 'name')->orderBy('name')->get();
        $brand = Brands::select('id', 'name')->orderBy('name')->get();
        return view('admin.product_edit', compact('product', 'category', 'brand'));
    }
    public function product_update(Request $request, $id)
    {
        // $request->validate([
        //     'name'=> 'required',
        //     'description'=> 'required',
        //     'slug'=> 'required|unique:products,slug',
        //     'short_description'=> 'required',
        //     'category_id'=> 'required',
        //     'brand_id'=> 'required',
        //     'image'=> 'required|mimes:png,jpg,jpeg|max:2048',
        //     'regular_price'=> 'required',
        //     'sale_price'=> 'required',
        //     'SKU'=> 'required',
        //     'quantity'=> 'required',
        //     'stock_status'=>'required',
        //     'featured'=>'required'
        //    ]);
        $product = Product::findOrFail($id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->quantity = $request->quantity;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $current_timestamp = Carbon::now()->timestamp;
        if ($request->hasFile('image')) {
            if (File::exists(public_path('upload/product') . '/' . $product->image)) {
                File::delete(public_path('upload/product') . '/' . $product->image);
            }
            if (File::exists(public_path('upload/product/thumbnails') . '/' . $product->image)) {
                File::delete(public_path('upload/product/thumbnails') . '/' . $product->images);
            }
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->generateCategoryImageProduct($image, $imageName);
            $product->image = $imageName;
        }
        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            foreach (explode(',', $product->images) as $ofile) {
                if (File::exists(public_path('upload/product') . '/' . $ofile)) {
                    File::delete(public_path('upload/product') . '/' . $ofile);
                }
                if (File::exists(public_path('upload/product/thumbnails') . '/' . $ofile)) {
                    File::delete(public_path('upload/product/thumbnails') . '/' . $ofile);
                }
            }
            $allowedFileExtensions = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension(); // الحصول على الامتداد
                if (in_array($extension, $allowedFileExtensions)) { // التحقق من الامتداد المسموح
                    $fileName = $current_timestamp . '-' . $counter . '.' . $extension; // إنشاء اسم فريد للصورة
                    $this->generateCategoryImageProduct($file, $fileName);
                    array_push($gallery_arr, $fileName); // إضافة اسم الصورة إلى المصفوفة
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images = $gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('success', value: 'Product has been Updated Successfully');
    }
    public function product_delete($id)
    {
        $product = Product::find($id);
        if (File::exists(public_path('upload/product') . '/' . $product->image)) {
            File::delete(public_path('upload_product') . '/' . $product->image);
        }
        if (File::exists(public_path('upload/product/thumbnails') . '/' . $product->image)) {
            File::delete(public_path('upload/product/thumbnails') . '/' . $product->image);
        }


        foreach (explode(',', $product->images) as $ofile) {
            if (File::exists(public_path('upload/product') . '/' . $ofile)) {
                File::delete(public_path('upload/product') . '/' . $ofile);
            }
            if (File::exists(public_path('upload/product/thumbnails') . '/' . $ofile)) {
                File::delete(public_path('upload/product/thumbnails') . '/' . $ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('success', value: 'Product has been Deleted Successfully');
    }
    public function coupon()
    {
        $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(10);
        return view('admin.coupon', compact('coupons'));
    }
    public function coupon_add()
    {
        return view('admin.coupon-add');
    }
    public function coupon_store(Request $request)
    {
        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('coupon.index')->with('success', 'Coupon add Successfully!');
    }
    public function coupon_edit($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit', compact('coupon'));
    }
    public function coupon_update(Request $request)
    {
        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('coupon.index')->with('success', 'Coupon Update Successfully!');
    }
    public function coupon_delete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('coupon.index')->with('success', 'Coupon Delete Successfully!');
    }
    public function order(){
        $orders=Order::orderBy('created_at','desc')->paginate(12);
        return view ('admin.order', compact('orders'));
    }
    public function order_details($id){
        $order=Order::find($id);
        $orderitem=OrderItem::where('order_id',$id)->orderBy('id')->paginate(10);
       $transaction=Transaction::where('order_id',$id)->first();
        return view('admin.order-details',compact('order','orderitem','transaction'));
    }
    public function order_status(Request $request){
    $order=Order::where('id',$request->order_id)->first();
    $order->status=$request->status;
    if($request->status== 'delivered'){
        $order->delivered_date=Carbon::now();
    }else if($request->status== 'canceled'){
        $order->canceled_date=Carbon::now();
    }
    $order->save();
    if($request->status=='delivered'){
    $transaction=Transaction::where('order_id',$request->order_id)->first();
    $transaction->status='approved';
         $transaction->save();
        }
        return back()->with('success','Order Status has been Updated Successfully');
    }
    public function slide(){
        $slide=Slide::orderBy('id','desc')->paginate(12);
        return view('admin.slide',compact('slide'));
    }
    public function slide_add(){
        return view('admin.add-slide');
    }
  public function slide_store(Request $request){
    $request->validate([
    'tagline'=>'required',
    'title'=> 'required',
    'subtitle'=> 'required',
    'link'=> 'required',
    'image'=> 'required',
    ]);
    $slide=new Slide();
    $slide->title=$request->title;
    $slide->tagline=$request->tagline;
    $slide->subtitle=$request->subtitle;
    $slide->link=$request->link;
    $slide->status=$request->status;

    $image = $request->file('image');
    $file_extention = $request->file('image')->extension();
    $file_name = Carbon::now()->timestamp . '.' . $file_extention;
    $this->generateSlideThumbnailImage($image, $file_name);
    $slide->image = $file_name;
    $slide->save();
    return redirect()->route('admin.slider')->with('success','Add Slide Successfully');
  }

    public function generateSlideThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('upload/slide');
        $destinationPath2=public_path('upload/slide/thumbnails');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $img = Image::make($image->path());
        // Resize and save the image
        $img->cover(400, 690,"top");
        $img->resize(400, 690, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
        
        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath2 . '/' . $imageName);
    }
    public function slide_edit($id){
        $slide=Slide::find($id);
        return view('admin.edit-slide',compact('slide'));
    }
    public function slide_update(Request $request,$id){
        $request->validate([
        'tagline'=>'required',
        'title'=> 'required',
        'subtitle'=> 'required',
        'link'=> 'required',
        ]);
        $slide=Slide::find($id);
        $slide->title=$request->title;
        $slide->tagline=$request->tagline;
        $slide->subtitle=$request->subtitle;
        $slide->link=$request->link;
        $slide->status=$request->status;
        if($request->hasFile('image')){
        if(File::exists(public_path('upload/slide').'/'.$slide->image)){
            File::delete(public_path('upload/slide').'/'.$slide->image);
        }
        if(File::exists(public_path('upload/slide/thumbnails').'/'.$slide->image)){
            File::delete(public_path('upload/slide/thumbnails').'/'.$slide->image);
        }
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->generateSlideThumbnailImage($image, $file_name);
        $slide->image = $file_name;
     }
        $slide->save();
        return redirect()->route('admin.slider')->with('success','Add Slide Successfully');
      }
      public function destroy($id)
      {
          $slide = Slide::find($id);
      
          if (!$slide) {
              return redirect()->route('admin.slider')->with('error', 'Slide not found');
          }
      
          if (File::exists(public_path('upload/slide') . '/' . $slide->image)) {
              File::delete(public_path('upload/slide') . '/' . $slide->image);
          }
          if (File::exists(public_path('upload/slide/thumbnails') . '/' . $slide->image)) {
              File::delete(public_path('upload/slide/thumbnails') . '/' . $slide->image);
          }
      
          $slide->delete();
          return redirect()->route('admin.slider')->with('success', 'Slide Deleted Successfully');
      }
      public function contact(){
        $contacts=Contact::orderBy('created_at','desc')->paginate(10);
        return view('admin.contact',compact('contacts'));
      }
      public function contact_delete($id){
        $contact=Contact::find($id);
        $contact->delete();
        return redirect()->route('admin.contact')->with('success','Contact has been Deleted Successfully');
      }
      
}

