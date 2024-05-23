@extends('backend.layouts.app')

@section('content')
    <div class="card">
        <h3 class="text-center mt-2">Top Selling Area Report</h3>
        <form class="" id="sort_products" action="" method="GET">
            <div class="card-header row gutters-5">
                <div class="col-8"></div>
                <div class="col-3 float-right ">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            @isset($sort_search) value="{{ $sort_search }}" @endisset
                            placeholder="{{ translate('Type & hit enter') }}">
                    </div>
                </div>
                <div>
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>

                            <th scope="col">Area ID</th>
                            <th scope="col">Area Name</th>
                            <th scope="col">Total Orders</th>
                            <th scope="col">Total Amounts</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($states as $id => $state)
                            <tr>
                                <td>{{ $state->id }}</td>
                                <td>{{ $state->name }} </td>
                                <td>{{ $state->total_orders }} </td>
                                <td>{{ $state->total_amount }} </td>
                            </tr>
                        @endforeach


                    </tbody>
                </table>

            </div>
        </form>
    </div>
@endsection