@extends('backend.layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Orders') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                 <!--   <a class="dropdown-item" href="#" onclick="bulk_delete()"> {{translate('Delete selection')}}</a> -->
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#sendToCurierModal">
                        <i class="las la-sync-alt"></i>
                        {{translate('Send to Courier')}}
                    </a>
                </div>
            </div>

              <div class="dropdown mb-2 mb-md-0 ml-2">
                    <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                        Update Status 
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" name="status" onchange="updateStatus(this)">
                       
                    <a class="dropdown-item" href="#" onclick="updateStatus('pending')" value="pending"> <i class=""></i>
                      Pending</a>

                        <a class="dropdown-item" href="#" onclick="updateStatus('confirmed')" value="confirmed"> <i class=""></i>
                            Confirm </a>

                        <a class="dropdown-item" href="#" onclick="updateStatus('picked_up')" value="picked_up"> <i class=""></i>
                            Pick up </a>
                        <a class="dropdown-item" href="#" onclick="updateStatus('on_the_Way')" value="on_the_Way"> <i class=""></i>
                            On the way </a>
                        <a class="dropdown-item" href="#" onclick="updateStatus('delivered')" value="delivered"> <i class=""></i>
                            Deliver </a>
                        
                    </div>
                </div>

            <!-- Change Status Modal -->
            {{-- <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                {{translate('Choose an order status')}}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <select class="form-control" onchange="change_status()" id="update_delivery_status">
                                <option value="pending">{{translate('Pending')}}</option>
                                <option value="confirmed">{{translate('Confirmed')}}</option>
                                <option value="picked_up">{{translate('Picked Up')}}</option>
                                <option value="on_the_way">{{translate('On The Way')}}</option>
                                <option value="delivered">{{translate('Delivered')}}</option>
                                <option value="cancelled">{{translate('Cancel')}}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class="modal fade" id="sendToCurierModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                {{translate('Choose a Courrier Service')}}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <label>Select Courier</label>
                            <select class="form-control" name='courier_type' id="courier_type">
                                <option value="redx">{{translate('Redx')}}</option>
                            </select>
                            <label>Enter Parcel Weigth</label>
                            <input type="text" class="form-control" id="parcel_weight" name="parcel_weight" placeholder="Enter Parcel Weight">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" onclick="sendToCurier()" class="btn btn-primary">Send To Courier</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="delivery_status" id="delivery_status">
                    <option value="">{{translate('Filter by Delivery Status')}}</option>
                    <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{translate('Pending')}}</option>
                    <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>{{translate('Confirmed')}}</option>
                    <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>{{translate('Picked Up')}}</option>
                    <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>{{translate('On The Way')}}</option>
                    <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{translate('Delivered')}}</option>
                    <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>{{translate('Cancel')}}</option>
                </select>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ translate('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code & hit Enter') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <form class="" id="order_form" method="GET">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <!--<th>#</th>-->
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{ translate('SL No') }}</th>
                        <th>{{ translate('Order From') }}</th>
                        <th>{{ translate('First Order') }}</th>
                        <th>{{ translate('Order No') }}</th>
                        <th>{{ translate('Combine Order No') }}</th>
                        <th>{{ translate('Order Code') }}</th>
                        <th data-breakpoints="md">{{ translate('Num. of Products') }}</th>
                        <th data-breakpoints="md">{{ translate('Customer') }}</th>
                        <th data-breakpoints="md">{{ translate('Amount') }}</th>
                        <th data-breakpoints="md">{{ translate('Delivery Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Payment Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Payment Type') }}</th>
                        @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                        <th>{{ translate('Refund') }}</th>
                        @endif
                        <th class="text-right" width="15%">{{translate('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $s=0;
                        $previous_comb_id =0 ;
                    @endphp
                    @foreach ($orders as $key => $order)
                    @php 
                        $s++;
                    @endphp
                    <tr>
    <!--                    <td>
                            {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                        </td>-->
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$order->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $s }}
                        </td>
                        <td>
                            {{ $order->order_from }}
                        </td>
                        <td>
                            {{ $order->is_first_order }}
                        </td>
                         <td>
                            {{ $order->id }}
                        </td>
                        <td>
                            {{ $order->combined_order_id }}
                        </td>
                        <td>
                            {{ $order->code }}
                        </td>
                        <td>
                            {{ count($order->orderDetails) }}
                        </td>
                        <td>
                            @if ($order->user != null)
                            {{ $order->user->name }}
                            @else
                            Guest ({{ $order->guest_id }})
                            @endif
                        </td>
                        <td>
                            {{ single_price($order->grand_total) }}
                        </td>
                        <td>
                            @php
                                $status = $order->delivery_status;
                                if($order->delivery_status == 'cancelled') {
                                    $status = '<span class="badge badge-inline badge-danger">'.translate('Cancel').'</span>';
                                }

                            @endphp
                            {!! $status !!}
                        </td>
                        <td>
                            @if ($order->payment_status == 'paid')
                            <span class="badge badge-inline badge-success">{{translate('Paid')}}</span>
                            @else
                            <span class="badge badge-inline badge-danger">{{translate('Unpaid')}}</span>
                            @endif
                        </td>
                        <td>
                        @if($order->payment_type == 'cash_on_delivery')
                            COD
                           @elseif($order->payment_type == 'bkash')
                            bKash
                           @else
                           <span class="badge bg-warning text-dark">{{translate('SSL')}}</span>
                        @endif
                        </td>
                        @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                        <td>
                            @if (count($order->refund_requests) > 0)
                            {{ count($order->refund_requests) }} {{ translate('Refund') }}
                            @else
                            {{ translate('No Refund') }}
                            @endif
                        </td>
                        @endif

                        <!-- <td class="text-right">
                            @php
                            $combained_order = \App\Order::where('combined_order_id',$order->combined_order_id)->count();
                            @endphp
                            
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('all_orders.show', encrypt($order->id))}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            @if($combained_order > 1)
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('all_combined_orders.show', encrypt($order->id))}}" title="{{ translate('Combined Order') }}">
                            <i class="las la-binoculars"></i>
                            </a>
                            @endif
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $order->id) }}" title="{{ translate('Download Invoice') }}">
                                <i class="las la-download"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy', $order->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td> -->


                                    @php
                                        $combained_order = \App\Order::where('combined_order_id', $order->combined_order_id)->count();
                                    @endphp

                                    @php
                                        if($order->combined_order_id !=  $previous_comb_id ){
                                        $previous_comb_id = $order->combined_order_id;
                                    @endphp

                                    <td class="text-right">
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                            href="{{ route('all_orders.show', encrypt($order->id)) }}"
                                            title="{{ translate('View') }}">
                                            <i class="las la-eye"></i>
                                        </a>

                                        @if ($combained_order > 1)
                                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                                href="{{ route('all_combined_orders.show', encrypt($order->id)) }}"
                                                title="{{ translate('Combined Order') }}">
                                                <i class="las la-binoculars"></i>
                                         </a>
                                        @endif
                                        
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                            href="{{ route('invoice.download', $order->id) }}"
                                            title="{{ translate('Download Invoice') }}">
                                            <i class="las la-download"></i>
                                        </a>
                                     
                                    </td>
                              @php
                                }
                                else{
                                    $previous_comb_id = $order->combined_order_id;
                                }
                                @endphp


                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $orders->appends(request()->input())->links() }}
            </div>
        </form>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

    //    function change_status() {
    //        var data = new FormData($('#order_form')[0]);
    //        $.ajax({
    //            headers: {
    //                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //            },
    //            url: "{{route('bulk-order-status')}}",
    //            type: 'POST',
    //            data: data,
    //            cache: false,
    //            contentType: false,
    //            processData: false,
    //            success: function (response) {
    //                if(response == 1) {
    //                    location.reload();
    //                }
    //            }
    //        });
    //    }


    function updateStatus(order_status) {
            var ids = new Array();
            $('.check-one:checked').each(function() {
                ids.push($(this).val())
            });

            var data = {
                'id': ids,
                'order_status': order_status,
                
            };
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('updateStatus') }}",
                type: 'POST',
                data: data,
                dataType: 'JSON',
                success: function(response) {
                    if (response == 1) {
                        location.reload();
                    }
                }
            });
           
        }


       function sendToCurier() {
        var ids = new Array();
        $('.check-one:checked').each(function() {
                    // if($(this).checked==true){
                        ids.push($(this).val())
                    // }
                });
           var data = {'id':ids,'parcel_weight':$('#parcel_weight').val(),'courier_type':$('#courier_type').val()};
           $.ajax({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               url: "{{route('sendToCurier')}}",
               type: 'POST',
               data: data,
               dataType: 'JSON',
               success: function (response) {
                   if(response == 1) {
                       location.reload();
                   }
               }
           });
       }

        function bulk_delete() {
            var data = new FormData($('#sort_orders')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-order-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }
    </script>
@endsection
