@extends('admin.layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Category</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{route('categories.index')}}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            <form action="" method="post" id="categoryForm" name="categoryForm">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" value="{{old('name', $category->name)}}" class="form-control" placeholder="Name">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <input type="text" value="{{$category->slug}}"  readonly name="slug" id="slug" class="form-control" placeholder="Slug">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image">Image</label>
                                <div id="image" class="dropzone dz-clickable">
                                    <div class="dz-message needsclick">
                                        <br>Drop files here or click to uplaod.<br><br>
                                    </div>
                                </div>
                                <input type="hidden" name="image_id" id="image_id" value="{{$category->image}}">
                            </div>
                            @if(!empty($category->image))
                            <div>
                                <img width="100" src="{{asset('uploads/category/thumb/'.$category->image)}}" alt="">
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option {{($category->status == 1) ? 'selected' : ''}}   value="1">Active</option>
                                    <option {{($category->status == 0 ? 'selected': '')}}     value="0">Block</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status">Show on Home</label>
                                <select name="showHome" id="showHome" class="form-control">
                                    <option value="No" {{($category->showHome == 'No') ? 'selected' : ''}}>No</option>
                                    <option value="Yes" {{ ($category->showHome == 'Yes') ? 'selected' : ''}}>Yes</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="pb-5 pt-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{route('categories.index')}}" class="btn btn-outline-dark ml-3">Cancel</a>
            </div>
            </form>
        </div>
        <!-- /.card -->
    </section>

@endsection

@section('customJs')
    <script>
        $("#categoryForm").submit(function(event){
            event.preventDefault();
            $("button[type=submit]").prop('disabled',true);
            var element = $(this);
            $.ajax({
                url: '{{route("categories.update", $category->id)}}',
                type: 'put',
                data: element.serializeArray(),
                dataType: 'json',
                success: function(response){
                $("button[type=submit]").prop('disabled', false);
                 if(response.status == true) {
                    window.location.href="{{route('categories.index')}}";

                      $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");

                       $("#slug").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                 } else{
                    var errors = response['errors']
                     if(errors['name']){
                        $("#name").addClass('is-invalid')
                        .siblings('p')
                        .addClass('invalid-feedback')
                        .html(errors['name']);
                     } else{
                        $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                     }

                     if(errors['slug']){
                        $("#slug").addClass('is-invalid')
                        .siblings('p')
                        .addClass('invalid-feedback')
                        .html(errors['slug']);
                     } else{
                        $("#slug").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                     }
                }
                }, error: function(jqXHR, exception){
                    console.log("Something went wrong.");
                }
            })
        })

        $("#name").change(function(){
            element = $(this) ;
            $("button[type=submit]").prop('disabled', true);
            $.ajax({
                url: '{{route("getSlug")}}',
                type: 'get',
                data: {title: element.val()},
                dataType: 'json',
                success: function(response){
                $("button[type=submit]").prop('disabled', false);
                     if(response["status"] == true){
                        $("#slug").val(response["slug"]);
                     }  
                }
            });
        });

    Dropzone.autoDiscover = false;
    const dropzone = new Dropzone("#image", {
    url: "{{ route('temp-images.create') }}",
    maxFiles: 1,
    paramName: 'image',
    addRemoveLinks: true,
    acceptedFiles: "image/jpeg,image/png,image/gif",
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    init: function() {
        @if(!empty($category->image))
            let mockFile = { 
                name: "{{ $category->image }}", 
                size: 12345 // approximate size
            };
            this.emit("addedfile", mockFile);
            this.emit("thumbnail", mockFile, "{{ asset('uploads/category/thumb/'.$category->image) }}?t={{ time() }}");
            this.emit("complete", mockFile);
            this.files.push(mockFile); // make Dropzone aware of this file

            // Hidden input for already uploaded image
            $("#image_id").val("{{ $category->image }}");
        @endif

        this.on('addedfile', function(file){
            if(this.files.length > 1){
                this.removeFile(this.files[0]); // keep only 1 file
            }
        });

        this.on('success', function(file, response){
            $("#image_id").val(response.image_id);
        });

        this.on('removedfile', function(file){
            $("#image_id").val('');
        });
    }
});

    </script>

@endsection



<!-- Dropzone.autoDiscover = false; const dropzone = $("#image").dropzone({ init: function(){ this.on('addedfile', function(file){ if(this.files.length > 1){ this.removefile(this.files[0]); } }); }, url: "{{route('temp-images.create')}}", maxFiles: 1, paramName: 'image', addRemoveLinks: true, acceptedFiles: "image/jpeg,image/png,image/gif", headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, success: function(file, response){ $("#image_id").val(response.image_id); } }); -->