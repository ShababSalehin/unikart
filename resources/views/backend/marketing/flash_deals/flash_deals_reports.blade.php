@extends('backend.layouts.app')

@section('content')

<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-start">
            
            <div class="aiz-user-panel">

            <div class="col-xl-9 order-0 order-xl-1">
                <div class="bg-white mb-3 shadow-sm rounded">
                    <div class="nav border-bottom aiz-nav-tabs">
                        <a href="#tab_default_1" data-toggle="tab"
                            class="p-3 fs-16 fw-600 text-reset active show">{{ translate('Campaign Reports')}}</a>
                            <a href="#tab_default_2" data-toggle="tab"
                            class="p-3 fs-16 fw-600 text-reset">{{ translate('Not Join Yet')}}</a>
                      
                    </div>

                    <div class="tab-content pt-0">
                        <div class="tab-pane fade active show" id="tab_default_1">
                            <div class="p-4">
                                
                                <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                    <div class="p-4">
                           
                           <ul class="list-group list-group-flush">
                           <div class="card-body">
                <table class="table table-bordered aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Seller Name') }}</th>
                            <th>{{ translate('Number of Products') }}</th>
                            <th>{{ translate('View Details') }}</th>
                           
                        </tr>
                    </thead>
                    <tbody>

                    @foreach ($products as $key => $product)
                            <tr>
                            
                                <td>{{($key+1)}}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->noproduct }}</td>
                                <td>
                               <a  href="{{route('view_fd_tails',['sid'=>$product->id,'fdid'=>$product->flash_deal_id,])}}">View</a>
                           </td>
                              
                            </tr>
                    @endforeach
                     
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    
                </div>
            </div>
                </ul>
                      
                  </div>
                    </div>
                      </div>
                            </div>

                        </div>
                      <div class="tab-pane fade" id="tab_default_2">
                            <div class="p-4">
                               <div class="p-4">
                               <div class="card-body">
                               <table class="table table-bordered aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Seller Name') }}</th>
                            <th>{{ translate('Seller Phone') }}</th>
                            <th>{{ translate('Seller Email') }}</th>
                            </tr>
                    </thead>
                    <tbody>
                    
                    @foreach ($not_join_yeat as $key => $not_join)
                            <tr>
                            
                                <td>{{($key+1)}}</td>
                                <td>{{ $not_join->name }}</td>
                                <td>{{ $not_join->phone }}</td>
                                <td>{{ $not_join->email }}</td>
                              
                            </tr>
                    @endforeach
                     
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    
                </div>
            </div>
                            </div>
                    </div>
                </div>
            </div>
             </div>
        </div>
    </div>
</section>

@endsection
