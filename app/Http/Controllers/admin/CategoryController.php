<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    public function index(Request $request){
        $categories = Category::latest();
        if(!empty($request->get('keyword'))){
            $categories->where('name', 'like', '%'.$request->get('keyword').'%');
        }
        $categories = $categories->paginate(10);
        return view('admin.category.list', compact('categories'));
    }

    public function create(){
       return view('admin.category.create');
    }

    public function store(Request $request){
       $validator = Validator::make($request->all(),[
           'name' => 'required',
           'slug' => 'required|unique:categories',
       ]);
       if($validator->passes()){
           $category = new Category();
           $category->name = $request->name;
           $category->slug = $request->slug;
           $category->status = $request->status;
           $category->showHome = $request->showHome;
           $category->save();

           // Save Image Here......
          if(!empty($request->image_id)){
    $tempImage = TempImage::find($request->image_id);

    if($tempImage != null){

        $extArray = explode('.',$tempImage->name);
        $ext = last($extArray);

        $newImageName = $category->id.'.'.$ext;

        $sPath = public_path('temp/'.$tempImage->name);
        $dPath = public_path('uploads/category/'.$newImageName);

        if(File::exists($sPath)){

            File::copy($sPath, $dPath);

            // Thumbnail
            $thumbPath = public_path('uploads/category/thumb/'.$newImageName);

            $manager = new ImageManager(new Driver());
            $image = $manager->read($sPath);

            $image->fit(450, 600, function($constraint){
                $constraint->upsize();
            });

            $image->save($thumbPath);

            $category->image = $newImageName;
            $category->save();
        }
    }
}

           $request->session()->flash('success', 'Category added successfully.');
           return response()->json([
            'status' => true,
            'message' => 'Category added successfully.'
           ]);
       }else{
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
       }
    }

    public function edit($id){
        $category =Category::find($id);
        if(empty($id)){
            return redirect()->route('categories.index');
        }
         return view('admin.category.edit', compact('category'));
    }

    public function update(Request $request, $id){
        $category = Category::find($id);
        if(empty($id)){
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found.',
            ]);
        }
        $validator = Validator::make($request->all(),[
            'name' => 'required',
          'slug' => 'required|unique:categories,slug,'.$id,

        //    'slug' => 'required|unique:categories,slug,'.$category->id.',id',

        //    'slug' => [
        //       'required',
        //        Rule::unique('categories')->ignore($id),
        //     ]
        ]);
        if($validator->passes()){
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
           $category->showHome = $request->showHome;            
            $category->save();
            $oldImage = $category->image;

            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'-'.time().'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                $thumbPath = public_path('uploads/category/thumb/'.$newImageName);
                $manager = new ImageManager(new Driver());
                $image = $manager->read($sPath);
                // $image->resize(450,600);
                $image->fit(450, 600, function($constraint){
                    $constraint->upsize();
                });
                $image->save($thumbPath);

                //Delete old IMages
                File::delete(public_path().'/uploads/category/'.$oldImage);
                File::delete(public_path().'/uploads/category/thumb/'.$oldImage);
            }
        $request->session()->flash('success', 'Category updated successfully.');

        return response()->json([
            'status' => true,
            'message' => 'Category added successfully.',
        ]);
        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $request, $id){
          $category= Category::find($id);
          if(empty($category)){
            return redirect()->back();
          }
          File::delete(public_path().'/uploads/category/thumb/'.$category->image);
          File::delete(public_path().'/uploads/category/'.$category->image);

          $category->delete();
          $request->session()->flash('success', 'Category deleted successfully.');

          return response()->json([
            'status'=> true,
            'message' => 'Category deleted successfully,',
          ]);
    }
}
