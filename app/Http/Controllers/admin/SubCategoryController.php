<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index(Request $req){
        $subCategories = SubCategory::latest('id');
        $categories = Category::query();
        if(!empty($req->get('keyword'))){
            $subCategories->where('name', 'like', '%'.$req->get('keyword').'%');
            $categories->orWhere('name', 'like', '%'.$req->get('keyword').'%');
            }
            $categories = Category::pluck('name','id');
        $subCategories = $subCategories->paginate(10);
        return view('admin.sub_category.list', compact('subCategories', 'categories'));
    }

    public function create(){
        $categories = Category::orderBy('name','ASC')->get();
        return view('admin.sub_category.create', compact('categories'));
     }

    public function store(Request $req){
        $validator = Validator::make($req->all(),[ 
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required',
        ]);
        if($validator->passes()){
            $subCategory = new SubCategory();
            $subCategory->name = $req->name;
            $subCategory->slug = $req->slug;
            $subCategory->status = $req->status;
            $subCategory->category_id = $req->category;
            $subCategory->save();

            $req->session()->flash('success', 'Sub Category created successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category created successfully.',
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit(Request $req, $id){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $req->session()->flash('error', 'Record not found');
            return redirect()->route('sub-categories.index');
        }
        $categories = Category::orderBy('name','ASC')->get();
        return view('admin.sub_category.edit', compact('subCategory', 'categories'));
    }

    public function update(Request $req, $id){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
             $req->session()->flash('error', 'Record not found.');
            return response([
                'status' => false,
                'message' => 'Record not found',
            ]);
        }

        $validator = Validator::make($req->all(),[ 
            'name' => 'required',
            'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
            'category' => 'required',
            'status' => 'required',
        ]);
        if($validator->passes()){
            $subCategory->name = $req->name;
            $subCategory->slug = $req->slug;
            $subCategory->status = $req->status;
            $subCategory->category_id = $req->category;
            $subCategory->save();

            $req->session()->flash('success', 'Sub Category updated successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category updated successfully.',
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    function destroy($id, Request $req){
        $subCategory = SubCategory::find($id);
        if(!$subCategory){
            $req->session()->flash('error', 'Record not found.');
            return response([
                'status' => false,
                'notFound' => true,
            ]);
        }
        $subCategory->delete();
        $req->session()->flash('success', 'Sub Category deleted successfully.');
        return response([
            'status' => true,
            'message' => 'Sub Category deleted successfully.'
        ]);
    }
}
