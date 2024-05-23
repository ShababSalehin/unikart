@extends('frontend.layouts.app')

@section('content')
<section class="pt-5 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="row aiz-steps arrow-divider">
                    <div class="col done">
                        <div class="text-center text-success">
                            <i class="la-3x mb-2 las la-shopping-cart"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('1. My Cart')}}</h3>
                        </div>
                    </div>
                    <!-- <div class="col done">
                        <div class="text-center text-success">
                            <i class="la-3x mb-2 las la-map"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('2. Shipping info')}}</h3>
                        </div>
                    </div> -->
                    <!-- <div class="col done">
                        <div class="text-center text-success">
                            <i class="la-3x mb-2 las la-truck"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('3. Delivery info')}}</h3>
                        </div>
                    </div> -->
                    <div class="col active">
                        <div class="text-center text-primary">
                            <i class="la-3x mb-2 las la-credit-card"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('2. Payment')}}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-check-circle"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ translate('3. Confirmation')}}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


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

                        <!-- <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Postal code')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('Your Postal Code')}}" name="postal_code" value="" required>
                            </div>
                        </div> -->
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

<section class="mb-4">
    <div class="container text-left">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('payment.checkout') }}" class="form-default" role="form" method="POST" id="checkout-form">
                    @csrf
                    <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">

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
                                <input type="radio" name="data[address_id][address_key]" id="address_key" value="{{$address->id}}" @if ($address->set_default)
                                    
                                    checked
                                    @endif required>
                                    <span class="d-flex p-3 aiz-megabox-elem">
                                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                        <span class="flex-grow-1 pl-3 text-left">
                                           
                                            <div>
                                                <span class="opacity-60">{{ translate('Address') }}:</span>
                                                <span class="fw-600 ml-2">{{ $address->address }}</span>
                                            </div>
                                            <!-- <div>
                                                <span class="opacity-60">{{ translate('Postal Code') }}:</span>
                                                <span class="fw-600 ml-2">{{ $address->postal_code }}</span>
                                            </div> -->
                                            <div>
                                                <span class="opacity-60">{{ translate('City') }}:</span>
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
                    <div class="row border-top pt-3 d-none">
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


            </div>
        </div>
    </div>
</section>

