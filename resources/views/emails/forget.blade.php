@component('mail::message')

Hello {{$user->name}},

<p>We Understand It Happens.</p>

@component('mail::button',['url'=>url('reset/'.$user-> remember_token)])
Reset your Password   
@endcomponent

<p>In case you have any issues recovering your password Please contact us.</p>
Thanks,<br>
    {{config('app_name')}}
@endcomponent