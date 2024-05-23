@extends('backend.layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<div class="card">
      <form class="" action="" method="GET">
        <div class="card-header row gutters-5">
          <div class="col text-center text-md-left">
            <h5 class="mb-md-0 h6">{{ translate('All Orders') }}</h5>
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
    </form>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th data-breakpoints="md">{{ translate('Purchase Order') }}</th>
                    <th data-breakpoints="md">{{ translate('Date') }}</th>
                    <th data-breakpoints="md">{{ translate('Supplier') }}</th>
                    <th data-breakpoints="md">{{ translate('Total') }}</th>
                    <th class="text-right" width="18%">{{translate('options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $key => $order)
                    <tr>
                        <td>
                            {{ ($key+1) + ($data->currentPage() - 1)*$data->perPage() }}
                        </td>
                        <td>
                            {{ $order->purchase_no }}
                        </td>
                        <td>
                            {{ $order->date }}
                        </td>
                        <td>
                        {{ $order->name }}
                        </td>
                        <td>
                            {{ single_price($order->total_value) }}
                        </td>
                        
                        <td class="text-right">
                            @if($order->status == '1')

                              <a href="{{route('puracher_edit', $order->id)}}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('Edit') }}">
                                   <i class="las la-edit"></i>
                               </a>

                               <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('purchase_approve.index', $order->id)}}" title="{{ translate('Approve') }}">
                                 <i class="las la-check-circle"></i>
                              </a>

                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy_po', $order->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                               @endif
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('purchase_orders_view', $order->id)}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>

                        </td>
                        
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $data->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">

    </script>
@endsection
