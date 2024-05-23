@extends('frontend.layouts.app')

@section('content')

<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-start">
            @include('frontend.inc.user_side_nav')
            <div class="aiz-user-panel">

            <div class="col-xl-9 order-0 order-xl-1">
                <div class="bg-white mb-3 shadow-sm rounded">
                    <div class="nav border-bottom aiz-nav-tabs">
                        <a href="#tab_default_1" data-toggle="tab"
                            class="p-3 fs-16 fw-600 text-reset active show">{{ translate('To be Reviewed')}}</a>
                      <a href="#tab_default_2" data-toggle="tab"
                            class="p-3 fs-16 fw-600 text-reset">{{ translate('History')}}</a>
                    </div>

                    <div class="tab-content pt-0">
                        <div class="tab-pane fade active show" id="tab_default_1">
                            <div class="p-4">
                                
                                <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                    <div class="p-4">
                           
                           <ul class="list-group list-group-flush">
                              @foreach ($noreview as $key => $noreview)
                             <li class="media list-group-item d-flex">
                                  <span class="avatar avatar-md mr-3">
                                      <img class="lazyload" src="{{ uploaded_asset($noreview->thumbnail_img) }}"
                                          onerror="this.onerror=null;this.src='{{ uploaded_asset($noreview->thumbnail_img) }}';">
                                  </span>
                                  <div class="media-body text-left">
                                      <div class="d-flex justify-content-between">
                                      <h3 class="fs-15 fw-600 mb-0">{{ $noreview->name }}</h3>

                                      <span class="rating rating-sm">
                                      <div class="opacity-60 mb-2">
                                         
                                       </div>     
                                            <a href="{{route('make_review',$noreview->id)}}">Make Review</a>
                                                           
                                                </span>
                                         </div>
                                         <div class="opacity-60 mb-2">
                                            Purchased On  {{ date('d-m-Y', strtotime($noreview->created_at)) }}
                                       
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
                      <div class="tab-pane fade" id="tab_default_2">
                            <div class="p-4">
                               <div class="p-4">
                            <ul class="list-group list-group-flush">
                                    @foreach ($review as $key => $review)
                                   <li class="media list-group-item d-flex">
                                        <span class="avatar avatar-md mr-3">
                                            <img class="lazyload" src="{{ uploaded_asset($review->thumbnail_img) }}"
                                                onerror="this.onerror=null;this.src='{{ uploaded_asset($review->thumbnail_img) }}';">
                                        </span>
                                        <div class="media-body text-left">
                                            <div class="d-flex justify-content-between">
                                            <h3 class="fs-15 fw-600 mb-0">{{ $review->name }}</h3>
                                                <span class="rating rating-sm">
                                                    @for ($i=0; $i < $review->rating; $i++)
                                                        <i class="las la-star active"></i>
                                                        @endfor
                                                        @for ($i=0; $i < 5-$review->rating; $i++)
                                                            <i class="las la-star"></i>
                                                            @endfor
                                                </span>
                                            </div>

                                            <div class="opacity-60 mb-2">
                                                {{ date('d-m-Y', strtotime($review->created_at)) }}</div>
                                            <p class="comment-text">
                                                {{ $review->comment }}
                                            </p>
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
</section>
@endsection