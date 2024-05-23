@extends('backend.layouts.app')
@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Reason For Refund Request ')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-lg-2 col-from-label"><b>{{translate('Return & Refund Reason')}}:</b></label>
                    
                    <div class="col-lg-8">
                        <p class="bord-all pad-all">{{ $refund->reason }}</p>
                       
                    </div>
                </div>

                        @php 
                        $userpaymrnt_mrthod = \App\Customer::where('user_id',$refund->user_id)->first();
                       
@endphp
                        @if($refund->order->payment_type == 'bkash')
                     <div class="form-group row">
                     <label class="col-lg-2 col-from-label"><b>{{translate('Request Payment Method')}}:</b></label>
                    
                        <div class="col-lg-8">
                        <p class="bord-all pad-all">
@if($refund->refund_status == 1 && !$refund->order->bkash_refundtrxid)
<a href="{{ route('get.refund', $refund->order->code) }}" class="btn btn-info">Refund</a>
@elseif($refund->order->bkash_refundtrxid)
Bkash Refund TrxID : {{ $refund->order->bkash_refundtrxid}}
@endif
                            {{$userpaymrnt_mrthod->bkash_ac}}
                         
                        </p>
                    </div>
                </div>

                @else

                <div class="form-group row">
                     <label class="col-lg-2 col-from-label"><b>{{translate('Request Payment Method')}}:</b></label>
                    
                        <div class="col-lg-8">
                    
                    <table style="width:30%">
                    <tr>
                        <th>Bank Name:</th>
                        <td>{{$userpaymrnt_mrthod->bank_name}}</td>
                    </tr>
                    <tr>
                        <th>Branch Name:</th>
                        <td>{{$userpaymrnt_mrthod->bank_branch_name}}</td>
                    </tr>
                    <tr>
                        <th>Account Name:</th>
                        <td>{{$userpaymrnt_mrthod->bank_acc_name}}</td>
                    </tr>
                    <tr>
                        <th>Account Number:</th>
                        <td>{{$userpaymrnt_mrthod->bank_acc_no}}</td>
                    </tr>
                  
                    </table>
                          </div>
                </div>

                @endif

            </div>
        </div>
    </div>
</div>

@endsection
