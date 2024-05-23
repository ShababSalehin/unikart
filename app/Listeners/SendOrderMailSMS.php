<?php

namespace App\Listeners;

use App\Events\OrderMailSMS;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use \App\Utility\NotificationUtility;

class SendOrderMailSMS
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderMailSMS  $event
     * @return void
     */
    public function handle(OrderMailSMS $event)
    {
       
        NotificationUtility::sendOrderPlacedNotificationForApi($event->order, $event->request);
        
    }
}
