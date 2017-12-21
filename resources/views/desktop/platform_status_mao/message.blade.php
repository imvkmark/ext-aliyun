@extends('lemon.template.desktop_angle_iframe')
@section('desktop-main')
    <div class="page">
        @include('desktop.platform_order.header_detail')
        <div>
            <table class="table">
                <tr>
                    <td>
                        <div style="height: 500px;overflow-y: auto;width:570px;">
                            @foreach($messages as $message)
                                <dl>
                                    <dt class="pt3">
                                        {!! $message->mao_user_id !!}
                                        <i class="fa fa-user"> {!! $message->nick_name !!} </i> &nbsp;&nbsp;
                                        <i class="fa fa-clock-o"> {!! $message->created_at !!}</i> &nbsp;&nbsp;
                                    </dt>
                                    <dd class="pd5 pb8">
                                        {!! \App\Models\PlatformMaoLog::kvMsgType($message->msg_type) !!}
                                        {!! $message->msg_content !!}
                                    </dd>
                                </dl>
                            @endforeach
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        {!! Form::open(['route' => 'dsk_platform_status_mao.message', 'id' => 'form_message']) !!}
                        {!! Form::hidden('status_id', $status->id) !!}
                        {!! Form::textarea('reason', null) !!}
                        <button class=" btn btn-primary">
                            <span>提交留言</span>
                        </button>
                        {!! Form::close() !!}
                    </td>
                </tr>
            </table>

            <script>
                require(['jquery', 'lemon/util', 'jquery.validate', 'jquery.form'], function ($, util) {
                    var conf = util.validate_conf({
                        submitHandler : function (form) {
                            $(form).ajaxSubmit({
                                success : util.splash
                            });
                        },
                        rules : {
                            reason : {
                                required : true
                            }
                        }
                    }, 'bt3_inline');
                    $('#form_message').validate(conf);
                });
            </script>
        </div>
    </div>
@endsection