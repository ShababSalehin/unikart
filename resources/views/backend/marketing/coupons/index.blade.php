@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('All Coupons')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('coupon.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add New Coupon')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form class="" action="" method="GET">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Coupon Information')}}</h5>
            <div class="row align-items-center">

            </div>
            <div class="col-lg-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="coupon_type" id="coupon_type">
                    <option value="">{{translate('Filter by Coupon Type')}}</option>
                    <option value="product_base">{{translate('For Products')}}</option>
                    <option value="cart_base">{{translate('For Total Orders')}}</option>
                    <option value="first_order_base">{{translate('For First Order')}}</option>
                    <option value="random_coupon">{{translate('For Random Coupon')}}</option>
                </select>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table p-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg">#</th>
                        <th>{{translate('Code')}}</th>
                        <th>{{translate('Discount Amount')}}</th>
                        <th>{{translate('Discount Type')}}</th>
                        <th data-breakpoints="lg">{{translate('Coupon Type')}}</th>
                        <th data-breakpoints="lg">{{translate('Start Date')}}</th>
                        <th data-breakpoints="lg">{{translate('End Date')}}</th>
                        <th width="10%">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $key => $coupon)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$coupon->code}}</td>
                        <td>{{$coupon->discount}}</td>
                        <td>{{$coupon->discount_type}}</td>
                        <td>@if ($coupon->type == 'cart_base')
                            {{ translate('Cart Base') }}
                            @elseif ($coupon->type == 'product_base')
                            {{ translate('Product Base') }}
                            @elseif ($coupon->type == 'first_order_base')
                            {{ translate('First Order Base') }}
                            @elseif ($coupon->type == 'random_coupon')
                            {{ translate('Random Coupon') }}
                            @endif
                        </td>
                        <td>{{ date('d-m-Y', $coupon->start_date) }}</td>
                        <td>{{ date('d-m-Y', $coupon->end_date) }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('coupon.edit', encrypt($coupon->id) )}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('coupon.destroy', $coupon->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </form>
</div>

@endsection

@section('modal')
@include('modals.delete_modal')
@endsection