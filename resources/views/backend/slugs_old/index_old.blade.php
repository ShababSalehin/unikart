@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="container mt-5">
            <h2 class="text-center">Add Slug</h2>
            <form class="row g-3 needs-validation" novalidate action="{{ Route('slugs.add_slug') }}" method="POST">
                @csrf

                <div class="col-md-2">
                    <label class="form-label">Product ID</label>
                    <input type="text" name="product_id" class="form-control" placeholder="Enter Product ID" required>
                    @error('product_id')
                    <p class="text-danger">
                        {{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Old SLug</label>
                    <input type="text" name="old_slug" class="form-control" placeholder="Elter Old SLug" required>
                    @error('old_slug')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">New SLug</label>
                    <input type="text" name="new_slug" class="form-control" placeholder="Enter New SLug" required>
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




    <div class="container mt-5">

        <h2 class="text-center mb-3">All Slugs</h2>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Old Slug</th>
                    <th scope="col">New Slug</th>
                    <th scope="col">Product Id</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($slug as $slugs)
                    <tr>
                        <th scope="row">{{ $slugs->id }}</th>
                        <td>{{ $slugs->old_slug }}</td>
                        <td>{{ $slugs->new_slug }}</td>
                        <td>{{ $slugs->product_id }}</td>
                        <td><a href="{{url('/admin/edit/')}}/{{$slugs->id}}" class="btn btn-primary">Edit</a ></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
