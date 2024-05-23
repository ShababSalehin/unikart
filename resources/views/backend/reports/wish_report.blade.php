@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class=" align-items-center">
       <h1 class="h3">{{translate('Product Wish Report')}}</h1>
	</div>
</div>
@php
$categories = \App\Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get(); 
@endphp
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <form id= "culexpo" action="{{ route('wish_report.index') }}" method="GET">
                    <div class="form-group row offset-lg-2">
                        <label class="col-md-3 col-form-label">{{ translate('Sort by Category') }}:</label>
                        <div class="col-md-5">
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
                        <div class="col-md-4">
                        <button class="btn btn-sm btn-primary" onclick="submitForm('{{ route('wish_report.index') }}')">{{ translate('Filter') }}</button>
                            <button class="btn btn-sm btn-primary" onclick="submitForm('{{ route('wishlist_report_download') }}')">{{ translate('Excel') }}</button>
                            <button class="btn btn-sm btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>
                         </div>
                    </div>
                </form>
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
                            <th>{{ translate('Number of Wish') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $key => $product)
                            @if($product->wishlists != null)
                                <tr>
                                    <td>{{ $product->getTranslation('name') }}</td>
                                    <td>{{ $product->wishlists->count() }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="aiz-pagination mt-4">
                    {{ $products->links() }}
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
