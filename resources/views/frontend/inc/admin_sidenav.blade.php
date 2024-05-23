<div class="aiz-sidebar-wrap">
    <div class="aiz-sidebar left c-scrollbar">
        <div class="aiz-side-nav-logo-wrap">
            <a href="{{ route('dashboard') }}" class="d-block text-left">
                @if(get_setting('system_logo_white') != null)
                <img class="mw-100" src="{{ uploaded_asset(get_setting('system_logo_white')) }}" class="brand-icon" alt="{{ get_setting('site_name') }}">
                @else
                <img class="mw-100" src="{{ static_asset('assets/img/logo.png') }}" class="brand-icon" alt="{{ get_setting('site_name') }}">
                @endif
            </a>
        </div>
        <div class="aiz-side-nav-wrap">
            <!-- <div class="px-20px mb-3">
                <input class="form-control bg-soft-secondary border-0 form-control-sm text-white" type="text" name="" placeholder="{{ translate('Search in menu') }}" id="menu-search" onkeyup="menuSearch()">
            </div>
            <ul class="aiz-side-nav-list" id="search-menu">
            </ul> -->
            <ul class="aiz-side-nav-list" id="main-menu" data-toggle="aiz-side-menu">
                <li class="aiz-side-nav-item">
                    <a href="{{ route('dashboard') }}" class="aiz-side-nav-link">
                        <i class="las la-home aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{translate('DASHBOARD')}}</span>
                    </a>
                </li>
                <!-- Products -->
                <li class="aiz-side-nav-item">
                    <a href="#" class="aiz-side-nav-link">
                        <i class="las la-shopping-cart aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{translate('PRODUCT')}}</span>
                        <span class="aiz-side-nav-arrow"></span>
                    </a>
                    <!--Submenu-->
                    <ul class="aiz-side-nav-list level-2">
                    <li class="aiz-side-nav-item">
                            <a href="{{route('seller.products')}}" class="aiz-side-nav-link">
                                <span class="aiz-side-nav-text">{{ translate('MANAGE PRODUCT') }}</span>
                            </a>
                        </li>
                        <li class="aiz-side-nav-item">
                            <a class="aiz-side-nav-link" href="{{route('seller.products.upload')}}">
                                <span class="aiz-side-nav-text">{{translate('ADD PRODUCT')}}</span>
                            </a>
                        </li>
                        
                        <li class="aiz-side-nav-item">
                            <a href="{{route('product_bulk_upload.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['products.admin', 'products.create', 'products.admin.edit']) }}">
                                <span class="aiz-side-nav-text">{{ translate('ADD BULK QUANTITY') }}</span>
                            </a>
                        </li>
                        
                        
                    </ul>
                </li>
                <!-- ORDERS & REVIEWS -->
                <li class="aiz-side-nav-item">
                    <a href="#" class="aiz-side-nav-link">
                        <i class="las la-shopping-cart aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{translate('ORDERS & REVIEWS')}}</span>
                        <span class="aiz-side-nav-arrow"></span>
                    </a>
                    <!--Submenu-->
                    <ul class="aiz-side-nav-list level-2">
                    <li class="aiz-side-nav-item">
                            <a href="{{ route('orders.index') }}" class="aiz-side-nav-link">
                                <span class="aiz-side-nav-text">{{ translate('MANAGE ORDER') }}</span>
                            </a>
                        </li>
                        <li class="aiz-side-nav-item">
                            <a class="aiz-side-nav-link" href="{{ route('reviews.seller') }}">
                                <span class="aiz-side-nav-text">{{translate('MANAGE REVIEWS')}}</span>
                            </a>
                        </li>
                       </ul>
                </li>

                <li class="aiz-side-nav-item">
                    <a href="{{ route('seller_income_report.details', Auth::user()->id) }}" class="aiz-side-nav-link">
                        <i class="las la-shopping-cart aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{translate('Income Details')}}</span>
                        <span class="aiz-side-nav-arrow"></span>
                    </a>
                </li>
                
                <!-- PROMOTIONS -->
                <li class="aiz-side-nav-item">
                    <a href="#" class="aiz-side-nav-link">
                        <i class="las la-shopping-cart aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{translate('PROMOTIONS')}}</span>
                        <span class="aiz-side-nav-arrow"></span>
                    </a>
                    <!--Submenu-->
                    <ul class="aiz-side-nav-list level-2">

                    <li class="aiz-side-nav-item">
                           <a class="aiz-side-nav-link" href="{{route('campaign')}}">
                               <span class="aiz-side-nav-text">{{translate('Campaign')}}</span>
                           </a>
                        </li>

                        <li class="aiz-side-nav-item">
                            <a class="aiz-side-nav-link" href="{{route('seller.coupon.index')}}">
                                <span class="aiz-side-nav-text">{{translate('VOUCHER')}}</span>
                            </a>
                        </li>
                       </ul>
                </li>
                 @php
                        $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
                        $newewquest = \App\RefundRequest::where('seller_seen',0)->get();
                    @endphp
                        @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('vendor_refund_request') }}" class="aiz-side-nav-link {{ areActiveRoutes(['vendor_refund_request','reason_show'])}}">
                                    <i class="las la-backward aiz-side-nav-icon"></i>
                                    <span class="aiz-side-nav-text">{{ translate('Received Refund Request') }}</span>
                                    @if( count($newewquest) > 0 )
                                <span class="badge badge-inline badge-success">{{ translate('New') }}</span>
                              @endif
                                </a>
                            </li>
                        @endif
                <li class="aiz-side-nav-item">
                    <a href="{{ route('shops.index') }}" class="aiz-side-nav-link">
                        <i class="las la-home aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{translate('SHOP SETTINGS')}}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item">
                    <a href="{{ route('profile') }}" class="aiz-side-nav-link">
                        <i class="las la-home aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{translate('MANAGE PROFILE')}}</span>
                    </a>
                </li>

                <li class="aiz-side-nav-item">
                    <a href="{{ route('payments.index') }}" class="aiz-side-nav-link">
                        <i class="las la-home aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{translate('PAYMENT HISTORY')}}</span>
                    </a>
                </li>

                
           @if (get_setting('conversation_system') == 1)
                        @php
                            $conversation = \App\Conversation::where('receiver_id', Auth::user()->id)->where('receiver_viewed', 0)->get()->count();
                            $message = \App\Message::where('receiver_id', Auth::user()->id)->where('receiver_viewed', 0)->get()->count();
                     
                        @endphp
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('conversations.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['conversations.index', 'conversations.show'])}}">
                                <i class="las la-comment aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ translate('Conversations') }}</span>
                                @if ( $conversation > 0 ||  $message > 0 )
                                <span class="badge badge-inline badge-success">{{ translate('New') }}</span>
                                @endif
                            </a>
                        </li>
                    @endif


            
            </ul><!-- .aiz-side-nav -->
        </div><!-- .aiz-side-nav-wrap -->
    </div><!-- .aiz-sidebar -->
    <div class="aiz-sidebar-overlay"></div>
</div><!-- .aiz-sidebar -->