<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index(Request $req){
        $brands = Brand::latest();
        if($req->get('keyword')){
            $brands = $brands->where('name','like','%'.$req->keyword.'%');
        }
        $brands = $brands->paginate(3)->appends($req->all());
        return view('admin.brands.list', compact('brands'));
    }

    public function create(){
        return view('admin.brands.create');
    }

    public function store(Request $req){
        $validator = Validator::make($req->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands',
        ]);

        if($validator->passes()){
            $brand = new Brand();
            $brand->name = $req->name;
            $brand->slug = $req->slug;
            $brand->status = $req->status;
            $brand->save();
            $req->session()->flash('success', 'Brand created successfully.');
            return response()->json([
                'status' => true,
                'message' => 'brand created successfully.',
            ]);
        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id){
        $brand = Brand::find($id);
         if(empty($brand)){
            $req->session()->flash('error', 'Record not found.');
            return redirect()->route('brands.index');
        }
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $req, $id){
        $brand = Brand::find($id);
        if(empty($brand)){
            $req->session()->flash('error', 'Record not found.');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }
        $validator = Validator::make($req->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$brand->id.',id',
            'status' => 'required',
        ]);

        if($validator->passes()){
            $brand->name = $req->name;
            $brand->slug = $req->slug;
            $brand->status = $req->status;
            $brand->save();

            $req->session()->flash('success', 'Brand updated successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Brand updates successfully.'
            ]);
        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

    }
    public function destroy(){
        
    }

}
