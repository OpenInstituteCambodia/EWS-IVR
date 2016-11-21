<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Twilio Making Outbound Calls</title>
    {!! Html::style('css/bootstrap.css') !!}
    {!! Html::style('css/font-awesome.css') !!}
    {!! Html::style('css/ionicons.css') !!}
</head>
<body>
<div id="home" class="container">
    <!-- C2C contact form-->
    <div class="row">
        <div class="col-md-12">
            {!! Form::open(['url' =>'makeCall','method' =>'POST','v-on:submit.prevent'=>'processData','enctype'=> 'multipart/form-data','files'=>true]) !!}
            {{--<div class="form-group">
                {!! Form::label('soundFile','Sound file',array('class' => 'control-label')) !!}
                {!! Form::file('soundFile', array('class'=>'form-control','v-model'=>"sound")) !!}
            </div>--}}
           {{-- <div class="form-group">
                {!! Form::label('phoneContactFile','Phone',array('class' => 'control-label')) !!}
                {!! Form::file('phoneContactFile', array('class' => 'form-control','v-model'=>"phone")) !!}
            </div>--}}
            <div class="form-group">
                {!! Form::submit('Process', array('class' => 'btn btn-primary')) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
{!! Html::script('js/jquery.js') !!}
{!! Html::script('js/bootstrap.js') !!}
{!! Html::script('js/vue.js') !!}
{!! Html::script('js/vue-resource.js') !!}
{!! Html::script('js/app.js') !!}

</body>
</html>