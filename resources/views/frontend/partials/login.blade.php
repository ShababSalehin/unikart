<a href="javascript:" class="d-flex align-items-center text-reset dropdown-toggle"  data-toggle="dropdown">
@php
$showname='';
@endphp
@auth
@php
$fullname = Auth::user()->name;
$showname=explode(" ",$fullname);
if(!empty($showname[2])){
    echo substr($showname[2], 0, 9). ' <i class="la la-user la-2x"></i>';

} else if (!empty($showname[1])){
    echo substr($showname[1], 0, 9). '<i class="la la-user la-2x"></i>';
} else{
   
    echo substr($showname[0], 0, 9). '<i class="la la-user la-2x"></i>';
}
@endphp
    @endauth
    @php
    
    if(empty($showname)){
        echo "Sign in". '<i class="la la-user la-2x"></i>';
    }
    @endphp
</a>
<div class="dropdown-menu">
    
                 @auth
                    @if(isAdmin())
                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">{{ translate('My Panel')}}</a>
                    
                    @else
                    <a class="dropdown-item" href="{{ route('dashboard') }}">{{ translate('My Panel')}}</a>
                    
                    @endif
                    <a class="dropdown-item" href="{{ route('logout') }}">{{ translate('Logout')}}</a>
                    
                    @else
                    <a class="dropdown-item" href="{{ route('user.login') }}">{{ translate('Login/Sign up')}}</a>
                    <!-- <a class="dropdown-item" href="{{ route('user.registration') }}">{{ translate('Registration')}}</a> -->
                    @endauth
    
  </div>