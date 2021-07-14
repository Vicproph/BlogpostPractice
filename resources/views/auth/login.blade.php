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
                <input id="email" type="email" name="email" id="email">
            </div>
            <div class="form-group my-4">
                <label for="">Password</label>
                <input id="password" type="password" name="password" id="password">
            </div>
            <div class="g-recaptcha" style="display: none;" data-sitekey="{{env('SITE_KEY')}}"></div>

            <button type="submit" class="mt-4 btn btn-primary">Submit</button>

        </form>
        <script type="text/javascript">
            document.getElementById('form').addEventListener('submit',submitForm);
            function submitForm(e){
                e.preventDefault();
                const email = document.querySelector('#email').value
                const password = document.querySelector('#password').value
                const submitButtonTag = document.querySelector('form button');
                fetch('api/users/login',{
                    method: 'POST',
                    headers:{
                        'Accept':'application/json, text/plain, */*',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email:email,
                        password:password,
                        captcha_response: grecaptcha.getResponse()
                    })
                })
                .then((response)=>response.json())
                .then((data)=>{
                    
                    if (data.reached_login_attempts_limit === true){ // reCaptcha now has to be activated
                        grecaptcha.reset()
                        let captchaDiv = submitButtonTag.previousElementSibling;
                        if (captchaDiv.style.display =='none'){
                            captchaDiv.style.display = 'block';
                        }
                        if (data.captcha.success ===false){
                            
                            alert('Please complete the reCaptcha process');
                        }
                        
                    }
                    else if (data.reached_login_attempts_limit === false){ // Invalid creds
                        alert('Invalid Creds');
                    }
                    
                    else if (data.reached_login_attempts_limit == undefined && data.id !=undefined){
                        document.querySelector('#form').submit();
                    }
                    console.log(data);

                });
                    
            }
        </script>
    </div>
@endsection