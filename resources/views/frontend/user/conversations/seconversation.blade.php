@extends('frontend.layouts.sellerapp')

@section('panel_content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="align-items-center">
			<h1 class="h3">{{translate('All Conversations')}}</h1>
	</div>
</div>
<div class="aiz-titlebar mt-2 mb-4">
      <div class="row align-items-center">
          <div class="col-md-6">
              <b class="h4">{{ translate('Conversations')}}</b>
          </div>
          <div class="col-md-4">
					<form class="" id="filter_conversation" action="" method="GET">
						<div class="input-group input-group-sm">
                            <label>Filter By Customer</label>
                            <select onchange="change_customer_filter()" class="aiz-selectpicker"data-live-search="true" data-placeholder="{{ translate('Select Customer')}}" name="search" id="search">
                            <option value="">{{ translate('Select Customer') }}</option>
                                @foreach($customers as $customer)
                                    <option @if($sort_search==$customer->user_id) selected @endif value="{{$customer->user_id}}">{{$customer->user->name}}</option>
                                @endforeach
                            </select>
					  		<!-- <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type shop name & Enter') }}"> -->
						</div>
					</form>
				</div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <ul class="list-group list-group-flush">
          @foreach ($conversations as $key => $conversation)
              @if ($conversation->receiver != null && $conversation->sender != null)
              @php if(!empty($sort_search) && ($sort_search!=$conversation->sender_id))
                       continue;     
              @endphp
                    <li class="list-group-item px-0">
                      <div class="row gutters-10">
                          <div class="col-auto">
                              <div class="media">
                                  <span class="avatar avatar-sm flex-shrink-0">
                                    @if (Auth::user()->id == $conversation->sender_id)
                                        <img @if ($conversation->receiver->avatar_original == null) src="{{ static_asset('assets/img/avatar-place.png') }}" @else src="{{ uploaded_asset($conversation->receiver->avatar_original) }}" @endif onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                                    @else
                                        <img @if ($conversation->sender->avatar_original == null) src="{{ static_asset('assets/img/avatar-place.png') }}" @else src="{{ uploaded_asset($conversation->sender->avatar_original) }}" @endif class="rounded-circle" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                                    @endif
                                </span>
                              </div>
                          </div>
                          <div class="col-auto col-lg-3">
                              <p>
                                  @if (Auth::user()->id == $conversation->sender_id)
                                      <span class="fw-600">{{ $conversation->receiver->name }}</span>
                                  @else
                                      <span class="fw-600">{{ $conversation->sender->name }}</span>
                                  @endif
                                  <br>
                                  <span class="opacity-50">
                                      {{ date('h:i:m d-m-Y', strtotime($conversation->messages->last()->created_at)) }}
                                  </span>
                              </p>
                          </div>
                          <div class="col-12 col-lg">
                              <div class="block-body">
                                  <div class="block-body-inner pb-3">
                                      <div class="row no-gutters">
                                          <div class="col">
                                              <h6 class="mt-0">
                                              @php
                                                  $img = '';
                                                  if(!empty($conversation->product_id)){ 
                                                    $product = \App\Product::find($conversation->product_id);
                                                    $photos = explode(',', $product->photos);
                                                    if(count($photos)>0){
                                                        $img = $photos[0];
                                                    }
                                                  }
                                                  @endphp
                                                  @if(!empty($img))
                                                  <a target="_blank" href="{{ route('product', $product->slug) }}"><img style="width:20%" class="img-fluid lazyload" src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($img) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"></a>
                                                  @endif
                                                  <a href="{{ route('conversations.show', encrypt($conversation->id)) }}" class="text-dark fw-600">
                                                      {{ $conversation->title }}
                                                  </a>
                                                  <a href="{{ route('conversations.show', encrypt($conversation->id)) }}" class="d-block text-right">
                                                      View Conversation
                                                  </a>
                                                  @if ((Auth::user()->id == $conversation->sender_id && $conversation->sender_viewed == 0) || (Auth::user()->id == $conversation->receiver_id && $conversation->receiver_viewed == 0))
                                                      <span class="badge badge-inline badge-danger">{{ translate('New') }}</span>
                                                  @endif
                                              </h6>
                                          </div>
                                      </div>
                                      <p class="mb-0 opacity-50">
                                          {{ $conversation->messages->last()->message }}
                                      </p>
                                  </div>
                              </div>
                          </div>
                      </div>
                    </li>
              @endif
          @endforeach
      </ul>
      </div>
    </div>
    <div class="aiz-pagination">
      	{{ $conversations->links() }}
    </div>
    <script type="text/javascript">
    function change_customer_filter(){
        $('#filter_conversation').submit();
    }
</script>

@endsection
