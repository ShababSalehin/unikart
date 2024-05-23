@extends('frontend.layouts.app')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="d-flex align-items-start">
                @include('frontend.inc.user_side_nav')
                <div class="aiz-user-panel">
                    <div class="card">
                        @php 
                        $detailedProduct  = \App\Product::with('reviews')->where('id', $requestid)->first();
                       
                        @endphp


                        <div class="p-4">
                        <h3>
                            {{  $detailedProduct->name }}
                            </h3>
                                <ul class="list-group list-group-flush">
                                    @foreach ($detailedProduct->reviews as $key => $review)
                                    @if($review->user != null)
                                    <li class="media list-group-item d-flex">
                                        <span class="avatar avatar-md mr-3">
                                            <img class="lazyload" src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                @if($review->user->avatar_original !=null)
                                            data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                            @else
                                            data-src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            @endif
                                            >
                                        </span>
                                        <div class="media-body text-left">
                                            <div class="d-flex justify-content-between">
                                                <h3 class="fs-15 fw-600 mb-0">{{ $review->user->name }}</h3>
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
                                    @endif
                                    @endforeach
                                </ul>

                                @if(count($detailedProduct->reviews) <= 0) <div class="text-center fs-18 opacity-70">
                                    {{ translate('There have been no reviews for this product yet.') }}
                            </div>
                            @endif

                            @if(Auth::check())
                            @php
                            $commentable = false;
                            @endphp
                            @foreach ($detailedProduct->orderDetails as $key => $orderDetail)
                            @if($orderDetail->order != null && $orderDetail->order->user_id == Auth::user()->id &&
                            $orderDetail->delivery_status == 'delivered' && \App\Review::where('user_id',
                            Auth::user()->id)->where('product_id', $detailedProduct->id)->first() == null)
                            @php
                            $commentable = true;
                            @endphp
                            @endif
                            @endforeach
                            @if ($commentable)
                        
                    <div class="border-bottom mb-4">
                                    <h3 class="fs-17 fw-600">
                                        {{ translate('Write a review')}}
                                    </h3>
                                </div>

                        <div class="pt-4">
                                
                                <form class="form-default" role="form" action="{{ route('reviews.store') }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $requestid }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for=""
                                                    class="text-uppercase c-gray-light">{{ translate('Your name')}}</label>
                                                <input type="text" name="name" value="{{ Auth::user()->name }}"
                                                    class="form-control" disabled required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for=""
                                                    class="text-uppercase c-gray-light">{{ translate('Email')}}</label>
                                                <input type="text" name="email" value="{{ Auth::user()->email }}"
                                                    class="form-control" required disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="opacity-60">{{ translate('Rating')}}</label>
                                        <div class="rating rating-input">
                                            <label>
                                                <input type="radio" name="rating" value="1" required>
                                                <i class="las la-star"></i>
                                            </label>
                                            <label>
                                                <input type="radio" name="rating" value="2">
                                                <i class="las la-star"></i>
                                            </label>
                                            <label>
                                                <input type="radio" name="rating" value="3">
                                                <i class="las la-star"></i>
                                            </label>
                                            <label>
                                                <input type="radio" name="rating" value="4">
                                                <i class="las la-star"></i>
                                            </label>
                                            <label>
                                                <input type="radio" name="rating" value="5">
                                                <i class="las la-star"></i>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="opacity-60">{{ translate('Comment')}}</label>
                                        <textarea class="form-control" rows="4" name="comment"
                                            placeholder="{{ translate('Your review')}}" required></textarea>
                                    </div>

                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary mt-3">
                                            {{ translate('Submit review')}}
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @endif
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
