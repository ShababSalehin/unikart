@extends('backend.layouts.app')
@section('content')

<div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{translate('App Slider Image')}}</h1>
                </div>
			<div class="card-body">
				<div class="alert alert-info">
					{{ translate('We have limited banner height to maintain UI. We had to crop from both left & right side in view for different devices to make it responsive. Before designing banner keep these points in mind.') }}
				</div>
				<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group">
						<label>{{ translate('Photos & Links') }}<small>({{ translate('480x540') }})</small></label>
						<div class="home-slider-target">
							<input type="hidden" name="types[]" value="app_slider_images">
							@php
							$lastkey = 0;
							@endphp
						      
							@if (get_setting('app_slider_images') != null)
								@foreach (json_decode(get_setting('app_slider_images'), true) as $key => $value)
								@php
								$lastkey += $key;
								$lkey = $lastkey -1;
								@endphp
									<div class="row gutters-5">
										<div class="col-md-4">
											<div class="form-group">
											<label>{{ translate('Photos & Links') }}<small></small></label>
												<div class="input-group" data-toggle="aizuploader" data-type="image">
					                                <div class="input-group-prepend">
					                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
					                                </div>
					                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
													<input type="hidden" name="types[]" value="app_slider_images">
					                                <input type="hidden" name="app_slider_images[]" class="selected-files" value="{{ json_decode(get_setting('app_slider_images'), true)[$key] }}">
					                            </div>
					                            <div class="file-preview box sm">
					                            </div>
				                            </div>
										</div>
									
										<div class="col-md">
											<div class="form-group">
											<label>App Banner Link Type</label>
											<div class="col-md-9">
											<input type="hidden" name="types[]" value="app_slider_type">
											<select class="form-control mb-3 aiz-selectpicker" onchange="bannerlink(this.value,<?php echo $key; ?>)" data-live-search="true" name="app_slider_type[]">
													@php
														$setting = App\BusinessSetting::where('type', 'app_slider_type')->first();
														$selectedValue = $setting ? json_decode($setting->value) : null;
													@endphp
													<option value="">Select One</option>
													<option value="Brand" @if ($selectedValue[$key] == 'Brand') selected @endif>Brand</option>
													<option value="Shop" @if ($selectedValue[$key] == 'Shop') selected @endif>Shop</option>
													<option value="Category" @if ($selectedValue[$key] == 'Category') selected @endif>Category</option>
												</select>
												</div>
											</div>
										</div>

										<div class="col-md">
										<label>Banner Item</label>
											<div class="form-group">
											<div class="col-md-9">
											<input type="hidden" name="types[]" value="app_slider_ids">
												<select class="form-control" id="app_slider_ids_<?php echo $key; ?>" name="app_slider_ids[]">
												    @php
														$setting = App\BusinessSetting::where('type', 'app_slider_ids')->first();
														$selectedItem = $setting ? json_decode($setting->value) : null;
													@endphp

													@if($selectedValue[$key] == 'Brand')
														@foreach (\App\Brand::all() as $item)
															<option value="{{ $item->id }}" @if ($item->id == $selectedItem[$key]) selected @endif>{{ $item->name }}</option>
														@endforeach
													@elseif($selectedValue[$key] == 'Shop') 
														@foreach (\App\Shop::all() as $item)
															<option value="{{ $item->id }}" @if ($item->id == $selectedItem[$key]) selected @endif>{{ $item->name }}</option>
														@endforeach 
													@else
														@foreach (\App\Category::all() as $item)
														<option value="{{ $item->id }}" @if ($item->id == $selectedItem[$key]) selected @endif>{{ $item->name }}</option>
														@endforeach  
													@endif
												</select>
												</div>
											</div>
										</div>


									

										<div class="col-md-auto">
											<div class="form-group">
												<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
													<i class="las la-times"></i>
												</button>
											</div>
										</div>
									</div>
								@endforeach
								
							@endif
						</div>
						<button
							type="button"
							class="btn btn-soft-secondary btn-sm"
							data-toggle="add-more"
							data-content='
							<div class="row gutters-5">
								<div class="col-md-4">
									<div class="form-group">
									<label>{{ translate('Photos & Links') }}<small>({{ translate('480x540') }})</small></label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
											</div>
											<div class="form-control file-amount">{{ translate('Choose File') }}</div>
											<input type="hidden" name="types[]" value="app_slider_images">
											<input type="hidden" name="app_slider_images[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>

								<div class="col-md">
								<label>App Banner Link Type</label>
									<div class="form-group">
									<div class="col-md-9">
									            <input type="hidden" name="types[]" value="app_slider_type">
												<select class="form-control mb-3 aiz-selectpicker" onchange="bannerlink(this.value,<?php echo $lkey-1;?>)" data-live-search="true" name="app_slider_type[]">
													<option  value="">Select One</option>
													<option  value="Brand">Brand</option>
													<option  value="Shop">Shop</option>
													<option  value="Category">Category</option>
												</select>
												</div>
									</div>
								</div>

								<div class="col-md">
								<label>App Banner Item</label>
									<div class="form-group">
									<div class="col-md-9">
									<input type="hidden"  name="types[]" value="app_slider_ids">
									  <select id="app_slider_ids_<?php echo $lkey-1; ?>" class="form-control" name="app_slider_ids[]">
										
										</select>
										</div>
									</div>
								</div>


								<div class="col-md-auto">
									<div class="form-group">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>
							</div>'data-target=".home-slider-target">
							{{ translate('Add New') }}
						</button>
					</div>
					<div class="text-right">
						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
					</div>
				</form>
			</div>
            </div>
        </div>
    </div>

@endsection


@section('script')

<script type="text/javascript">
        function bannerlink(val,id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            url: "{{route('categories.bannerlinkdata')}}",
            type: 'GET',
            data: {
                value: val
            },

            success: function(data){
                $('#app_slider_ids_'+id).html('');
                $.each(data, function(key, value){
                $('#app_slider_ids_'+id).append('<option value="'+ value.id +'">' + value.name + '</option>');
                });
            }
        });
    }
    
    </script>
@endsection