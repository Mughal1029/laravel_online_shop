<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;


class ProductController extends Controller
{
    public function index(Request $request){
        $product = Product::latest('id')->with('product_images');
        if($request->get('keyword') != ""){
            $product = $product->where('title','like','%'.$request->keyword.'%');
        }
        $product = $product->paginate(10);
        $data['products'] = $product;
        return view('admin.product.list', $data);
    }

    public function create(){
        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        return view('admin.product.create', $data);
    }

    public function store(Request $req){
        // dd($req->image_array);
        // exit();
        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];
        if(!empty($req->track_qty) && $req->track_qty == 'Yes'){
            $rules['qty'] = 'required|numeric';
        }
        $validator = Validator::make($req->all(),$rules);

        if($validator->passes()){
            $product = new Product();
            $product->title = $req->title;
            $product->slug = $req->slug;
            $product->description = $req->description;
            $product->price = $req->price;
            $product->compare_price = $req->compare_price;
            $product->sku = $req->sku;
            $product->barcode = $req->barcode;
            $product->track_qty = $req->track_qty;
            $product->qty = $req->qty;
            $product->status = $req->status;
            $product->category_id = $req->category;
            $product->sub_category_id = $req->sub_category;
            $product->brand_id = $req->brand;
            $product->is_featured = $req->is_featured;
            $product->save();
               
            // Save Gallery Pics
            if (!empty($req->image_array)) {
                foreach ($req->image_array as $temp_image_id) {
                    $tempImageInfo = TempImage::find($temp_image_id);
                        if (!$tempImageInfo) {
                            continue;
                        }
                        
                // PHP function
                $ext = pathinfo($tempImageInfo->name, PATHINFO_EXTENSION);
                // laravel function
                //  $extArray = explode('.',$tempImageInfo->name);
                // $ext = last($extArray);

                // create product image row
                $productImage = new ProductImage();
                $productImage->product_id = $product->id;
                $productImage->image = '';
                $productImage->save();

                $imageName = $product->id . '-' . $productImage->id . '-' . time() . '.' . $ext;

                $productImage->image = $imageName;
                $productImage->save();

                $sourcePath = public_path('temp/' . $tempImageInfo->name);
                if (!file_exists($sourcePath)) {
                    continue; // 👈 skip missing file
                }
                $manager = new ImageManager(new Driver());

                //    LARGE IMAGE (KEEP RATIO)
                $largePath = public_path('uploads/product/large/' . $imageName);
                $image = $manager->read($sourcePath);
                $image->scale(width: 1400); // correct modern method
                $image->save($largePath);

                //    SMALL IMAGE (CROP SQUARE)
                $smallPath = public_path('uploads/product/small/' . $imageName);
                $image = $manager->read($sourcePath);
                $image->cover(300, 300); // perfect for thumbnails
                $image->save($smallPath);
            }
        }
            $req->session()->flash('success', 'Product created successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Product created successfully.',
            ]);
        } else{
            $req->session()->flash('error', '');
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    function edit($id){
        $data = [];
        $product = Product::find($id);
        if(empty($product)){
            return redirect()->route('product.index')->with('error', 'Product not found.');
        }
        $productImage = ProductImage::where('product_id',$product->id)->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['product'] = $product;
        $data['productImages'] = $productImage;
        $data['subCategories'] = $subCategories;
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        return view('admin.product.edit' ,$data);
    }

    function update($id, Request $req){
        $product = Product::find($id);
          $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,'.$product->id.',id',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,'.$product->id.',id',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
          ];

          if(!empty($req->track_qty) && $req->track_qty == 'Yes'){
            $rules['qty'] = 'required|numeric';
          }
          $validator = Validator::make($req->all(),$rules);
          if($validator->passes()){
            $product->title = $req->title;
            $product->slug = $req->slug;
            $product->description = $req->description;
            $product->price = $req->price;
            $product->compare_price = $req->compare_price;
            $product->sku = $req->sku;
            $product->barcode = $req->barcode;
            $product->track_qty = $req->track_qty;
            $product->qty = $req->qty;
            $product->status = $req->status;
            $product->category_id = $req->category;
            $product->sub_category_id = $req->sub_category;
            $product->brand_id = $req->brand;
            $product->is_featured = $req->is_featured;
            $product->save();

            $req->session()->flash('success', 'Product updated successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully.'
            ]);
          } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
          }
    }

    function destroy($id, Request $req){
        $product = Product::find($id);
        if(empty($product)){
            $req->session()->flash('error', 'Product not found.');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $productImages = ProductImage::where('product_id', $id)->get();
        if($productImages->isNotEmpty()){
            foreach($productImages as $productImage){
                File::delete(public_path('uploads/product/large/'.$productImage->image));
                File::delete(public_path('uploads/product/small/'.$productImage->image));
            }
            ProductImage::where('product_id',$id)->delete();
        }
        $product->delete();

       $req->session()->flash('success', 'Product deleted successfull.');
        return response()->json([
                'status' => true,
                'message' => 'Product deleted succssfully.',
            ]);

    }
}


