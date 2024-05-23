@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h1 class="h6">{{translate('User Search Report')}}</h1>
                <form id="culexpo" action="{{ route('user_search_report.index') }}" method="GET">
                    <div class="col-md-4">
                        <button class="btn btn-sm btn-primary" onclick="submitForm('{{ route('user_search_download') }}')">{{ translate('Excel') }}</button>
                        <button class="btn btn-sm btn-info" onclick="printDiv()" type="button">{{ translate('Print') }}</button>
                    </div>
                </form>
            </div>
            <div class="card-body printArea">
                <style>
                    th {
                        text-align: center;
                    }
                </style>
                <table class="table table-bordered aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Search By') }}</th>
                            <th>{{ translate('Number searches') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($searches as $key => $searche)
                        <tr>
                            <td>{{ ($key+1) + ($searches->currentPage() - 1)*$searches->perPage() }}</td>
                            <td>{{ $searche->query }}</td>
                            <td>{{ $searche->count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $searches->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function submitForm(url) {
        $('#culexpo').attr('action', url);
        $('#culexpo').submit();
    }
</script>
@endsection