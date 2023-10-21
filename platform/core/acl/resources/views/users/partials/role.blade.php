<a data-type="select"
   data-source="{{ route('roles.list.json') }}"
   data-pk="{{ $item->id }}"
   data-url="{{ route('roles.assign') }}"
   data-value="{{ $role?->id ?: 0 }}"
   data-title="{{ trans('core/acl::users.assigned_role') }}"
   class="editable"
   href="#">
    {{ $role?->name ?: trans('core/acl::users.no_role_assigned') }}
</a>
