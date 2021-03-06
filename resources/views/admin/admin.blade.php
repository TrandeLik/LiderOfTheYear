@extends('layouts.app')
@section('content')

    <div class="row pl-0">
        <div class="col-md-3">
            <h2>Достижения</h2>
                <div class='card'>
                    <div class='card-body'>
                        <ul>
                            @foreach($allTypes as $type)
                                <li>{{$type -> category.', '.$type -> type.', '.$type -> stage}}</li>
                            @endforeach
                        </ul>
                        <a href="{{url('/achievement_types/all')}}">Все достижения</a><br>
                    </div>
              </div><br>
            <!--<a href="{{url('/achievement_type/add')}}">Добавить событие</a><br><br>-->
            <a href="{{url('/achievement_types/download_file')}}">Текущий список достижений</a><br><br>
            <form method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div>
                        <div class="input-group">
                            <div class="custom-file">
                                <input accept=  "application/vnd.ms-excel,
                                                application/vnd.ms-office,
                                                application/vnd-xls,
                                                application/vnd.ms-excel,
                                                application/msexcel,
                                                application/x-msexcel,
                                                application/x-ms-excel,
                                                application/x-excel,
                                                application/excel,
                                                application/x-dos_ms_excel,
                                                application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,
                                                application/xls,
                                                application/x-xls" type="file" class="custom-file-input" id="file" name="file">
                                <label class="custom-file-label" for="file" data-browse="Обзор">Изменить список достижений</label>
                            </div>
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#uploadListModal">
                                    Загрузить
                                </button>
                                <div class="modal fade" id="uploadListModal" tabindex="-1" role="dialog" aria-labelledby="uploadListModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="uploadListModalLabel">Подтвердите действие</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Вы действительно хотите загрузить новый список типов достижений? Все старые типы будут автоматически удалены
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                                <input type="submit" class="btn btn-outline-secondary" value="Загузить">
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <h2>
                Заявки
                <a href="{{url('achievements/all')}}"><button class="btn btn-success">Все достижения учеников</button></a>
            </h2>
            @if(count($sentAchievements) == 0)
                <p>На данный момент заявок нет</p>
            @else
                @foreach($sentAchievements as $achievement)
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">От пользователя {{$achievement -> user -> name}}, {{$achievement -> user -> form}}</h3>
                            @if ($achievement->category=='Участие в лицейской жизни')
                                <p>{{$achievement -> type.', '.$achievement -> name.', '.$achievement -> date}}</p>
                            @else
                                <p>{{$achievement -> type.', '.$achievement -> name.', '.$achievement -> stage.', '.$achievement -> subject.', '.$achievement -> result}}</p>
                            @endif
                            @if ($achievement->confirmation!='')
                                <a href="{{url('/achievement/'.$achievement->id.'/download_confirmation')}}">Подтверждение</a><br><br>
                            @endif
                            @if (count($achievement->comments) !== 0)
                                <comment-showing :comments="{{json_encode($achievement->comments)}}" :id="'{{$achievement->id}}'" :username="'{{$achievement->user->name}}'"></comment-showing><br>
                            @endif
                            <div class="row">
                                <a href="{{url('/achievement/'. $achievement -> id . '/confirm')}}"><button class="btn btn-success">Одобрить</button> </a>
                                <reject-achievement class="ml-1" :action-address="{{json_encode('/achievement/' . $achievement->id . '/reject')}}" :id="{{$achievement->id}}"></reject-achievement>
                            </div>
                        </div><br>
                    </div><br>
                @endforeach
                <a href="{{url('/achievements/sent')}}">Все заявки</a>
            @endif
        </div>
        <div class="col-md-3">
            <h2>Участники</h2>
            <div class="card">
                <div class="card-body">
                    @foreach($students as $student)
                        <li>
                            <a href="{{url('/user/' . $student -> id . '/profile')}}">{{$student -> name . ', ' . $student -> form}}</a>
                        </li>
                    @endforeach<br>
                    <a href="{{url('/users/all')}}">Все участники</a>
                </div>
            </div><br>
            <a href="{{url('/users/banned')}}">Заблокированные пользователи</a>
        </div>
    </div>
@endsection