<div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ translate('Special Instruction (Optional)')}}</label>
                <input type="text" class="form-control" placeholder="{{ translate('Write Special Instruction')}}" name="special_instruction">
            </div>
        </div>

                    <div class="card shadow-sm border-0 rounded">
                        <div class="card-header p-3">
                            <h3 class="fs-16 fw-600 mb-0">
                                {{ translate('Select a payment option')}}
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="row">
                                <div class="col-xxl-8 col-xl-10 mx-auto">
                                    <div class="row gutters-10">
                                        @if(get_setting('paypal_payment') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="paypal" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/paypal.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Paypal')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('stripe_payment') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="stripe" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/stripe.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Stripe')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('sslcommerz_payment') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="sslcommerz" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/sslcommerz.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('sslcommerz')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('instamojo_payment') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="instamojo" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/instamojo.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Instamojo')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('razorpay') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="razorpay" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/rozarpay.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Razorpay')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('paystack') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="paystack" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/paystack.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Paystack')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('voguepay') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="voguepay" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/vogue.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('VoguePay')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('payhere') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="payhere" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/payhere.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('payhere')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('ngenius') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="ngenius" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/ngenius.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('ngenius')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('iyzico') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="iyzico" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/iyzico.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Iyzico')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('nagad') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="nagad" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/nagad.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Nagad')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('bkash') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="bkash" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/bkash.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Bkash')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('aamarpay') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="aamarpay" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/aamarpay.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Aamarpay')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('proxypay') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="proxypay" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/proxypay.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('ProxyPay')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(addon_is_activated('african_pg'))
                                        @if(get_setting('mpesa') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="mpesa" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/mpesa.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('mpesa')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('flutterwave') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="flutterwave" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/flutterwave.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('flutterwave')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('payfast') == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="payfast" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/payfast.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('payfast')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @endif
                                        @if(addon_is_activated('paytm'))
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="paytm" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/paytm.jpg')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Paytm')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @if(get_setting('cash_payment') == 1)
                                        @php
                                        $digital = 0;
                                        $cod_on = 1;
                                        foreach($carts as $cartItem){
                                        $product = \App\Product::find($cartItem['product_id']);
                                        if($product['digital'] == 1){
                                        $digital = 1;
                                        }
                                        if($product['cash_on_delivery'] == 0){
                                        $cod_on = 0;
                                        }
                                        }
                                        @endphp
                                        @if($digital != 1 && $cod_on == 1)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="cash_on_delivery" class="online_payment" type="radio" name="payment_option" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ static_asset('assets/img/cards/cod.png')}}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ translate('Cash on Delivery')}}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                        @endif
                                        @if (Auth::check())
                                        @if (addon_is_activated('offline_payment'))
                                        @foreach(\App\ManualPaymentMethod::all() as $method)
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="{{ $method->heading }}" type="radio" name="payment_option" onchange="toggleManualPaymentData({{ $method->id }})" data-id="{{ $method->id }}" checked>
                                                <span class="d-block p-3 aiz-megabox-elem">
                                                    <img src="{{ uploaded_asset($method->photo) }}" class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span class="d-block fw-600 fs-15">{{ $method->heading }}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        @endforeach

                                        @foreach(\App\ManualPaymentMethod::all() as $method)
                                        <div id="manual_payment_info_{{ $method->id }}" class="d-none">
                                            @php echo $method->description @endphp
                                            @if ($method->bank_info != null)
                                            <ul>
                                                @foreach (json_decode($method->bank_info) as $key => $info)
                                                <li>{{ translate('Bank Name') }} - {{ $info->bank_name }}, {{ translate('Account Name') }} - {{ $info->account_name }}, {{ translate('Account Number') }} - {{ $info->account_number}}, {{ translate('Routing Number') }} - {{ $info->routing_number }}</li>
                                                @endforeach
                                            </ul>
                                            @endif
                                        </div>
                                        @endforeach
                                        @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if (addon_is_activated('offline_payment'))
                            <div class="bg-white border mb-3 p-3 rounded text-left d-none">
                                <div id="manual_payment_description">

                                </div>
                            </div>
                            @endif
                            @if (Auth::check() && get_setting('wallet_system') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3">
                                    <span class="opacity-60">{{ translate('Or')}}</span>
                                </span>
                            </div>
                            <div class="text-center py-4">
                                <div class="h6 mb-3">
                                    <span class="opacity-80">{{ translate('Your wallet balance :')}}</span>
                                    <span class="fw-600">{{ single_price(Auth::user()->balance) }}</span>
                                </div>
                                @if(Auth::user()->balance < $total) <button type="button" class="btn btn-secondary" disabled>
                                    {{ translate('Insufficient balance')}}
                                    </button>
                                    @else
                                    <button type="button" onclick="use_wallet()" class="btn btn-primary fw-600">
                                        {{ translate('Pay with wallet')}}
                                    </button>
                                    @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="pt-3"> 
                        <label  class="aiz-checkbox">
                            <input type="checkbox" checked required id="agree_checkbox">
                            <span class="aiz-square-check"></span>
                            <span>{{ translate('I agree to the')}}</span>

                        </label>
                        <a href="{{ route('terms') }}">{{ translate('terms and conditions')}}</a>,
                        <a href="{{ route('returnpolicy') }}">{{ translate('return policy')}}</a> &
                        <a href="{{ route('privacypolicy') }}">{{ translate('privacy policy')}}</a>
                        
                    </div>
                    <div class="pt-3">
                            <h6>Estimated Delivery: (2-3 days with in Dhaka, 3-5 days outside of Dhaka)</h6>
                        </div>
                    <div class="row align-items-center pt-3">
                        <div class="col-6">
                            <a href="{{ route('home') }}" class="link link--style-3">
                                <i class="las la-arrow-left"></i>
                                {{ translate('Return to shop')}}
                            </a>
                        </div>
                        <div class="col-3 text-right">
                            <button type="button" onclick="submitOrder(this)" class="btn btn-primary fw-600">{{ translate('Complete Order')}}</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4 mt-4 mt-lg-0" id="cart_summary">
                @include('frontend.partials.cart_summary')
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script type="text/javascript">

function add_new_address(){
            $('#new-address-modal').modal('show');
        }

        function edit_address(address) {
            var url = '{{ route("addresses.edit", ":id") }}';
            url = url.replace(':id', address);
            
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function (response) {
                    $('#edit_modal_body').html(response.html);
                    $('#edit-address-modal').modal('show');
                    AIZ.plugins.bootstrapSelect('refresh');
                    var country = $("#edit_country").val();
                    get_city(country);

                    @if (get_setting('google_map') == 1)
                        var lat     = -33.8688;
                        var long    = 151.2195;

                        if(response.data.address_data.latitude && response.data.address_data.longitude) {
                            lat     = response.data.address_data.latitude;
                            long    = response.data.address_data.longitude;
                        }

                        initialize(lat, long, 'edit_');
                    @endif
                }
            });
        }
        $(document).on('change', '[name=country_id]', function() {
        var country_id = $(this).val();
        get_states(country_id);
    });
