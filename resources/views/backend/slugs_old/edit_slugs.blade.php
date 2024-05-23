@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="container mt-5">
            <h2 class="text-center">Add Slug</h2>
            <form class="row g-3 needs-validation" novalidate action="{{url('/admin/update_slug/')}}/{{$slug->id}}" method="POST">
                @csrf

                <div class="col-md-2">
                    <label class="form-label">Product ID</label>
                    <input type="text" name="product_id" class="form-control" placeholder="Enter Product ID" value="{{$slug->product_id}}" required>
                    @error('product_id')
                    <p class="text-danger">
                        {{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Old SLug</label>
                    <input type="text" name="old_slug" class="form-control" placeholder="Elter Old SLug" value="{{$slug->old_slug}}" required>
                    @error('old_slug')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">New SLug</label>
                    <input type="text" name="new_slug" class="form-control" placeholder="Enter New SLug"  value="{{$slug->new_slug}}" required>
                    @error('new_slug')
                      <p class="text-danger">  {{ $message }}</p>
                    @enderror
                </div>


                <div class="col-12 my-2">
                    <button class="btn btn-primary float-right" type="submit">Submit</button>
                </div>

            </form>
        </div>
    </div>




    
@endsection
