@extends('admin.layouts.app')

@section('content')
    <section class="content-header">					
					<div class="container-fluid my-2">
						<div class="row mb-2">
							<div class="col-sm-6">
								<h1>Edit Sub Category</h1>
							</div>
							<div class="col-sm-6 text-right">
								<a href="{{route('sub-categories.index')}}" class="btn btn-primary">Back</a>
							</div>
						</div>
					</div>
					<!-- /.container-fluid -->
				</section>
				<!-- Main content -->
				<section class="content">
					<!-- Default box -->
					<div class="container-fluid">
                        <form action="" name="subCategoryForm" id="subCategoryForm">
						<div class="card">
							<div class="card-body">								
								<div class="row">
                                    <div class="col-md-12">
										<div class="mb-3">
											<label for="category">Category</label>
											<select name="category" id="category" class="form-control">
                                                @if($categories->isNotEmpty())
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                <option {{ ($subCategory->category_id == $category->id) ? 'selected': ''}} value="{{$category->id}}">
                                                    {{$category->name}}
                                                </option>
                                                @endforeach
                                                @endif
                                            </select>
                                            <p></p>
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<label for="name">Name</label>
											<input type="text" value="{{$subCategory->name}}" name="name" id="name" class="form-control" placeholder="Name">
                                            <p></p>	
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<label for="slug">Slug</label>
											<input type="text" value="{{old('slug',$subCategory->slug)}}" name="slug" id="slug" readonly class="form-control" placeholder="Slug">
                                            <p></p>	
										</div>
									</div>
                                    
                                    <div class="col-md-6">
										<div class="mb-3">
											<label for="status">Status</label>
											<select name="status" id="status" class="form-control">
                                                <option {{ ($subCategory->status == 1) ? 'selected' : ''}} value="1">Active</option>
                                                <option {{ ($subCategory->status == 0) ? 'selected' : ''}} value="0">Block</option>
                                            </select>
                                            <p></p>
										</div>
									</div>
								</div>
							</div>							
						</div>
						<div class="pb-5 pt-3">
							<button type="submit" class="btn btn-primary">Update</button>
							<a href="{{route('sub-categories.index')}}" class="btn btn-outline-dark ml-3">Cancel</a>
						</div>
                        </form>
					</div>
					<!-- /.card -->
				</section>

@endsection

@section('customJs')
    <script>
    $("#subCategoryForm").submit(function(e){
        e.preventDefault();
        a = $(this);
        $("button[type=submit]").prop('disabled', true);
        $.ajax({
            url: "{{route('sub-category.update', $subCategory->id)}}",
            type: 'post',
            data: a.serializeArray().concat({name: '_method', value: 'PUT'}),
            dataType: 'json',
            success: function(res){
                $("button[type=submit]").prop('disabled',  false);
                if(res.status == true){
                    window.location.href="{{route('sub-categories.index')}}";

                    $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    $("#slug").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    $("#category").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                } else{
                    if(res.message){
                        window.location.href="{{route('sub-categories.index')}}";
                        return false;
                    }
                    errors = res['errors']
                    if(errors['name']){
                        $("#name").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.name);
                    } else{
                        $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }

                    if(errors['slug']){
                        $("#slug").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.slug);
                    }else{
                        $("#slug").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }

                    if(errors['category']){
                        $("#category").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.category);
                    }else{
                        $("#category").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                }
            }, error: function(jqXHR, exception){
                console.log("Something went wrong.");
            }
        });
    });



    $("#name").change(function(){
        b = $(this);
        $("button[type=submit]").prop('disabled', true);
        $.ajax({
            url: '{{route("getSlug")}}',
            type: 'get',
            data: {title: b.val()},
            dataType: 'json',
            success: function(res){
                $("button[type=submit]").prop('disabled', false);
                if(res["status"] == true){
                    $("#slug").val(res["slug"]);
                }
            }
        });
    });
    </script>

@endsection