get_states(18);
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
                country_id: country_id
            },
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj != '') {
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
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj != '') {
                    $('[name="city_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
        });
    }

    $(document).ready(function() {
        $(".online_payment").click(function() {
            $('#manual_payment_description').parent().addClass('d-none');
        });
        toggleManualPaymentData($('input[name=payment_option]:checked').data('id'));
    });

    function use_wallet() {
        $('input[name=payment_option]').val('wallet');
        if ($('#agree_checkbox').is(":checked")) {
            $('#checkout-form').submit();
        } else {
            AIZ.plugins.notify('danger', '{{ translate('You need to agree with our policies ') }}');
        }
    }

    function submitOrder(el) {
        $(el).prop('disabled', true);
        if ($('#agree_checkbox').is(":checked")) {
            $('#checkout-form').submit();
        } else {
            AIZ.plugins.notify('danger', '{{ translate('You need to agree with our policies ') }}');
            $(el).prop('disabled', false);
        }
    }

    function toggleManualPaymentData(id) {
        if (typeof id != 'undefined') {
            $('#manual_payment_description').parent().removeClass('d-none');
            $('#manual_payment_description').html($('#manual_payment_info_' + id).html());
        }
    }

    $(document).on("click", "#coupon-apply", function() {
        var data = new FormData($('#apply-coupon-form')[0]);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: "POST",
            url: "{{route('checkout.apply_coupon_code')}}",
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data, textStatus, jqXHR) {
                AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                //                    console.log(data.response_message);
                $("#cart_summary").html(data.html);
            }
        })
    });

    $(document).on("click", "#coupon-remove", function() {
        var data = new FormData($('#remove-coupon-form')[0]);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: "POST",
            url: "{{route('checkout.remove_coupon_code')}}",
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data, textStatus, jqXHR) {
                $("#cart_summary").html(data);
            }
        })
    })
</script>
    <script type = "text/javascript">
	dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
	dataLayer.push({
        event    : "begin_checkout",
    	ecommerce: {
        	items: [@foreach ($carts as $product){
            <?php $products = \App\Product::find($product->product_id); ?>
                item_name 	: "{{$product->product->name}}",
                item_id   	: "{{$product->product_id}}",
                price     	: "{{$product->price}}",
                item_brand	: "{{!empty($products->brand_id) ? $products->brand->name : '' }}",
                item_category : "{{$products->category->name}}",
            	item_category2: "",
                item_category3: "",
                item_category4: "",
                item_variant  : "",
                item_list_name: "",  // If associated with a list selection.
                item_list_id  : "",  // If associated with a list selection.
                index     	: 0,  // If associated with a list selection.
                quantity  	: {{$product->quantity ?? 0}}
            },@endforeach]
    	}
	});
</script>


<script>

$(function(){
$("input:radio[name='data[address_id][address_key]']").change(function(){
    let value = $(this).val();
    
    $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('get_shipping_cost')}}",
            type: 'POST',
            data: {
                city_id: value
            },
           
           success: function(data){
           $("#shipping_cost").html(data.cost);
           $("#areawiseshipping").val(data.cost);
           var ttl = $('#totalpri').val();
           ttl = formatNumber(Number(ttl)+data.cost)+' ৳'
           $('#totalprittl').html(ttl)
    }
        });
    
});

});
function formatNumber(number) {
  if (number < 1000) {
    return String(number);
  }
  if (number < 1000000) {
    let numbers = String(number).split('');
    numbers.splice(-3, 0, ',');
    return numbers.join('');
  }
  if (number < 1000000000) {
    let numbers = String(number).split('');
    numbers.splice(-3, 0, ',');
    numbers.splice(-7, 0, ',');
    return numbers.join('');
  }
  if (number < 1000000000000) {
    let numbers = String(number).split('');
    numbers.splice(-3, 0, ',');
    numbers.splice(-7, 0, ',');
    numbers.splice(-11, 0, ',');
    return numbers.join('');
  }

  throw new Error(`number: ${number} is too big`);
}
</script>

@endsection