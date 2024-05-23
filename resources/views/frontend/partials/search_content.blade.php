@if(get_setting('vendor_system_activation') == 1)
    <div>
        @if (count($shops) > 0)
            <div class="px-2 py-1 text-uppercase fs-10 text-right text-muted bg-soft-secondary">{{translate('Shops')}}</div>
            <ul class="list-group list-group-raw">
                @foreach ($shops as $key => $shop)
                    <li class="list-group-item">
                        <a class="text-reset" href="{{ route('shop.visit', $shop->slug) }}">
                            <div class="d-flex search-product align-items-center">
                                <div class="mr-3">
                                    <img class="size-40px img-fit rounded" src="{{ uploaded_asset($shop->logo) }}">
                                </div>
                                <div class="flex-grow-1 overflow--hidden">
                                    <div class="product-name text-truncate fs-14 mb-5px">
                                        {{ $shop->name }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
<div>
    @if (sizeof($tags) > 0)
        <div class="px-2 py-1 text-uppercase fs-10 text-right text-muted bg-soft-secondary">{{translate('Popular Suggestions')}}</div>
        <ul class="list-group list-group-raw">
            @foreach ($tags as $key => $tag)
            @php
            $fastReplace = str_replace("&","",$tag->tag);
            $secondReplace = str_replace("–"," ",$fastReplace);
            $thirdReplace = str_replace("\xC2\xA0"," ",$secondReplace);
            @endphp
                <li class="list-group-item py-1">
                    <a class="text-reset hov-text-primary" href="{{ route('suggestion.search', $thirdReplace) }}">{!! str_ireplace($search_str, "<b>$search_str</b>", $tag->tag) !!}</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>

