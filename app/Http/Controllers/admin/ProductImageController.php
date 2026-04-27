<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;

class ProductImageController extends Controller
{
     function update(Request $req){
        $image = $req->image;
        $ext = $image->getClientOriginalExtension();
        $sourcePath = $image->getPathName();

        $productImage = new ProductImage();
        $productImage->product_id = $req->product_id;
        $productImage->image = '';
        $productImage->save();

        $imageName = $req->product_id.'-'.$productImage->id.'-'.time().'.'.$ext;
        $productImage->image = $imageName;
        $productImage->save();

        $manager = new ImageManager(new Driver());
        $destPath = public_path().'/uploads/product/large/'.$imageName;
        $image = $manager->read($sourcePath);
        $image->scale(width: 1400);    
        $image->save($destPath);

        $destPath = public_path().'/uploads/product/small/'.$imageName;
        $image = $manager->read($sourcePath);
        $image->cover(300, 300);
        $image->save($destPath);

        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'ImagePath' => asset('uploads/product/small/'.$productImage->image),
            'message' => 'Image saved successfully.',
        ]);
     }

     function destroy(Request $req){
        $productImage = ProductImage::find($req->id);
        if(empty($productImage)){
            return response()->json([
                'status' => false,
                'message' => 'Image not found.',
            ]);
        }

        File::delete(public_path('uploads/product/large/'.$productImage->image));
        File::delete(public_path('uploads/product/small/'.$productImage->image));

        $productImage->delete();

        return response()->json([
            'status' => false,
            'message' => 'Image deleted successfully.',
        ]);
     }
}
 