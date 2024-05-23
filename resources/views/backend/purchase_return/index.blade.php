@extends('backend.layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<div class="card">
      <form class="" action="" method="GET">
        <div class="card-header row gutters-5">
          <div class="col text-center text-md-left">
            <h5 class="mb-md-0 h6">{{ translate('All Return') }}</h5>
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
                    <th data-breakpoints="md">{{ translate('Purchase ID') }}</th>
                    <th data-breakpoints="md">{{ translate('Return No') }}</th>
                    <th data-breakpoints="md">{{ translate('Return Date') }}</th>
                    <th data-breakpoints="md">{{ translate('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $key => $return)
                    <tr>
                         <td>{{ ($key+1) }}</td>
                        <td>{{ str_pad($return->purchase_id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $return->return_number }}</td>
                        <td>{{ $return->return_date }}</td>
                      
                        
                        
                        <td>
                            @if($return->status == '0')
                              <a href="{{route('purchase_return.edit', $return->id)}}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('Edit') }}">
                                   <i class="las la-edit"></i>
                               </a>

                               <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('return_approve', $return->id)}}" title="{{ translate('Approve') }}">
                                 <i class="las la-check-circle"></i>
                              </a>

                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('purchase_return.destroy', $return->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>

                               @endif
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('purchase_return.show', $return->id)}}" title="{{ translate('View') }}">
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
