<!DOCTYPE html>
<style>
    .button {
      background-color: #4CAF50!important;
      border: none;
      color: white;
      padding: 15px 32px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 16px;
      margin: 4px 2px;
      cursor: pointer;
    }
    
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                <!-- new user -->
                    </br>    
                    <form >
                        <div><b>New user</b></div>
                        </br>

                        <div class="form-group">
                            <label>First name:</label>
                            <input type="text" name="first_name" class="form-control" placeholder="first name" required="">
                        </div>

                        <div class="form-group">
                            <label>Last name:</label>
                            <input type="text" name="last_name" class="form-control" placeholder="last name" required="">
                        </div>

                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" class="form-control" placeholder="Password" required="">
                        </div>

                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" class="form-control" placeholder="Email" required="">
                        </div>

                        <div class="form-group button">
                            <button class="btn btn-success btn-new_user">new user</button>
                        </div>

                    </form>                

                    </br>    
                    
                <!-- current user -->
                    </br>    
                        <div class="form-group button">
                            <button class="btn btn-success btn-current_user">current user</button>
                        </div>
            </main>
        </div>
    </body>
    

</div>
<script type="text/javascript">
   
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
   
    $(".btn-new_user").click(function(e){
  
        e.preventDefault();
   
        var first_name = $("input[name=first_name]").val();
        var last_name = $("input[name=last_name]").val();
        var password = $("input[name=password]").val();
        var email = $("input[name=email]").val();
   
        $.ajax({
           type:'POST',
           url:"{{ route('add_user') }}",
           data:{first_name:first_name, last_name:last_name, password:password, email:email},
           success:function(response){
               
               switch(response.success) {
                   case 0:
                            alert('sikertelen user felvitel');
                            break;
                   case 1:
                            alert('sikertelen user lekérdezése');
                            break;
                   case 2:
                            alert('sikertelen user-app hozzárendelés');
                            break;
                   default:
                            alert('sikeres felvitel');
               }
           }
        });
  
    });
    
    $(".btn-current_user").click(function(e){
  
        e.preventDefault();
   
        $.ajax({
           type:'POST',
           url:"{{ route('current_user') }}",
           success:function(response){
               if (response.success){
                    alert(response.id+"/"+response.name+"/"+response.email);}
               else{
                    alert('sikertelen lekérdezés');
               }
           }
        });
  
    });

</script>    

</html>
