@extends('backend.layouts.app')

@section('content')

@php
$categories = \App\Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get(); 
@endphp
<form id="culexpo" action="{{ route('customer_wishlist.index') }}" method="GET">
    <div class="card-header row gutters-5">
        <div class="col text-center text-md-left">
            <h5 class="mb-md-0 h6">{{ translate('Product Wish Report') }}</h5>
        </div>
               <div class="col-md-3">
                            <select id="demo-ease " class="select2 form-control aiz-selectpicker" name="category_id" data-live-search="true">
                                <option value=''>Chose Category</option>
                                @foreach ($categories as $category)
                                <option @php if($sort_by==$category->id)
                                    echo 'selected';
                                    @endphp
                                    value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                    @foreach ($category->childrenCategories as $childCategory)
                                    @include('categories.child_category', ['child_category' => $childCategory,'value'=>$category->getTranslation('name').'/'])
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

        <!-- <div class="col-md-3">
            <div class="form-group mb-0">
                <input type="text" class="form-control form-control-sm aiz-date-range" id="search" name="date_range"@isset($date_range) value="{{ $date_range }}" @endisset placeholder="{{ translate('Daterange') }}">
            </div>
        </div> -->
        <div class="col-md-4">
        <button class="btn btn-primary" onclick="submitForm('{{ route('customer_wishlist.index') }}')">{{ translate('Filter') }}</button>
        <button class="btn btn-primary" onclick="submitForm('{{ route('customer_wishlist_download') }}')">{{ translate('Excel') }}</button>
        <button class="btn btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <div class="printArea">
                    <style>
                        th {
                            text-align: center;
                        }
                    </style>
                <table class="table table-bordered aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Product Name') }}</th>
                            <th>{{ translate('Costomer ID') }}</th>
                            <th>{{ translate('Costomer Name ') }}</th>
                            <th>{{ translate('Costomer Phone') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wishreports as $key => $wishreport)
                                <tr>
                                    <td>{{ $wishreport->proname }}</td>
                                    <td>{{ $wishreport->id}}</td>
                                    <td>{{ $wishreport->name}}</td>
                                    <td>{{ $wishreport->phone }}</td>
                                </tr>
                            
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function submitForm(url){
   $('#culexpo').attr('action',url);
   $('#culexpo').submit();
}
</script>
@endsection
