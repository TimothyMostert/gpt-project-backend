@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Privacy Policy</h1>

    <h4>Introduction</h4>
    <p>Our privacy policy will help you understand what information we collect at {{ config('app.name') }}, how {{ config('app.name') }} uses it, and what choices you have.</p>

    <h4>Data We Collect</h4>
    <p>We collect the following data:</p>
    <ul>
        <li>Personal identification information</li>
        <li>Browser and visit information</li>
        <li>Cookie information</li>
    </ul>

    <h4>How We Collect Your Data</h4>
    <p>We collect data and process data when you:</p>
    <ul>
        <li>Register online or place an order for any of our products or services.</li>
        <li>Voluntarily complete a customer survey or provide feedback on any of our message boards or via email.</li>
        <li>Use or view our website via your browser's cookies.</li>
    </ul>

    <h4>How We Use Your Data</h4>
    <p>{{ config('app.name') }} collects your data so that we can:</p>
    <ul>
        <li>Process your order and manage your account.</li>
        <li>Email you with special offers on other products and services we think you might like.</li>
    </ul>

    <h4>Data Protection Rights</h4>
    <p>We would like to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:</p>
    <ul>
        <li>The right to access – You have the right to request copies of your personal data. We may charge you a small fee for this service.</li>
        <li>The right to rectification – You have the right to request that we correct any information you believe is inaccurate. You also have the right to request that we complete the information you believe is incomplete.</li>
        <li>The right to erasure – You have the right to request that we erase your personal data, under certain conditions.</li>
        <li>The right to restrict processing – You have the right to request that we restrict the processing of your personal data, under certain conditions.</li>
        <li>The right to object to processing – You have the right to object to our processing of your personal data, under certain conditions.</li>
        <li>The right to data portability – You have the right to request that we transfer the data that we have collected to another organization, or directly to you, under certain conditions.</li>
    </ul>

    <h4>Cookies</h4>
    <p>Cookies are text files placed on your computer to collect standard Internet log information and visitor behavior information. When you visit our websites, we may collect information from you automatically through cookies or similar technology. For further information, visit allaboutcookies.org.</p>

    <h4>Privacy Policies of Other Websites</h4>
    <p>The {{ config('app.name') }} website contains links to other websites. Our privacy policy applies only to our website, so if you click on a link to another website, youshould read their privacy policy.</p>

    <h4>Changes to Our Privacy Policy</h4>
    <p>{{ config('app.name') }} keeps its privacy policy under regular review and places any updates on this web page.</p>

    <h4>How to Contact Us</h4>
    <p>If you have any questions about {{ config('app.name') }}'s privacy policy, the data we hold on you, or you would like to exercise one of your data protection rights, please do not hesitate to contact us.</p>

    <h4>How to Contact the Appropriate Authorities</h4>
    <p>Should you wish to report a complaint or if you feel that {{ config('app.name') }} has not addressed your concern in a satisfactory manner, you may contact the Information Commissioner's Office.</p>
</div>
@endsection