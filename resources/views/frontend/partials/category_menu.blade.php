<div class="aiz-category-menu category-max-min bg-white  @if(Route::currentRouteName() == 'home') shadow-sm" @else shadow-lg" @endif id="category-sidebar">
    <div class="px-3 d-none d-lg-block rounded-top all-category position-relative text-left" style="padding-top: 10px;">
    <a href="{{ route('offers') }}" class="text-reset"><span class="fw-600 fs-16 mr-1 m">{{ translate('OFFER') }}</span>
            <span class="d-none d-lg-inline-block"  style="border: 1px solid #DA2785;padding: 2px 21px;color: #DA2785 !important;border-radius: 7px;font-weight: bold;">{{offerCount()}}</span>
        </a>
        
    </div>
    @php  echo file_get_contents(base_path('category_menu_static.php'), true); @endphp
</div>