@extends('frontend.layouts.app')

@section('content')
    
<section class="pt-4 mb-4">
    <div class="container" style="background-color: #E94373;">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h6 class="fw-600"><span class="font-weight-bold font-italic">Need Help?</span> We are here to Assist you 7 Days a Week!</h6>
                <h6 class="fw-600">{{ translate('Live Chat Services Available Daily: 10:00 AM- 7:00 PM') }}</h6><br>
                <button type="button" class="btn btn-primary" style=" height: 60px; background-color: #0A7CFF;border: none;"><div class="span" style="font-size: 25px;">Chat with us in Messenger</div></button><br></br>
                <h6 class="fw-700">{{ translate('or E-Mail us at info@unikart.com.bd')}}</h6>

            </div>
           
        </div>
    </div>
</section>

@endsection
<script>
	function openLiveChat(){
		$('.fb_dialog_content').click();
	}
</script>