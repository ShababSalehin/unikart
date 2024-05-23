@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Supplier')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('supplier.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New Supplier')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Supplier')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th  width="10%">#</th>
                    <th>{{translate('Name')}}</th>
                    
                    <th >{{translate('Phone')}}</th>
                    <th >{{translate('address')}}</th>
                    <th >{{translate('Contact person')}}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $key => $supplier)
                    @if($supplier->name != null)
                        <tr>
                            <td>{{ ($key+1)}}</td>
                            <td>{{$supplier->name}}</td>
                            
                            <td>{{$supplier->phone}}</td>
                            <td>{{$supplier->address}}</td>
                            <td>{{$supplier->contact_person}}</td>
                            
                            <td class="text-right">
		                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('supplier.edit', encrypt($supplier->supplier_id))}}" title="{{ translate('Edit') }}">
		                                <i class="las la-edit"></i>
		                            </a>
<!-- 		                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('supplier.destroy', $supplier->supplier_id)}}" title="{{ translate('Delete') }}">
		                                <i class="las la-trash"></i>
		                            </a> -->
		                        </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $suppliers->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
