@php /** @var \Botble\Table\Abstracts\TableActionAbstract[] $actions */ @endphp
@php /** @var \Illuminate\Database\Eloquent\Model $model */ @endphp

<div class="table-actions">
    @foreach($actions as $action)
        {{ $action->model($model) }}
    @endforeach
</div>
