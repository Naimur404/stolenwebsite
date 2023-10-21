<div class="col-xl-2">
    <div class="col mb-5">
        <div class="widget widget-custom-menu">
            <p class="h5 fw-bold widget-title mb-4">{{ $config['name'] }}</p>
            {!!
                Menu::generateMenu(['slug' => $config['menu_id'], 'options' => ['class' => 'ps-0'], 'view' => 'menu-default'])
            !!}
        </div>
    </div>
</div>

