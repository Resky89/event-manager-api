<x-mail::message>
# Verify your account

Hello {{ $user->name ?? 'there' }},

Use the OTP code below to verify your account.

<x-mail::panel>
    <span style="font-size:28px;font-weight:700;letter-spacing:4px;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;display:inline-block;">
        {{ $otp }}
    </span>
</x-mail::panel>

This code is valid for {{ $minutes }} minutes.

<x-mail::button :url="config('app.url')">
Open Event Manager
</x-mail::button>

If you did not request this, please ignore this email.

Thanks,<br>
{{ config('app.name', 'Event Manager') }}
</x-mail::message>
