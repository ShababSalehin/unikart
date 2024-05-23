@extends('frontend.layouts.user_panel')

@section('panel_content')
<div class="aiz-titlebar mt-2 mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Manage Profile') }}</h1>
        </div>
    </div>
</div>

<!-- Basic Info-->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Basic Info')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group row">
                <label class="col-md-2 col-form-label">{{ translate('Your Name') }}</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" placeholder="{{ translate('Your Name') }}" name="name" value="{{ Auth::user()->name }}">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label">{{ translate('Your Phone') }}</label>
                <div class="col-md-10">
                    <input type="number" class="form-control" placeholder="{{ translate('Your Phone')}}" name="phone" value="{{ Auth::user()->phone }}" readonly>
                </div>
            </div>
            <!-- <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ translate('Your Gender') }}</label>
                    <div class="col-md-10">
                    <select class="form-control" name="gender">
	<option value="" >Select Gender</option>
	<option value="Male">Male</option>
	<option value="Female">Female</option>
	<option value="Other">Other</option>
</select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ translate('Your Birth Date') }}</label>
                    <div class="col-md-10">
                        <input type="date" class="form-control" placeholder="{{ translate('Your Birth Date') }}" name="date_of_birth" value="{{ Auth::user()->birth_day }}">
                    </div>
                </div> -->
            <div class="form-group row">
                <label class="col-md-2 col-form-label">{{ translate('Photo') }}</label>
                <div class="col-md-10">
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="photo" value="{{ Auth::user()->avatar_original }}" class="selected-files">
                    </div>
                    <div class="file-preview box sm">
                    </div>
                </div>
            </div>


            <div class="form-group mb-0 text-right">
                <button type="submit" class="btn btn-primary">{{translate('Update Profile')}}</button>
            </div>
        </form>
    </div>
</div>

