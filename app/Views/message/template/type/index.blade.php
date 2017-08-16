@extends('common.table')
@section('tableHeader')
    <th class="sort" data-field="id">ID</th>
    <th>上级</th>
    <th>类型</th>
    <th>模版数量</th>
    <th class="sort" data-field="created_at">创建时间</th>
    <th class="sort" data-field="updated_at">更新时间</th>
    <th>操作</th>
@stop
@section('tableBody')
    @foreach($data as $type)
        <tr>
            <td>{{ $type->id }}</td>
            <td>{{ $type->parent ? $type->parent->name : '一级' }}</td>
            <td>{{ $type->name }}</td>
            <td>{{ $type->templates()->count() }}</td>
            <td>{{ $type->created_at }}</td>
            <td>{{ $type->updated_at }}</td>
            <td>
                <a href="{{ route('messageTemplateType.show', ['id'=>$type->id]) }}" class="btn btn-info btn-xs">
                    <span class="glyphicon glyphicon-eye-open"></span> 查看
                </a>
                @if(request()->user()->group == 'leader')
                <a href="{{ route('messageTemplateType.edit', ['id'=>$type->id]) }}" class="btn btn-warning btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span> 编辑
                </a>
                @if($type->templates()->count() < 1)
                    <a href="javascript:" class="btn btn-danger btn-xs delete_item"
                       data-id="{{ $type->id }}"
                       data-url="{{ route('messageTemplateType.destroy', ['id' => $type->id]) }}">
                        <span class="glyphicon glyphicon-trash"></span> 删除
                    </a>
                @endif
                @endif
            </td>
        </tr>
    @endforeach
@stop
