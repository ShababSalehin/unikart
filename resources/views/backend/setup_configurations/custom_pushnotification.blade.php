@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ translate('Create Custom Push Notification') }}</h1>
                </div>
                <div class="card-body">
                    <form method="post" action="{{route('custom_push_store')}}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="exampleFormControlInput1">Notification Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="Write Title">
                        </div>
                        <div class="form-group">
                            <label for="exampleFormControlSelect1">Notification Type</label>
                            <select class="form-control" id="type" name="type">
                            <option value="">Select One</option>
                                @foreach($campaign as $camp)
                                <option value="{{$camp->id}}">{{$camp->title}}</option>
                                @endforeach
                            </select>
                        </div>

                                
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Image Link')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="image_link" placeholder="{{ translate('Image Link') }}">
                                
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Video Link')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="video_link" placeholder="{{ translate('Video Link') }}">
                                
                            </div>
                        </div>
										
                     <!-- <div class="form-group">
                        <label for="exampleFormControlSelect2">Example multiple select</label>
                        <select multiple class="form-control" id="exampleFormControlSelect2">
                        <option>1</option>
                        </select>
                      </div> -->

                        <div class="form-group">
                            <label for="text">Description</label>
                            <textarea class="form-control" id="text" name="text"></textarea>
                        </div>
                        <div class="position position-left">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
