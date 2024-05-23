<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Conversation;
use App\Seller;
use App\Customer;
use App\BusinessSetting;
use App\Message;
use Auth;
use App\Product;
use Mail;
use App\Mail\ConversationMailManager;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;

        if(Auth::user()->user_type == 'seller'){
            if (BusinessSetting::where('type', 'conversation_system')->first()->value == 1) {
                $conversations = Conversation::where('sender_id', Auth::user()->id)->orWhere('receiver_id', Auth::user()->id)->orderBy('created_at', 'desc');
            $sort_search = '';
               if ($request->has('search') && !empty($request->search)){
                    $sort_search = $request->search;
                }
                $conversations =  $conversations->paginate(15);
            
               $ids = $conversations->pluck('sender_id');
               $customers = Customer::whereIn('user_id',$ids)->get();
                return view('frontend.user.conversations.seconversation', compact('conversations','customers','sort_search'));
            }
            else {
                flash(translate('Conversation is disabled at this moment'))->warning();
                return back();
            }

        }

        if(Auth::user()->user_type == 'customer'){
            //$sellers = Seller::get();
            if (BusinessSetting::where('type', 'conversation_system')->first()->value == 1) {
                $conversations = Conversation::where('sender_id', Auth::user()->id)->orWhere('receiver_id', Auth::user()->id)->orderBy('created_at', 'desc');
               $sort_search = '';
               if ($request->has('search') && !empty($request->search)){
                    $sort_search = $request->search;
                }
                $conversations =  $conversations->paginate(15);
               $ids = $conversations->pluck('receiver_id');
               $sellers = Seller::whereIn('user_id',$ids)->get();

                return view('frontend.user.conversations.cuconversation', compact('conversations','sellers','sort_search'));
            }
            else {
                flash(translate('Conversation is disabled at this moment'))->warning();
                return back();
            }
            
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_index()
    {
        if (BusinessSetting::where('type', 'conversation_system')->first()->value == 1) {
            $conversations = Conversation::orderBy('created_at', 'desc')->get();
            return view('backend.support.conversations.index', compact('conversations'));
        }
        else {
            flash(translate('Conversation is disabled at this moment'))->warning();
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_type = Product::findOrFail($request->product_id)->user->user_type;

        $conversation = new Conversation;
        $conversation->sender_id = Auth::user()->id;
     
        $conversation->receiver_id = Product::findOrFail($request->product_id)->user->id;
        $conversation->product_id = $request->product_id;
        $conversation->title = $request->title;
        $conversation->save();

        if($conversation->save()) {
            $message = new Message;
            $message->conversation_id = $conversation->id;
            $message->receiver_id = $conversation->receiver_id;
            $message->user_id = Auth::user()->id;
            $message->message = $request->message;
            $message->save();

            // if ($message->save()) {
            //     $this->send_message_to_seller($conversation, $message, $user_type);
            // }
        
        }

        flash(translate('Message has been send to seller'))->success();
        return redirect('/conversations');
    }


    public function messstore(Request $request)
    {

       $conversation = new Conversation;
        $conversation->sender_id = Auth::user()->id;
        $conversation->receiver_id =$request->user_id;
        $conversation->title = $request->title;
        $conversation->save();

        if($conversation->save()) {
            $message = new Message;
            $message->conversation_id = $conversation->id;
            $message->user_id = Auth::user()->id;
            $message->message = $request->message;
            $message->save();
            }

        flash(translate('Message has been send to seller'))->success();
        return back();
    }

    public function send_message_to_seller($conversation, $message, $user_type)
    {
        $array['view'] = 'emails.conversation';
        $array['subject'] = 'Sender:- '.Auth::user()->name;
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Hi! You recieved a message from '.Auth::user()->name.'.';
        $array['sender'] = Auth::user()->name;

        if($user_type == 'admin') {
            $array['link'] = route('conversations.admin_show', encrypt($conversation->id));
        } else {
            $array['link'] = route('conversations.show', encrypt($conversation->id));
        }

        $array['details'] = $message->message;

        try {
            Mail::to($conversation->receiver->email)->queue(new ConversationMailManager($array));
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Auth::user()->user_type == 'seller'){

        $conversation = Conversation::findOrFail(decrypt($id));
       $messages = Message::where('conversation_id','=',(decrypt($id)))->where('receiver_id',Auth::user()->id)->get();
        
        foreach ($messages as $key => $message) {
            $message->receiver_viewed = 1;
            $message->save();
        }

        if ($conversation->sender_id == Auth::user()->id) {
            $conversation->sender_viewed = 1;
        }
        elseif($conversation->receiver_id == Auth::user()->id) {
            $conversation->receiver_viewed = 1;
        }
        $conversation->save();
      
            return view('frontend.user.conversations.show', compact('conversation'));
        
       }
       
       if(Auth::user()->user_type == 'customer'){

        $conversation = Conversation::findOrFail(decrypt($id));

        $messages = Message::where('conversation_id','=',(decrypt($id)))->where('receiver_id',Auth::user()->id)->get();
        
      foreach ($messages as $key => $message) {
            $message->receiver_viewed = 1;
            $message->save();
        }

    return view('frontend.user.conversations.cushow', compact('conversation')); 

    }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function refresh(Request $request)
    {
        $conversation = Conversation::findOrFail(decrypt($request->id));
        if($conversation->sender_id == Auth::user()->id){
            $conversation->sender_viewed = 1;
            $conversation->save();
        }
        else{
            $conversation->receiver_viewed = 1;
            $conversation->save();
        }
        return view('frontend.partials.messages', compact('conversation'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_show($id)
    {
        $conversation = Conversation::findOrFail(decrypt($id));
        if ($conversation->sender_id == Auth::user()->id) {
            $conversation->sender_viewed = 1;
        }
        elseif($conversation->receiver_id == Auth::user()->id) {
            $conversation->receiver_viewed = 1;
        }
        $conversation->save();

        $messages = Message::where('receiver_id','=',$conversation->sender_id)->get();

        foreach ($messages as $key => $message) {
            $message->admin_viewed = 1;
            $message->save();
        }
        return view('backend.support.conversations.show', compact('conversation'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $conversation = Conversation::findOrFail(decrypt($id));
        foreach ($conversation->messages as $key => $message) {
            $message->delete();
        }
        if(Conversation::destroy(decrypt($id))){
            flash(translate('Conversation has been deleted successfully'))->success();
            return back();
        }
    }
}