<!-- Address -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Address')}}</h5>
    </div>
    <div class="card-body">
        <div class="row gutters-10">
            @foreach (Auth::user()->addresses as $key => $address)
            <div class="col-lg-6">
                <div class="border p-3 pr-5 rounded mb-3 position-relative">
                   
                    <div>
                        <span class="w-50 fw-600">{{ translate('Address') }}:</span>
                        <span class="ml-2">{{ $address->address }}</span>
                    </div>
                    <!-- <div>
                        <span class="w-50 fw-600">{{ translate('Postal Code') }}:</span>
                        <span class="ml-2">{{ $address->postal_code }}</span>
                    </div> -->
                    <div>
                        <span class="w-50 fw-600">{{ translate('City') }}:</span>
                        <span class="ml-2">{{ get_city_name($address->city_id)[0] }}</span>
                    </div>
                    <div>
                        <span class="w-50 fw-600">{{ translate('Country') }}:</span>
                        <span class="ml-2">{{ get_country_name($address->country_id)[0] }}</span>
                    </div>
                    <div>
                        <span class="w-50 fw-600">{{ translate('Phone') }}:</span>
                        <span class="ml-2">{{ $address->phone }}</span>
                    </div>
                    @if ($address->set_default)
                    <div class="position-absolute right-0 bottom-0 pr-2 pb-3">
                        <span class="badge badge-inline badge-primary">{{ translate('Default') }}</span>
                    </div>
                    @endif
                    <div class="dropdown position-absolute right-0 top-0">
                        <button class="btn bg-gray px-2" type="button" data-toggle="dropdown">
                            <i class="la la-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" onclick="edit_address('{{$address->id}}')">
                                {{ translate('Edit') }}
                            </a>
                            @if (!$address->set_default)
                            <a class="dropdown-item" href="{{ route('addresses.set_default', $address->id) }}">{{ translate('Make This Default') }}</a>
                            @endif
                            <a class="dropdown-item" href="{{ route('addresses.destroy', $address->id) }}">{{ translate('Delete') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="col-lg-6 mx-auto" onclick="add_new_address()">
                <div class="border p-3 rounded mb-3 c-pointer text-center bg-light">
                    <i class="la la-plus la-2x"></i>
                    <div class="alpha-7">{{ translate('Add New Address') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Change -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Your email')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('user.change.email') }}" method="POST">
            @csrf
            <div class="row">
            @if(!empty(Auth::user()->email))
              <div class="col-md-2">
                    <label>{{ translate('Your Email') }}</label>
                </div>
                <div class="col-md-10">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="{{ translate('Your Email')}}" name="email" value="{{ Auth::user()->email }}" readonly>
                        <div class="input-group-append">
                            
                        </div>
                    </div>
                   
                </div>

              @else 

              <div class="col-md-2">
                    <label>{{ translate('Your Email') }}</label>
                </div>
                <div class="col-md-10">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="{{ translate('Your Email')}}" name="email" value="{{ Auth::user()->email }}" />
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary new-email-verification">
                                <span class="d-none loading">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ translate('Sending Email...') }}
                                </span>
                                <span class="default">{{ translate('Verify & Update') }}</span>
                            </button>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                       
                    </div>
                </div>
              @endif
            </div>
        </form>
    </div>
</div>

<!-- for payment -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Payment Method')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('customer.change.paymentmethod') }}" method="POST">
            @csrf

           
    <div class="card-body">
                <label class="col-md-2 col-form-label">{{ translate('Bkash Account') }}</label>
                <div class="col-md-10">
                    <input type="number" class="form-control" placeholder="{{ translate('bKash(Personal) ') }}" name="bkash_ac" value="{{ Auth::user()->customer->bkash_ac }}">
                </div>
            </div>

            <div class="row">
                <label class="col-md-3 col-form-label">{{ translate('Bank Name') }}</label>
                <div class="col-md-9">
                    <input type="text" class="form-control mb-3" placeholder="{{ translate('Bank Name')}}" value="{{ Auth::user()->customer->bank_name }}" name="bank_name">
                </div>
            </div>

            <div class="row">
                <label class="col-md-3 col-form-label">{{ translate('Bank Account Name') }}</label>
                <div class="col-md-9">
                    <input type="text" class="form-control mb-3" placeholder="{{ translate('Bank Account Name')}}" value="{{ Auth::user()->customer->bank_acc_name }}" name="bank_acc_name">
                </div>
            </div>

            <div class="row">
                <label class="col-md-3 col-form-label">{{ translate('Bank Account Number') }}</label>
                <div class="col-md-9">
                    <input type="number" class="form-control mb-3" placeholder="{{ translate('Bank Account Number')}}" value="{{ Auth::user()->customer->bank_acc_no }}" name="bank_acc_no">
                </div>
            </div>

            <div class="row">
                <label class="col-md-3 col-form-label">{{ translate('Bank Branch Name') }}</label>
                <div class="col-md-9">
                    <input type="text" lang="en" class="form-control mb-3" placeholder="{{ translate('Bank Branch Name')}}" value="{{ Auth::user()->customer->bank_branch_name }}" name="bank_branch_name">
                </div>
            </div>
            </div>



            <div class="form-group mb-0 text-right">
                <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
            </div>


        </form>
    </div>
    </div>
</div>


<!-- Password Change -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Change your Password')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('customer.change.password') }}" method="POST">
            @csrf
            <div class="form-group row">
                <label class="col-md-2 col-form-label">{{ translate('Your Password') }}</label>
                <div class="col-md-10">
                    <input type="password" class="form-control" placeholder="{{ translate('New Password') }}" name="new_password" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2 col-form-label">{{ translate('Confirm Password') }}</label>
                <div class="col-md-10">
                    <input type="password" class="form-control" placeholder="{{ translate('Confirm Password') }}" name="confirm_password" required>
                </div>
            </div>

            <div class="form-group mb-0 text-right">
                <button type="submit" class="btn btn-primary">{{translate('Update Password')}}</button>
            </div>


        </form>
    </div>
</div>

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

@endsection

@section('script')
<script type="text/javascript">
    function add_new_address() {
        $('#new-address-modal').modal('show');
    }

    $('.new-email-verification').on('click', function() {
        $(this).find('.loading').removeClass('d-none');
        $(this).find('.default').addClass('d-none');
        var email = $("input[name=email]").val();

        $.post('{{ route("user.new.verify") }}', {
                _token: '{{ csrf_token() }}',
                email: email
            },
            function(data) {
                data = JSON.parse(data);
                $('.default').removeClass('d-none');
                $('.loading').addClass('d-none');
                if (data.status == 2)
                    AIZ.plugins.notify('warning', data.message);
                else if (data.status == 1)
                    AIZ.plugins.notify('success', data.message);
                else
                    AIZ.plugins.notify('danger', data.message);
            });
    });
	get_states(18);
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

                @if(get_setting('google_map') == 1)
                var lat = -33.8688;
                var long = 151.2195;

                if (response.data.address_data.latitude && response.data.address_data.longitude) {
                    lat = response.data.address_data.latitude;
                    long = response.data.address_data.longitude;
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
</script>

@if (get_setting('google_map') == 1)

@include('frontend.partials.google_map')

@endif

@endsection