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
                            class="p-3 fs-16 fw-600 text-reset active show">{{ translate('Shop Added Product List')}}</a>
                    </div>

                    <div class="tab-content pt-0">
                        <div class="tab-pane fade active show" id="tab_default_1">
                            <div class="p-4">
                                
                                <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                    <div class="p-4">
                           
                           <ul class="list-group list-group-flush">
                              @foreach ($flash_deals as $key => $flash_deal)
                             <li class="media list-group-item d-flex">
                                  <span class="avatar avatar-md mr-3">
                                      <img class="lazyload" src="{{ uploaded_asset($flash_deal->thumbnail_img) }}"
                                          onerror="this.onerror=null;this.src='{{ uploaded_asset($flash_deal->thumbnail_img) }}';">
                                  </span>
                                  <div class="media-body text-left">
                                      <div class="d-flex justify-content-between">
                                      <h3 class="fs-15 fw-600 mb-0">{{ $flash_deal->name }}</h3>

                                      <span class="rating rating-sm">
                                      <div class="opacity-60 mb-2">
                                         
                                       </div>     
                                           
                                         </div>
                                       
                                            
                                     
                                  </div>
                              </li>
                             @endforeach
                          </ul>
                      
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