@extends('frontend.layouts.app')

@section('content')

<section class="mb-4 gry-bg">
    <div class="container">
        <div class="row cols-xs-space cols-sm-space cols-md-space">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <form class="form-default" data-toggle="validator" action="{{ route('checkout.store_shipping_infostore') }}" role="form" method="POST">
                    @csrf
                    @if(Auth::check())
                    <div class="shadow-sm bg-white p-4 rounded mb-4">
                        <div class="row gutters-5">
                            @foreach (Auth::user()->addresses as $key => $address)
                            <div class="col-md-6 mb-3">
                                <label class="aiz-megabox d-block bg-white mb-0">
                                    <input type="radio" name="address_id" value="{{ $address->id }}" @if ($address->set_default)
                                    checked
                                    @endif required>
                                    <span class="d-flex p-3 aiz-megabox-elem">
                                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                        <span class="flex-grow-1 pl-3 text-left">
                                        <div>
                                                <span class="w-50 fw-700">{{ translate('Title') }}:</span>
                                                <span class="fw-700 ml-2">{{ $address->title }}</span>
                                            </div>

                                            <div>
                                                <span class="opacity-60">{{ translate('Address') }}:</span>
                                                <span class="fw-600 ml-2">{{ $address->address }}</span>
                                            </div>
                                            <div>
                                                <span class="opacity-60">{{ translate('Postal Code') }}:</span>
                                                <span class="fw-600 ml-2">{{ $address->postal_code }}</span>
                                            </div>
                                            <div>
                                                <span class="opacity-60">{{ translate('Thana') }}:</span>
                                                <span class="fw-600 ml-2">{{ get_city_name($address->city_id)[0] }}</span>
                                            </div>
                                            <div>
                                                <span class="opacity-60">{{ translate('Country') }}:</span>
                                                <span class="fw-600 ml-2">{{ get_country_name( $address->country_id)[0] }}</span>
                                            </div>
                                            <div>
                                                <span class="opacity-60">{{ translate('Phone') }}:</span>
                                                <span class="fw-600 ml-2">{{ $address->phone }}</span>
                                            </div>
                                        </span>
                                    </span>
                                </label>
                                <div class="dropdown position-absolute right-0 top-0">
                                    <button class="btn bg-gray px-2" type="button" data-toggle="dropdown">
                                        <i class="la la-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" onclick="edit_address('{{$address->id}}')">
                                            {{ translate('Edit') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <input type="hidden" name="checkout_type" value="logged">
                            <div class="col-md-6 mx-auto mb-3">
                                <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center" onclick="add_new_address()">
                                    <i class="las la-plus la-2x mb-3"></i>
                                    <div class="alpha-7">{{ translate('Add New Address') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="shadow-sm bg-white p-4 rounded mb-4">
                        <div class="form-group">
                            <label class="control-label">{{ translate('Name')}}</label>
                            <input type="text" class="form-control" name="name" placeholder="{{ translate('Name')}}" required>
                        </div>

                        <div class="form-group">
                            <label class="control-label">{{ translate('Email')}}</label>
                            <input type="text" class="form-control" name="email" placeholder="{{ translate('Email')}}" required>
                        </div>

                        <div class="form-group">
                            <label class="control-label">{{ translate('Address')}}</label>
                            <input type="text" class="form-control" name="address" placeholder="{{ translate('Address')}}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ translate('Select your country')}}</label>
                                    <select class="form-control aiz-selectpicker" data-live-search="true" name="country">
                                        @foreach (\App\Country::where('status', 1)->get() as $key => $country)
                                        <option value="{{ $country->name }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group has-feedback">

                                    <label class="control-label">{{ translate('City')}}</label>
                                    <select class="form-control aiz-selectpicker" data-live-search="true" name="city" required>
                                        @foreach (\App\City::get() as $key => $city)
                                        <option value="{{ $city->name }}">{{ $city->getTranslation('name') }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('Postal code')}}</label>
                                    <input type="text" class="form-control" placeholder="{{ translate('Postal code')}}" name="postal_code" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('Phone')}}</label>
                                    <input type="number" lang="en" min="0" class="form-control" placeholder="{{ translate('Phone')}}" name="phone" required>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="checkout_type" value="guest">
                    </div>
                    @endif


                    <!-- new added -->
                    <div class="row border-top pt-3">
                        <div class="col-md-6">
                            <h6 class="fs-15 fw-600">{{ translate('Choose Delivery Type') }}</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="row gutters-5">
                                <div class="col-6">
                                    <label class="aiz-megabox d-block bg-white mb-0">
                                        <input type="radio" name="shipping_type_{{ \App\User::where('user_type', 'admin')->first()->id }}" value="home_delivery" onchange="show_pickup_point(this)" data-target=".pickup_point_id_admin" checked>
                                        <span class="d-flex p-3 aiz-megabox-elem">
                                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                            <span class="flex-grow-1 pl-3 fw-600">{{ translate('Home Delivery') }}</span>
                                        </span>
                                    </label>
                                </div>
                                @if (\App\BusinessSetting::where('type', 'pickup_point')->first()->value == 1)
                                <div class="col-6">
                                    <label class="aiz-megabox d-block bg-white mb-0">
                                        <input type="radio" name="shipping_type_{{ \App\User::where('user_type', 'admin')->first()->id }}" value="pickup_point" onchange="show_pickup_point(this)" data-target=".pickup_point_id_admin">
                                        <span class="d-flex p-3 aiz-megabox-elem">
                                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                            <span class="flex-grow-1 pl-3 fw-600">{{ translate('Local Pickup') }}</span>
                                        </span>
                                    </label>
                                </div>
                                @endif
                            </div>
                            <div class="mt-4 pickup_point_id_admin d-none">
                                <select class="form-control aiz-selectpicker" name="pickup_point_id_{{ \App\User::where('user_type', 'admin')->first()->id }}" data-live-search="true">
                                    <option>{{ translate('Select your nearest pickup point')}}</option>
                                    @foreach (\App\PickupPoint::where('pick_up_status',1)->get() as $key => $pick_up_point)
                                    <option value="{{ $pick_up_point->id }}" data-content="<span class='d-block'>
                                                                    <span class='d-block fs-16 fw-600 mb-2'>{{ $pick_up_point->getTranslation('name') }}</span>
                                                                    <span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pick_up_point->getTranslation('address') }}</span>
                                                                    <span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{ $pick_up_point->phone }}</span>
                                                                </span>">
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>


                    <!--end new added -->


                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                            <a href="{{ route('home') }}" class="btn btn-link">
                                <i class="las la-arrow-left"></i>
                                {{ translate('Return to shop')}}
                            </a>
                        </div>
                        <div class="col-md-6 text-center text-md-right">
                            <button type="submit" class="btn btn-primary fw-600">{{ translate('Continue to Delivery Info')}}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('modal')
