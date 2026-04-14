<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(){
        return view('admin.product.list');
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
}
