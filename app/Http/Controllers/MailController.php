<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller {
//    public function basic_email() {
//       $data = array('name'=>"Adlan Akmal");

//       Mail::send(['text'=>'mail'], $data, function($message) {
//          $message->to('nabila@igsprotech.com.my', 'Wafaa Nabila')->subject
//             ('Email Test');
//          $message->from('adlan@igsprotech.com.my','Virat Gandhi');
//       });
//       echo "Basic Email Sent. Check your inbox.";
//    }
   public function html_email() {
      $data = array('name'=>"Adlan Akmal");
      Mail::send('auth.passwords.forgot', $data, function($message) {
         $message->to('nabila@igsprotech.com.my', 'Wafaa Nabila')
                 ->subject('Email Test');

         $message->from('adlan@igsprotech.com.my','Adlan Akmal');
      });
      echo "HTML Email Sent. Check your inbox.";
   }

}