<div class="modal fade" id="new-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('New Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-default" role="form" action="{{ route('addresses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="p-3">
                    <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Title')}}</label>
                            </div>
                            <div class="col-md-10">
                                <textarea class="form-control mb-3" placeholder="{{ translate('Office/Home')}}" rows="2" name="title" required></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Address')}}</label>
                            </div>
                            <div class="col-md-10">
                                <textarea class="form-control mb-3" placeholder="{{ translate('Your Address')}}" rows="2" name="address" required></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Country')}}</label>
                            </div>
                            <div class="col-md-10">
                                <div class="mb-3">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ translate('Select your country') }}" name="country_id" required>
                                        <option value="">{{ translate('Select your country') }}</option>
                                        @foreach (\App\Country::where('status', 1)->get() as $key => $country)
                                            <option selected value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('District')}}</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="state_id" required>

                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Thana')}}</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="city_id" required>

                                </select>
                            </div>
                        </div>

                        @if (get_setting('google_map') == 1)
                            <div class="row">
                                <input id="searchInput" class="controls" type="text" placeholder="{{translate('Enter a location')}}">
                                <div id="map"></div>
                                <ul id="geoData">
                                    <li style="display: none;">Full Address: <span id="location"></span></li>
                                    <li style="display: none;">Postal Code: <span id="postal_code"></span></li>
                                    <li style="display: none;">Country: <span id="country"></span></li>
                                    <li style="display: none;">Latitude: <span id="lat"></span></li>
                                    <li style="display: none;">Longitude: <span id="lon"></span></li>
                                </ul>
                            </div>

                            <div class="row">
                                <div class="col-md-2" id="">
                                    <label for="exampleInputuname">Longitude</label>
                                </div>
                                <div class="col-md-10" id="">
                                    <input type="text" class="form-control mb-3" id="longitude" name="longitude" readonly="">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2" id="">
                                    <label for="exampleInputuname">Latitude</label>
                                </div>
                                <div class="col-md-10" id="">
                                    <input type="text" class="form-control mb-3" id="latitude" name="latitude" readonly="">
                                </div>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Postal code')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('Your Postal Code')}}" name="postal_code" value="" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Phone')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="number" class="form-control mb-3" placeholder="{{ translate('01*********')}}" name="phone" value="" required>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('New Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body" id="edit_modal_body">

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    function edit_address(address) {
        var url = '{{ route("addresses.edit", ":id") }}';
        url = url.replace(':id', address);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
            success: function(response) {
                $('#edit_modal_body').html(response.html);
                $('#edit-address-modal').modal('show');
                AIZ.plugins.bootstrapSelect('refresh');

                var country = $("#edit_country").val();
                get_city(country);

                @if(get_setting('google_map') == 1)
                var lat = -33.8688;
                var long = 151.2195;

                if (response.data.address_data.latitude && response.data.address_data.longitude) {
                    lat = parseFloat(response.data.address_data.latitude);
                    long = parseFloat(response.data.address_data.longitude);
                }

                initialize(lat, long, 'edit_');
                @endif
            }
        });
    }
get_states(18);
    $(document).on('change', '[name=country_id]', function() {
        var country_id = $(this).val();
        get_states(country_id);
    });

    $(document).on('change', '[name=state_id]', function() {
        var state_id = $(this).val();
        get_city(state_id);
    });
    
    function get_states(country_id) {
        $('[name="state"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('get-state')}}",
            type: 'POST',
            data: {
                country_id  : country_id
            },
            success: function (response) {
                var obj = JSON.parse(response);
                if(obj != '') {
                    $('[name="state_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
        });
    }

    function get_city(state_id) {
        $('[name="city"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('get-city')}}",
            type: 'POST',
            data: {
                state_id: state_id
            },
            success: function (response) {
                var obj = JSON.parse(response);
                if(obj != '') {
                    $('[name="city_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
        });
    }

    function add_new_address() {
        $('#new-address-modal').modal('show');
    }
</script>

@if (get_setting('google_map') == 1)

@include('frontend.partials.google_map')

@endif

@endsection