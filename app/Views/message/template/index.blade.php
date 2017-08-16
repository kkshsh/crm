@extends('common.table')
@section('tableHeader')
    <th class="sort" data-field="id">ID</th>
    <th>一级类型</th>
    <th>类型</th>
    <th>名称</th>
    <th class="sort" data-field="created_at">创建时间</th>
    <th class="sort" data-field="updated_at">更新时间</th>
    <th>操作</th>
@stop
@section('tableBody')
    @foreach($data as $template)
        <tr>
            <td>{{ $template->id }}</td>
            <td>{{ $template->type_parent_name }}</td>
            <td>{{ $template->type_name }}</td>
            <td>{{ $template->name }}</td>
            <td>{{ $template->created_at }}</td>
            <td>{{ $template->updated_at }}</td>
            <td>
                <a href="{{ route('messageTemplate.show', ['id'=>$template->id]) }}" class="btn btn-info btn-xs">
                    <span class="glyphicon glyphicon-eye-open"></span> 查看
                </a>
                @if(request()->user()->group == 'leader')
                <a href="{{ route('messageTemplate.edit', ['id'=>$template->id]) }}" class="btn btn-warning btn-xs">
                    <span class="glyphicon glyphicon-pencil"></span> 编辑
                </a>
                <a href="javascript:" class="btn btn-danger btn-xs delete_item"
                   data-id="{{ $template->id }}"
                   data-url="{{ route('messageTemplate.destroy', ['id' => $template->id]) }}">
                    <span class="glyphicon glyphicon-trash"></span> 删除
                </a>
                @endif
            </td>
        </tr>
    @endforeach
@stop
