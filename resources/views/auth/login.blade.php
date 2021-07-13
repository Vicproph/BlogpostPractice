@extends('layouts.app')
@section('content')
    <div style="background-color: #ddd;" class="container mt-5  card card-body d-flex flex-column align-items-center ">
        <h2 class="my-4">Login Form</h2>
        @if ($errors->any())
            <div class="card alert alert-danger ">
                @foreach ($errors->all() as $error)
                    {{$error}}
                @endforeach
            </div>
        @endif
        <form class="my-4" action="{{route('auth.signin')}}" id="form" method="POST">
            @csrf
            <div class="form-group my-4">
                <label for="">Identifier</label>
                <input id="email" type="email" name="email" id="">
            </div>
            <div class="form-group my-4">
                <label for="">Password</label>
                <input id="password" type="password" name="password" id="">
            </div>
            <button type="submit" class="g- btn btn-primary" 
            data-sitekey="{{env("SITE_KEY")}}">Submit</button>
            <div class="g-recaptcha" data-sitekey="{{env('SITE_KEY')}}" id="recap"></div>

        </form>
        <script type="text/javascript">
            document.getElementById('form').addEventListener('submit',submitForm);
            function submitForm(e){
                e.preventDefault();
                const email = document.querySelector('#password').value
                const password = document.querySelector('#password').value
                const captcha = document.querySelector('#g-recaptcha-response')

                fetch('/subscribe')
            }
        </script>
    </div>
@endsection